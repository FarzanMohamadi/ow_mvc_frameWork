<?php
class FRMTELEGRAMIMPORT_CLASS_MessageCollection{
    private $collection;
    public function __construct($messages){
        $this->collection = $messages;
    }
    public function getMessage($i){
        $msg = $this->collection[$i];
        $message = FRMTELEGRAMIMPORT_CLASS_MessageFactory::createMessage($msg);
        return $message;
    }
    public function size(){
        return sizeof($this->collection);
    }


}