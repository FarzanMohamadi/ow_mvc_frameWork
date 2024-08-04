<?php
class FRMTELEGRAMIMPORT_CLASS_TextMessage extends FRMTELEGRAMIMPORT_CLASS_Message{
    public function __construct($message){
        parent::__construct($message);
    }
    public function buildAttachmentPreviewIdList(){
        $this->attachmentIdList = array();
        $this->previewIdList = array();
    }

    public function typeOfMessage()
    {
        return FRMTELEGRAMIMPORT_CLASS_Message::TEXT_MESSAGE;
    }

    public function isMessagePublishable()
    {
        if($this->text!=""){
            return true;
        }else{
            return false;
        }
    }
}