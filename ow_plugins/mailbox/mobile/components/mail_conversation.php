<?php
class MAILBOX_MCMP_MailConversation extends OW_MobileComponent
{
    public function __construct($data)
    {
        $script = UTIL_JsGenerator::composeJsString('

        OWM.bind("mailbox.ready", function(readyStatus){
            if (readyStatus == 2){
                OWM.conversation = new MAILBOX_Conversation({$params});
                OWM.conversationView = new MAILBOX_MailConversationView({model: OWM.conversation});
            }
        });
        ', array('params' => $data));

        OW::getDocument()->addOnloadScript($script);

        OW::getLanguage()->addKeyForJs('mailbox', 'text_message_invitation');

        $form = new MAILBOX_MCLASS_NewMailMessageForm($data['conversationId'], $data['opponentId']);
        $this->addForm($form);
        $messages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversation($data['conversationId'],OW::getUser()->getId());
        foreach($messages as $message){
            $message->recipientRead = 1;
            MAILBOX_BOL_MessageDao::getInstance()->save($message);
        }

        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null){
            $this->assign('backReffererUrl',$_SERVER['HTTP_REFERER']);
        }
        if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('mailbox')) {
            $this->assign('backReffererUrl', OW::getRouter()->urlForRoute('frmmainpage.mailbox.type',array('type'=>'mail')));
        }
        $this->assign('data', $data);
        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
    }
}