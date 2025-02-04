<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.controllers
 * @since 1.6.1
 */
class PHOTO_CTRL_AjaxUpload extends OW_ActionController
{
    CONST STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    
    public function __construct()
    {
        parent::__construct();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
    }
    
    public function init()
    {
        parent::init();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        if ( !OW::getUser()->isAuthorized('photo', 'upload')  && !OW::getUser()->isAuthorized('photo') && !OW::getUser()->isAdmin())
        {
            $this->returnResponse(array(
                'status' => self::STATUS_ERROR,
                'result' => false,
                'msg' => OW::getLanguage()->text('photo', 'auth_upload_permissions')
            ));
        }
    }
    
    protected function getEntity( $params )
    {
        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            $params['entityType'] = 'user';
            $params['entityId'] = OW::getUser()->getId();
        }
        
        return array($params['entityType'], $params['entityId']);
    }

    private function isAvailableFile( $file )
    {
        return !empty($file['file']) && 
            $file['file']['error'] === UPLOAD_ERR_OK && 
            in_array($file['file']['type'], array('image/jpeg', 'image/png', 'image/gif'), true) &&
            $_FILES['file']['size'] <= $this->photoService->getMaxUploadFileSize() && 
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
                    return OW::getLanguage()->text('photo', 'error_ini_size');
                case UPLOAD_ERR_FORM_SIZE:
                    return OW::getLanguage()->text('photo', 'error_form_size');
                case UPLOAD_ERR_PARTIAL:
                    return OW::getLanguage()->text('photo', 'error_partial');
                case UPLOAD_ERR_NO_FILE:
                    return OW::getLanguage()->text('photo', 'error_no_file');
                case UPLOAD_ERR_NO_TMP_DIR:
                    return OW::getLanguage()->text('photo', 'error_no_tmp_dir');
                case UPLOAD_ERR_CANT_WRITE:
                    return OW::getLanguage()->text('photo', 'error_cant_write');
                case UPLOAD_ERR_EXTENSION:
                    return OW::getLanguage()->text('photo', 'error_extension');
                default:
                    return OW::getLanguage()->text('photo', 'no_photo_uploaded');                        
            }
        }
        else
        {
            return OW::getLanguage()->text('photo', 'no_photo_uploaded');
        }
    }
    
    public function ajaxSubmitPhotos( $params )
    {
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('photo', 'upload');
        // Check balance photo count == balanse count. Delete other photo
        if ( $status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE && !OW::getUser()->isAuthorized('photo') && !OW::getUser()->isAdmin())

        {
            $this->returnResponse(array('result' => FALSE, 'msg' => $status['msg']));
        }
        
        $userId = OW::getUser()->getId();
        $photoTmpService = PHOTO_BOL_PhotoTemporaryService::getInstance();
        
        if ( (!strlen($albumName =  UTIL_HtmlTag::stripTagsAndJs($_POST['album'])) || !strlen($albumName = UTIL_HtmlTag::stripTagsAndJs($_POST['album-name']))) || count($tmpList = $photoTmpService->findUserTemporaryPhotos($userId, 'order')) === 0 )
        {
            $resp = array('result' => FALSE, 'msg' => OW::getLanguage()->text('photo', 'photo_upload_error'));
            
            $this->returnResponse($resp);
        }
        
        $form = new PHOTO_CLASS_AjaxUploadForm('user', $userId);
        
        if ( !$form->isValid($_POST) )
        {
            $error = $form->getErrors();
            $resp = array('result' => FALSE);
                
            if ( !empty($error['album-name'][0]) )
            {
                $resp['msg'] = $error['album-name'][0];
            }
            else
            {
                $resp['msg'] = OW::getLanguage()->text('photo', 'photo_upload_error');
            }
            
            $this->returnResponse($resp);
        }
        
        list($entityType, $entityId) = $this->getEntity($params);
        
        if ( !($album = $this->photoAlbumService->findEntityAlbumByName($albumName, $entityId, $entityType)) )
        {
            $album = new PHOTO_BOL_PhotoAlbum();
            $album->name = $albumName;
            $album->userId = $userId;
            $album->entityId = $entityId;
            $album->entityType = $entityType;
            $album->createDatetime = time();
            $album->description = strlen($_POST['description']) ? UTIL_HtmlTag::stripTagsAndJs(trim($_POST['description'])) : '';
            
            $this->photoAlbumService->addAlbum($album);
        }

        $photos = array();
        $photoTotalCount = $this->photoService->countUserPhotos($userId);
        $photoTotalSetting = OW::getConfig()->getValue('photo', 'user_quota');
        $photoInAlbumCount = $this->photoAlbumService->countAlbumPhotos($album->id);
        $photoInAlbumSetting = OW::getConfig()->getValue('photo', 'album_quota');

        $tmpList = array_reverse($tmpList);

        foreach ( $tmpList as $tmpPhoto )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('photo', 'upload');
            $status_moderator = BOL_AuthorizationService::getInstance()->getActionStatus('photo');
            if ( $status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE && $status_moderator['status'] != BOL_AuthorizationService::STATUS_AVAILABLE )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('photo', 'permission_exceeded', array('count' => count($tmpList), 'uploaded' => count($photos))));

                break;
            }
            elseif ( ++$photoTotalCount > $photoTotalSetting )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('photo', 'quota_exceeded', array('limit' => $photoTotalSetting)));

                break;
            }
            elseif ( ++$photoInAlbumCount > $photoInAlbumSetting )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('photo', 'album_quota_exceeded', array('limit' => $photoInAlbumSetting)));

                break;
            }
                
            $tmpId = $tmpPhoto['dto']->id;
            $angel = isset($_POST['rotate'][$tmpId]) ? fmod((int) $_POST['rotate'][$tmpId], 360) : 0;
            
            $photo = $photoTmpService->moveTemporaryPhoto($tmpId, $album->id, !empty($_POST['desc'][$tmpId]) ? $_POST['desc'][$tmpId] : '', NULL, $angel);
            $photoTmpService->deleteTemporaryPhoto($tmpId);
            
            if ( $photo )
            {
                $photos[] = $photo;
            }
        }

        $form->triggerComplete(array(
            'album' => $album,
            'photos' => $photos
        ));

        $resp = $this->onSubmitComplete($entityType, $entityId, $album, $photos);
        
        $this->returnResponse($resp);
    }

    protected function onSubmitComplete( $entityType, $entityId, PHOTO_BOL_PhotoAlbum $album, $photos )
    {
        $this->photoService->createAlbumCover($album->id, $photos);
        
        $userId = OW::getUser()->getId();
        $result = array('result' => TRUE);
        
        if ( empty($photos) )
        {
            $result['url'] = OW::getRouter()->urlForRoute('photo_user_album', array(
                'user' => BOL_UserService::getInstance()->getUserName($userId),
                'album' => $album->id
            ));
            
            return $result;
        }
        
        $movedArray = array();
        foreach ( $photos as $photo )
        {
            $movedArray[] = array(
                'entityType' => $entityType,
                'entityId' => $entityId,
                'addTimestamp' => $photo->addDatetime,
                'photoId' => $photo->id,
                'hash' => $photo->hash,
                'description' => $photo->description
            );
            $userId = OW::getUser()->getId();
            OW::getEventManager()->trigger(new OW_Event('photo.after_desc_change', array('entityType' => 'photo_comments','entityId' => $photo->id, 'text'=>$photo->description,'userId'=>$userId)));
        }
        PHOTO_BOL_SearchService::getInstance()->handlePhotoInsertionCacheInconsistency($photos);

        $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, $movedArray);
        OW::getEventManager()->trigger($event);

        $photoCount = count($photos);
        $photoIdList = array();
        foreach ( $photos as $photo )
        {
            $photoIdList[] = $photo->id;
        };

        $newPhotos = PHOTO_BOL_PhotoDao::getInstance()->findByIdList($photoIdList);
        $approvalPhotos = array();

        foreach ($newPhotos as $photo )
        {
            if
            ( $photo->status == PHOTO_BOL_PhotoDao::STATUS_APPROVAL )
            {
                $approvalPhotos[] = $photo;
            }
        };

        if ( ($approvalCount = count($approvalPhotos)) === $photoCount )
        {
            if ( $approvalCount === 1 )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('photo', 'photo_uploaded_pending_approval'));
            }
            else
            {
                OW::getFeedback()->info(OW::getLanguage()->text('photo', 'photos_uploaded_pending_approval', array('count' => $approvalCount)));
            }

            if ( $this->photoAlbumService->countAlbumPhotos($album->id) > 0 )
            {
                $result['url'] = OW::getRouter()->urlForRoute('photo_user_album', array(
                    'user' => BOL_UserService::getInstance()->getUserName($userId),
                    'album' => $album->id
                ));
            }
            else
            {
                $result['url']= OW::getRouter()->urlForRoute('photo_user_albums', array(
                    'user' => BOL_UserService::getInstance()->getUserName($userId)
                ));
            }

            return $result;
        }
        
        if ( $photoCount == 1 )
        {
            $this->photoService->triggerNewsfeedEventOnSinglePhotoAdd($album, $photos[0]);
        }
        else
        {
            $this->photoService->triggerNewsfeedEventOnMultiplePhotosAdd($album, $photos);
        }

        $result['url'] = OW::getRouter()->urlForRoute('photo_user_album', array(
            'user' => BOL_UserService::getInstance()->getUserName($userId),
            'album' => $album->id
        ));
        OW::getFeedback()->info(OW::getLanguage()->text('photo', 'photos_uploaded', array('count' => $photoCount)));
        
        return $result;
    }
    
    public function upload( array $params = array() )
    {
        if ( $this->isAvailableFile($_FILES) )
        {
            $order = !empty($_POST['order']) ? (int) $_POST['order'] : 0;

            if ( ($id = PHOTO_BOL_PhotoTemporaryService::getInstance()->addTemporaryPhoto($_FILES['file']['tmp_name'], OW::getUser()->getId(), $order)) )
            {
                $fileUrl = PHOTO_BOL_PhotoTemporaryDao::getInstance()->getTemporaryPhotoUrl($id, 2);
                
                $this->returnResponse(array(
                    'status' => self::STATUS_SUCCESS,
                    'fileUrl' => $fileUrl . '?' . FRMSecurityProvider::generateUniqueId(),
                    'id' => $id
                ));
            }
            else
            {
                $this->returnResponse(array(
                    'status' => self::STATUS_ERROR,
                    'msg' => OW::getLanguage()->text('photo', 'no_photo_uploaded')
                ));
            }
        }
        else
        {
            $msg = $this->getErrorMsg($_FILES);

            $this->returnResponse(array(
                'status' => self::STATUS_ERROR,
                'msg' => $msg
            ));
        }
    }
    
    public function delete( array $params = array() )
    {
        if ( !empty($_POST['id']) )
        {
            PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteTemporaryPhoto((int)$_POST['id']);
        }
        
        exit();
    }
    
    private function returnResponse( $response )
    {
        if ( !OW_DEBUG_MODE )
        {
            ob_end_clean();
        }

        exit(json_encode($response));
    }

    public function checkFakeAlbumData( array $params = array() )
    {
        $form = new PHOTO_CLASS_CreateFakeAlbumForm();

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $this->returnResponse(array(
                'result' => true,
                'data' => array_merge($_POST, $form->getValues())
            ));
        }
        else
        {
            $this->returnResponse(array(
                'result' => false,
                'errors' => $form->getErrors()
            ));
        }
    }
}
