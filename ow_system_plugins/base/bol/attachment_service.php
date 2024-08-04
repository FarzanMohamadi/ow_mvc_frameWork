<?php
/**
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_AttachmentService
{
    /**
     * @var BOL_AttachmentDao
     */
    private $attachmentDao;

    /**
     * Singleton instance.
     *
     * @var BOL_AttachmentService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AttachmentService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->attachmentDao = BOL_AttachmentDao::getInstance();
    }

    public function deleteExpiredTempImages()
    {
        $attachList = $this->attachmentDao->findExpiredInactiveItems(time() - 3600);

        /* @var $item BOL_Attachment */
        foreach ( $attachList as $item )
        {
            $filePath = $this->getAttachmentsDir() . $item->getFileName();

            if ( OW::getStorage()->fileExists($filePath) )
            {
                OW::getStorage()->removeFile($filePath);
            }

            $this->attachmentDao->delete($item);
        }
    }

    public function deleteUserAttachments( $userId )
    {
        $list = $this->attachmentDao->findByUserId($userId);

        /* @var $item BOL_Attachment */
        foreach ( $list as $item )
        {
            $this->deleteAttachmentById($item->getId());

            $this->attachmentDao->delete($item);
        }
    }

    // TODO refactor - should delete attachment by id
//    public function deleteAttachmentByUrl( $url )
//    {
//        $attch = $this->attachmentDao->findAttachmentByFileName(trim(basename($url)));
//
//        if ( $attch != NULL )
//        {
//            $this->deleteAttachmentById($attch->getId());
//
//            $this->attachmentDao->delete($attch);
//        }
//        else
//        {
//            if ( OW::getStorage()->fileExists($this->getAttachmentsDir() . basename($url)) )
//            {
//                OW::getStorage()->removeFile($this->getAttachmentsDir() . basename($url));
//            }
//        }
//    }

    public function deleteAttachmentById( $id )
    {
        $attch = $this->attachmentDao->findById($id);
        /* @var $attch BOL_Attachment */
        if ( $attch !== null )
        {
            $fileName=$attch->fileName;
            $directory=$this->getAttachmentsDir();
            $filePath = $this->getAttachmentsDir() . $fileName;
            if ( OW::getStorage()->fileExists($filePath) )
            {
                $attch->setFileName('deleted_' . FRMSecurityProvider::generateUniqueId() . '_' . $attch->getFileName());
                $this->attachmentDao->save($attch);
                $fileNewPath = $directory . $attch->getFileName();
                OW::getStorage()->renameFile($filePath, $fileNewPath);
//                OW::getStorage()->removeFile($filePath);
                $string=explode(".",$fileName);
                $fileExtention=".".$string[count($string)-1];
                $firstPartofName=str_replace($fileExtention,"",$fileName);
                $pattern=$directory.'/'.$firstPartofName.'*';
                $files = glob($pattern);

                for($i=0;$i<count($files);$i=$i+1){
                    if ( OW::getStorage()->fileExists($files[$i]) ){
                        OW::getStorage()->removeFile($files[$i]);
                    }
                }
            }
            $this->attachmentDao->delete($attch);

            $logArray = array('entity_type' => 'attachment', 'id'=> $attch->getId(), 'user_id' => $attch->getUserId(), 'size' => $attch->getSize(),
                'plugin_key' => $attch->getPluginKey(), 'file_name' => $attch->getFileName(), 'original_name' => $attch->getOrigFileName());
            OW::getLogger()->writeLog(OW_Log::INFO, 'remove_file', $logArray);

            OW::getEventManager()->trigger(new OW_Event("base.attachment.delete", array(
                'id'=> $attch->getId(), 'plugin_key' => $attch->getPluginKey(),
                'file_name' => $attch->getFileName(), 'original_name' => $attch->getOrigFileName()
            )));

            $config=OW::getConfig();
            $fileSize=BOL_AttachmentService::getInstance()->getTotalAttachmentsSize();
            $config->saveConfig('base','totalSize',$fileSize);
        }
    }

    public function editAttachmentById($id, $new_name, $new_parent_id){
        $attch = $this->attachmentDao->findById($id);
        /* @var $attch BOL_Attachment */

        if ( empty($attch) ) {
            return;
        }

        // rename from base table
        if (!empty($new_name)) {
            $attch->setOrigFileName($new_name);
            $this->attachmentDao->save($attch);
        }

        // rename and relocate from frmfilemanager
        if(FRMSecurityProvider::checkPluginActive('frmfilemanager', true)){
            FRMFILEMANAGER_BOL_Service::getInstance()->editFileByAttachmentId($id, $new_name, $new_parent_id);
        }

        OW::getEventManager()->trigger(new OW_Event("base.attachment.edit", array('attachment_id' => $id)));
        $logArray = array('entity_type' => 'attachment', 'id'=> $attch->getId(), 'user_id' => $attch->getUserId(), 'new_name' => $attch->getOrigFileName());
        OW::getLogger()->writeLog(OW_Log::INFO, 'edit_attachment', $logArray);
    }

    /***
     * @param $id
     * @return BOL_Attachment
     */
    public function duplicateAttachmentById($id){
        $logArray = array('entity_type' => 'attachment', 'id'=> $id);
        OW::getLogger()->writeLog(OW_Log::INFO, 'duplicate_attachment', $logArray);

        $attch = $this->attachmentDao->findById($id);
        /* @var $attch BOL_Attachment */

        if ( empty($attch) ) {
            return null;
        }

        // copy file
        $filePath = $this->getAttachmentsDir() . $attch->getFileName();
        $newFileName = FRMSecurityProvider::generateUniqueId() . '_' . UTIL_File::sanitizeName($attch->getOrigFileName());
        if ( ! OW::getStorage()->fileExists($filePath) )
        {
            return null;
        }
        OW::getStorage()->copyFile($filePath, $this->getAttachmentsDir() . $newFileName);

        // new row
        $attachDto = new BOL_Attachment();
        $attachDto->setUserId(OW::getUser()->getId());
        $attachDto->setAddStamp(time());
        $attachDto->setStatus(1);
        $attachDto->setSize($attch->size);
        $attachDto->setOrigFileName($attch->origFileName);
        $attachDto->setFileName($newFileName);
        $attachDto->setBundle($attch->bundle);
        $attachDto->setPluginKey($attch->pluginKey);
        $this->attachmentDao->save($attachDto);
        return $attachDto;
    }


//    public function saveTempImage( $id )
//    {
//        $attch = $this->attachmentDao->findById($id);
//        /* @var $attch BOL_Attachment */
//        if ( $attch === null )
//        {
//            return '_INVALID_URL_';
//        }
//
//        $filePath = $this->getAttachmentsTempDir() . $attch->getFileName();
//
//        if ( OW::getUser()->isAuthenticated() && file_exists($filePath) )
//        {
//            OW::getStorage()->copyFile($filePath, $this->getAttachmentsDir() . $attch->getFileName());
//            unlink($filePath);
//        }
//
//        $attch->setStatus(true);
//        $this->attachmentDao->save($attch);
//
//        return OW::getStorage()->getFileUrl($this->getAttachmentsDir() . $attch->getFileName());
//    }
    /*
     * @param array $fileInfo
     * @return array
     * Mohammad Agha Abbasloo $maxUploadSize must be read from config
     */

    public function processPhotoAttachment( $pluginKey, array $fileInfo, $bundle = null, $validFileExtensions = array(), $maxUploadSize = 0 )
    {
        return $this->processUploadedFile($pluginKey, $fileInfo, $bundle, array('jpeg', 'jpg', 'png', 'gif'), null);
    }
    /* attachments 1.6.1 update */

//    public function getAttachmentsTempUrl()
//    {
//        return OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'attachments/temp/';
//    }
//
//    public function getAttachmentsTempDir()
//    {
//        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS . 'temp' . DS;
//    }

    public function getAttachmentsUrl()
    {
        return OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments') . '/';
    }

    public function getAttachmentsDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS;
    }

    public function saveAttachment( BOL_Attachment $dto )
    {
        $this->attachmentDao->save($dto);
    }

    public function processUploadedFile( $pluginKey, array $fileInfo, $bundle = null, $validFileExtensions = array(), $maxUploadSize = null, $dimensions = null )
    {
        $language = OW::getLanguage();
        $error = false;
        $fileName=substr($fileInfo['name'],0,strrpos($fileInfo['name'],'.'));
        $explodes = explode('.', $fileInfo['name']);
        $fileInfo['name'] =  $fileName.'.'.strtolower(end($explodes));
        $eventValidateName = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::VALIDATE_UPLOADED_FILE_NAME, array('fileName' => $fileInfo['name'])));
        if(isset($eventValidateName->getData()['fileName'])){
            $fileValidName = $eventValidateName->getData()['fileName'];
        }
        if(isset($eventValidateName->getData()['fixedOriginalFileName'])){
            $fileInfo['name'] = $eventValidateName->getData()['fixedOriginalFileName'];
        }
        if ( !OW::getUser()->isAuthenticated() && !defined("OW_CRON") )
        {
            throw new InvalidArgumentException($language->text('base', 'user_is_not_authenticated'));
        }

        if ( empty($fileInfo) || (!is_uploaded_file($fileInfo['tmp_name']) && !OW::getStorage()->fileExists($fileInfo['tmp_name'])))
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_fail'));
        }

        if ( $fileInfo['error'] != UPLOAD_ERR_OK )
        {
            switch ( $fileInfo['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }

            throw new InvalidArgumentException($error);
        }

        if ( empty($validFileExtensions) )
        {
            $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
        }

        if ( $maxUploadSize === null )
        {
            $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
        }

        if ( !empty($validFileExtensions) && !in_array(UTIL_File::getExtension($fileInfo['name']), $validFileExtensions) )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_extension_is_not_allowed'));
        }

        // get all bundle upload size
        $bundleSize = floor($fileInfo['size'] / 1024);
        if ( $bundle !== null )
        {
            $list = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

            /* @var $item BOL_Attachment */
            foreach ( $list as $item )
            {
                $bundleSize += $item->getSize();
            }
        }

        /**
         * commented by Mohammad Agha Abbasloo
         * Bundle is same for all attachments until the user refreshes the page. So no error must be raised in this situation
         */
       /*if ( $maxUploadSize > 0 && $bundleSize > ($maxUploadSize * 1024) )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_max_upload_filesize_error'));
        }*/

        $attachDto = new BOL_Attachment();
        $attachDto->setUserId(OW::getUser()->getId());
        $attachDto->setAddStamp(time());
        $attachDto->setStatus(0);
        $attachDto->setSize(floor($fileInfo['size'] / 1024));
        $attachDto->setOrigFileName(htmlspecialchars($fileInfo['name']));
        if(isset($fileValidName)){
            $attachDto->setFileName(FRMSecurityProvider::generateUniqueId() . '_' . UTIL_File::sanitizeName($fileValidName));
        }else {
            $attachDto->setFileName(FRMSecurityProvider::generateUniqueId() . '_' . UTIL_File::sanitizeName($attachDto->getOrigFileName()));
        }
        $attachDto->setPluginKey($pluginKey);


        if ( $bundle !== null )
        {
            $attachDto->setBundle($bundle);
        }

        $this->attachmentDao->save($attachDto);
        $uploadPath = $this->getAttachmentsDir() . $attachDto->getFileName();
        $tempPath = $this->getAttachmentsDir() . 'temp_' . $attachDto->getFileName();

        $logArray = array('entity_type' => 'attachment', 'id '=> $attachDto->getId(), 'user_id' => $attachDto->getUserId(), 'size' => $attachDto->getSize(),
            'plugin_key' => $attachDto->getPluginKey(), 'file_name' => $attachDto->getFileName(), 'original_name' => $attachDto->getOrigFileName(), 'upload_stamp' => $attachDto->getAddStamp());
        OW::getLogger()->writeLog(OW_Log::INFO, 'upload_file', $logArray);

        if ( in_array(UTIL_File::getExtension($fileInfo['name']), array('jpg', 'jpeg', 'png', 'bmp')) )
        {
            try
            {
                $image = new UTIL_Image($fileInfo['tmp_name']);

                if ( empty($dimensions) )
                {
                    $dimensions = array('width' => UTIL_Image::DIM_FULLSCREEN_WIDTH, 'height' => UTIL_Image::DIM_FULLSCREEN_HEIGHT);
                }

                $image->resizeImage($dimensions['width'], $dimensions['height'])->orientateImage()->saveImage($tempPath);
                $image->destroy();
                OW::getStorage()->removeFile($fileInfo['tmp_name'], true);
            }
            catch ( Exception $e )
            {
                throw new InvalidArgumentException($language->text('base', 'upload_file_fail'));
            }
        }
        else
        {
            if(OW::getStorage()->fileExists($fileInfo['tmp_name']))
            {
                OW::getStorage()->copyFile($fileInfo['tmp_name'], $tempPath,true);
            }
            else {
                OW::getStorage()->moveFile($fileInfo['tmp_name'], $tempPath);
            }
        }

        OW::getStorage()->copyFile($tempPath, $uploadPath);
        OW::getStorage()->removeFile($tempPath);

        return array('uid' => $attachDto->getBundle(), 'dto' => $attachDto, 'path' => $uploadPath, 'url' => $this->getAttachmentsUrl() . $attachDto->getFileName());
    }

    public function getFilesByBundleName( $pluginKey, $bundle )
    {
        $list = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

        $resultArray = array();

        /* @var $item BOL_Attachment */
        foreach ( $list as $item )
        {
            $resultArray[] = array('dto' => $item, 'path' => $this->getAttachmentsDir() . $item->getFileName(), 'url' => $this->getAttachmentsUrl() . $item->getFileName());
        }

        return $resultArray;
    }

    /**
     * @param string $bundle
     * @param int $status
     */
    public function updateStatusForBundle( $pluginKey, $bundle, $status )
    {
        $this->attachmentDao->updateStatusByBundle($pluginKey, $bundle, $status);
        if($status==1) {
            $config = OW::getConfig();
            $fileSize = BOL_AttachmentService::getInstance()->getTotalAttachmentsSize();
            $config->saveConfig('base', 'totalSize', $fileSize);
        }
    }

    /**
     * @param int $userId
     * @param int $attachId
     */
    public function deleteAttachment( $userId, $attachId )
    {
        /* @var $attachId BOL_Attachment */
        $attach = $this->attachmentDao->findById($attachId);

        if ( $attach !== null && OW::getUser()->isAuthenticated() && OW::getUser()->getId() == $attach->getUserId() )
        {
            $this->deleteAttachmentById($attach->getId());

            $this->attachmentDao->delete($attach);
        }
    }

    /**
     * @param string $pluginKey
     * @param string $bundle
     */
    public function deleteAttachmentByBundle( $pluginKey, $bundle )
    {
        $attachments = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

        /* @var $attach BOL_Attachment */
        foreach ( $attachments as $attach )
        {
            $this->deleteAttachmentById($attach->getId());

            $this->attachmentDao->delete($attach);
        }
    }


    public function getTotalAttachmentsSize()
    {
        return $this->attachmentDao->getTotalAttachmentsSize();
    }
}
