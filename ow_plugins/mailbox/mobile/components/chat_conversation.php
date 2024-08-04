<?php
class MAILBOX_MCMP_ChatConversation extends OW_MobileComponent
{
    public function __construct($data)
    {
        $script = UTIL_JsGenerator::composeJsString('
        OWM.conversation = new MAILBOX_Conversation({$params});
        OWM.conversationView = new MAILBOX_ConversationView({model: OWM.conversation});
        ', array('params' => $data));

        OW::getDocument()->addOnloadScript($script);

        OW::getLanguage()->addKeyForJs('mailbox', 'text_message_invitation');

        $form = new MAILBOX_MCLASS_NewMessageForm($data['conversationId'], $data['opponentId']);
        $this->addForm($form);
        $messages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversation($data['conversationId'],OW::getUser()->getId());
        foreach($messages as $message){
            $message->recipientRead = 1;
            MAILBOX_BOL_MessageDao::getInstance()->save($message);
        }
        $imageColor = BOL_AvatarService::getInstance()->getAvatarInfo($data['opponentId'], $data['avatarUrl']);
        if ($imageColor['empty']){
            $this->assign('color', $imageColor['color']);
        }
        $this->assign('data', $data);
        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());

        $url = OW::getRouter()->urlForRoute('mailbox_conv_list');
        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null){
            $url = $_SERVER['HTTP_REFERER'];
        }
        if(FRMSecurityProvider::checkPluginActive('frmmainpage', true)){
            if(!FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('chatGroups'))
                $url = OW::getRouter()->urlForRoute('frmmainpage.chatGroups');
            else if(!FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('mailbox'))
                $url = OW::getRouter()->urlForRoute('frmmainpage.mailbox.type', array('type'=>'chat'));
        }
        $this->assign('backReffererUrl', $url);

        $firstMessage = MAILBOX_BOL_ConversationService::getInstance()->getFirstMessage($data['conversationId']);





        $seenImgUrl = OW::getPluginManager()->getPlugin('mailbox')->getStaticUrl().'img/tic.svg';
        OW::getDocument()->addStyleDeclaration(".message_seen{background-image: url('".$seenImgUrl."');}");
        OW::getDocument()->addStyleDeclaration("#header{display:none}");
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('mailbox')->getStaticCssUrl().'mailbox.css' );

        OW::getDocument()->addScriptDeclaration("window.mailbox_remove_url = '" . OW::getRouter()->urlForRoute('mailbox_ajax_remove_message') . "'");
        OW::getDocument()->addScriptDeclaration("window.replyToMessage = null;");

        $language = OW::getLanguage();
        $language->addKeyForJs('mailbox', 'find_contact');
        $language->addKeyForJs('base', 'user_cant_chat_with_this_user');
        $language->addKeyForJs('mailbox', 'send_message_failed');
        $language->addKeyForJs('mailbox', 'confirm_conversation_delete');
        $language->addKeyForJs('mailbox', 'silent_mode_off');
        $language->addKeyForJs('mailbox', 'silent_mode_on');
        $language->addKeyForJs('mailbox', 'show_all_users');
        $language->addKeyForJs('mailbox', 'show_all_users');
        $language->addKeyForJs('mailbox', 'show_online_only');
        $language->addKeyForJs('mailbox', 'new_message');
        $language->addKeyForJs('mailbox', 'mail_subject_prefix');
        $language->addKeyForJs('mailbox', 'chat_subject_prefix');
        $language->addKeyForJs('mailbox', 'new_message_count');
        $language->addKeyForJs('mailbox', 'chat_message_empty');
        $language->addKeyForJs('mailbox', 'text_message_invitation');
        $language->addKeyForJs('mailbox', 'delete_confirm');
        $language->addKeyForJs('mailbox', 'text');
        $language->addKeyForJs('mailbox', 'send');
        $language->addKeyForJs('mailbox', 'attachment');
        $language->addKeyForJs('base', 'cancel');
        $language->addKeyForJs('mailbox', 'attache_file_delete_button');
    }
}