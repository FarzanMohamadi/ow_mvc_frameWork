<?php
class FRMTELEGRAMIMPORT_CLASS_PhotoMessage extends FRMTELEGRAMIMPORT_CLASS_Message{
    public $photo;
    public $width;
    public $height;
    public function __construct($message){
        parent::__construct($message);
        $this->photo = $message->photo;
        $this->width = $message->width;
        $this->height = $message->height;
    }
    protected function buildAttachmentPreviewIdList(){
        $this->attachmentIdList = array();
        $this->previewIdList = array();
        $pluginKey = 'frmnewsfeedplus';
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $importDirPath = $service->getImportDirPath();
        $imagePath = $importDirPath . 'data'. DS . $this->photo;
        $size = filesize($imagePath);
        $fileName = basename($this->photo);
        $attachDto = new BOL_Attachment();
        $attachDto->setUserId(OW::getUser()->getId());
        $attachDto->setAddStamp(time());
        $attachDto->setStatus(0);
        $attachDto->setSize(floor($size / 1024));
        $attachDto->setOrigFileName(htmlspecialchars($fileName));

        $eventValidateName = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::VALIDATE_UPLOADED_FILE_NAME, array('fileName' => $fileName)));
        if(isset($eventValidateName->getData()['fileName'])){
            $fileValidName = $eventValidateName->getData()['fileName'];
        }
        if(isset($fileValidName)){
            $attachDto->setFileName(FRMSecurityProvider::generateUniqueId() . '_' . UTIL_File::sanitizeName($fileValidName));
        }else {
            $attachDto->setFileName(FRMSecurityProvider::generateUniqueId() . '_' . UTIL_File::sanitizeName($attachDto->getOrigFileName()));
        }
        $attachDto->setPluginKey($pluginKey);
        BOL_AttachmentDao::getInstance()->save($attachDto);

        $uploadPath = $service->getAttachmentsDir() . $attachDto->getFileName();

        OW::getStorage()->copyFile($imagePath, $uploadPath);
        $this->attachmentIdList [] = $attachDto->id;
        $this->previewIdList [] =$attachDto->id;
    }

    public function typeOfMessage()
    {
        return FRMTELEGRAMIMPORT_CLASS_Message::PHOTO_MESSAGE;
    }

    public function isMessagePublishable()
    {
        $relativePath = $this->photo;
        if(strpos($relativePath,"(")!=false || strpos($relativePath,")")!=false)
            return false;

        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $importDirPath = $service->getImportDirPath();
        $absolutePath =  $importDirPath . 'data'. DS . $relativePath;

        try{
            $isExist = OW::getStorage()->fileExists($absolutePath);
            return $isExist;
        }catch (Exception $e){
            return false;
        }
    }
}