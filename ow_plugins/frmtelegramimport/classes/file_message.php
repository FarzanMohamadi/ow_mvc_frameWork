<?php
class FRMTELEGRAMIMPORT_CLASS_FileMessage extends FRMTELEGRAMIMPORT_CLASS_Message
{
    public $file;
    public $media_type;
    public $mime_type;
    public $thumbnail;

    public function __construct($message)
    {
        parent::__construct($message);
        $this->file = $message->file;
        if(isset($message->mime_type)){

            $this->mime_type = $message->mime_type;
        }
        if(isset($message->media_type)){

            $this->media_type = $message->media_type;
        }
        if(isset($message->thumbnail)){
            $this->thumbnail = $message->thumbnail;
        }
    }

    protected function buildAttachmentPreviewIdList()
    {
        $this->attachmentIdList = array();
        $this->previewIdList = array();
        $pluginKey = 'frmnewsfeedplus';
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $importDirPath = $service->getImportDirPath();
        $imagePath = $importDirPath . 'data'. DS . $this->file;
        $size = filesize($imagePath);
        $fileName = basename($this->file);
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
    }

    public function typeOfMessage()
    {
        if(isset($this->media_type)){
            if($this->media_type == "sticker")
                return FRMTELEGRAMIMPORT_CLASS_Message::STICKER_MESSAGE;
            if($this->media_type == "video_file" || $this->media_type == "video_message"){
                return FRMTELEGRAMIMPORT_CLASS_Message::VIDEO_MESSAGE;
            }
            if($this->media_type == "voice_message")
                return FRMTELEGRAMIMPORT_CLASS_Message::AUDIO_MESSAGE;
            if($this->media_type == "animation"){
                return FRMTELEGRAMIMPORT_CLASS_Message::ANIMATION_MESSAGE;
            }
        }
        return FRMTELEGRAMIMPORT_CLASS_Message::FILE_MESSAGE;
    }

    public function isMessagePublishable()
    {
        $relativePath = $this->file;
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