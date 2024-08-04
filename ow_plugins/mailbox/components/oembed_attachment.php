<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.components
 * @since 1.6.1
 * */
class MAILBOX_CMP_OembedAttachment extends OW_Component
{
    protected $oembed = array();
    protected $message = "";

    public function __construct($message, $oembed )
    {
        parent::__construct();

        $this->message = $message;
        $this->oembed = $oembed;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if (!empty($this->oembed['title']))
        {
            $this->oembed['title'] = UTIL_String::truncate($this->oembed['title'], 23, '...');
        }

        if (!empty($this->oembed['description']))
        {
            $this->oembed['description'] = UTIL_String::truncate($this->oembed['description'], 40, '...');
        }

        $message_information = MAILBOX_BOL_MessageDao::getInstance()->findById($this->oembed['messageId']);
        $message_time = strftime("%H:%M", (int) $message_information->timeStamp);
        $message_seen = ($message_information->recipientRead === "1" and $message_information->senderId === OW::getUser()->getId()) ? true : false;

        $this->assign('message', $this->message);
        $this->assign('data', $this->oembed);
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == false) {
            $this->assign('message_info', array("message_time"=> $message_time, "message_seen"=>$message_seen));
        }
    }
}
