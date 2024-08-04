<?php
class FRMTELEGRAMIMPORT_CLASS_Channel{
    public $name;
    public $type;
    public $messages;
    public function __construct($channel)
    {
        $this->name = $channel->name;
        $this->type = $channel->type;
        $collection = new FRMTELEGRAMIMPORT_CLASS_MessageCollection($channel->messages);
        $this->messages = $collection;
    }
    public function statistic(){
        $info = array();
        $info[FRMTELEGRAMIMPORT_CLASS_Message::TEXT_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::PHOTO_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::VIDEO_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::AUDIO_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::FILE_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::ANIMATION_MESSAGE]= 0;
        $info[FRMTELEGRAMIMPORT_CLASS_Message::STICKER_MESSAGE]= 0;
        $n = $this->messages->size();
        for($i=0;$i<$n; $i++){
           $message =  $this->messages->getMessage($i);
           if($message!=null){
                $type = $message->typeOfMessage();
                $info[$type] +=1;
            }
        }
        unset($info["STICKER"]);
        unset($info["ANIMATION"]);
        return $info;
    }
}