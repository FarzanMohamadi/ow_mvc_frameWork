<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.7.5
 */
class ADMIN_CTRL_AjaxUpload extends ADMIN_CTRL_Abstract
{
    CONST STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    
    public function __construct()
    {
        parent::__construct();
        $this->fileService = BOL_FileService::getInstance();
    }
    
    public function init()
    {
        parent::init();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
    }
    
    protected function getEntity( $params )
    {
        if ( empty($params["entityType"]) || empty($params["entityId"]) )
        {
            $params["entityType"] = "user";
            $params["entityId"] = OW::getUser()->getId();
        }
        
        return array($params["entityType"], $params["entityId"]);
    }

    private function isAvailableFile( $file )
    {
        return !empty($file['file']) && 
            $file['file']['error'] === UPLOAD_ERR_OK && 
            in_array($file['file']['type'], array('image/jpeg', 'image/png', 'image/gif')) && 
            $_FILES['file']['size'] <= $this->fileService->getUploadMaxFilesizeBytes() &&
            is_uploaded_file($file['file']['tmp_name']);
    }
    
    private function getErrorMsg( $file )
    {
        if ( $this->isAvailableFile($file) )
        {
            return null;
        }
        
        if ( !empty($file['file']['error']) )
        {
            switch ( $file['file']['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    return OW::getLanguage()->text('admin', 'error_ini_size');
                case UPLOAD_ERR_FORM_SIZE:
                    return OW::getLanguage()->text('admin', 'error_form_size');
                case UPLOAD_ERR_PARTIAL:
                    return OW::getLanguage()->text('admin', 'error_partial');
                case UPLOAD_ERR_NO_FILE:
                    return OW::getLanguage()->text('admin', 'error_no_file');
                case UPLOAD_ERR_NO_TMP_DIR:
                    return OW::getLanguage()->text('admin', 'error_no_tmp_dir');
                case UPLOAD_ERR_CANT_WRITE:
                    return OW::getLanguage()->text('admin', 'error_cant_write');
                case UPLOAD_ERR_EXTENSION:
                    return OW::getLanguage()->text('admin', 'error_extension');
                default:
                    return OW::getLanguage()->text('admin', 'no_photo_uploaded');
            }
        }
        else
        {
            return OW::getLanguage()->text('admin', 'no_photo_uploaded');
        }
    }
    
    public function ajaxSubmitPhotos( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        $userId = OW::getUser()->getId();
        $fileTmpService = BOL_FileTemporaryService::getInstance();
        $themeService = BOL_ThemeService::getInstance();
        
        if ( count($tmpList = $fileTmpService->findUserTemporaryFiles($userId, 'order')) === 0 )
        {
            $resp = array('result' => false, 'msg' => OW::getLanguage()->text('admin', 'photo_upload_error'));
            
            $this->returnResponse($resp);
        }
        
        $form = new BASE_CLASS_AjaxUploadForm('user', $userId);
        
        if ( !$form->isValid($_POST) )
        {
            $resp = array('result' => false);
            $resp['msg'] = OW::getLanguage()->text('admin', 'photo_upload_error');
            $this->returnResponse($resp);
        }
        
        list($entityType, $entityId) = $this->getEntity($params);

        $files = array();
        $tmpList = array_reverse($tmpList);

        foreach ( $tmpList as $tmpFile )
        {
            $tmpId = $tmpFile['dto']->id;

            $file = $themeService->moveTemporaryFile($tmpId, !empty($_POST['desc'][$tmpId]) ? $_POST['desc'][$tmpId] : '');

            $fileTmpService->deleteTemporaryFile($tmpId);
            
            if ( $file )
            {
                $files[] = $file;
            }
        }

        $resp = $this->onSubmitComplete($entityType, $entityId, $files);
        
        $this->returnResponse($resp);
    }

    protected function onSubmitComplete( $entityType, $entityId, $files )
    {
        $result = array('result' => true);
        
        if ( empty($files) )
        {
            $result['url'] = OW::getRouter()->urlForRoute('admin_theme_graphics');
            
            return $result;
        }
        
        $movedArray = array();
        foreach ( $files as $file )
        {
            $movedArray[] = array(
                'entityType' => $entityType,
                'entityId' => $entityId,
                'addTimestamp' => $file->addDatetime,
                'fileId' => $file->id,
                'filename' => $file->filename,
                'title' => $file->title
            );
        }
        
        $fileCount = count($files);
        $fileIdList = array();
        foreach ( $files as $file )
        {
            $fileIdList[] = $file->id;
        };

        $result['url'] = OW::getRouter()->urlForRoute('admin_theme_graphics');
        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'photos_uploaded', array('count' => $fileCount)));
        
        return $result;
    }
    
    public function upload()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        if ( $this->isAvailableFile($_FILES) )
        {
            $order = !empty($_POST['order']) ? (int) $_POST['order'] : 0;

            if ( ($id = BOL_FileTemporaryService::getInstance()->addTemporaryFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], OW::getUser()->getId(), $order)) )
            {
                $fileUrl = BOL_FileTemporaryService::getInstance()->getTemporaryFileUrl($id);
                
                $this->returnResponse(array(
                    'status' => self::STATUS_SUCCESS,
                    'fileUrl' => $fileUrl,
                    'id' => $id,
                    'filename' => $_FILES['file']['name'])
                );
            }
            else
            {
                $this->returnResponse(array('status' => self::STATUS_ERROR, 'msg' => OW::getLanguage()->text('admin', 'no_photo_uploaded')));
            }
        }
        else
        {
            $msg = $this->getErrorMsg($_FILES);

            $this->returnResponse(array('status' => self::STATUS_ERROR, 'msg' => $msg));
        }
    }
    
    public function delete( array $params = array() )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        if ( !empty($_POST['id']) )
        {
            PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteTemporaryPhoto((int)$_POST['id']);
        }
        
        exit();
    }
    
    private function returnResponse( $response )
    {
        ob_end_clean();

        exit(json_encode($response));
    }
}
