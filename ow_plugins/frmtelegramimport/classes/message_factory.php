<?php
class FRMTELEGRAMIMPORT_CLASS_MessageFactory{
    public static function createMessage($message){
        if($message->type !="message"){
            return null;
        }
        if(isset($message->photo)){
            return new FRMTELEGRAMIMPORT_CLASS_PhotoMessage($message);
        }
        if(isset($message->file)){
            return new FRMTELEGRAMIMPORT_CLASS_FileMessage($message);
        }
        if(isset($message->text) && !empty($message->text)){
            return new FRMTELEGRAMIMPORT_CLASS_TextMessage($message);
        }
        return null;
    }
}