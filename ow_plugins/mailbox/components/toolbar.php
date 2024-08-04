<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.mailbox.components
 * @since 1.6.1
 */
class MAILBOX_CMP_Toolbar extends OW_Component
{
    private $useChat;

    public function __construct()
    {
        parent::__construct();

        $handlerAttributes = OW::getRequestHandler()->getHandlerAttributes();
        $event = new OW_Event('plugin.mailbox.on_plugin_init.handle_controller_attributes', array('handlerAttributes'=>$handlerAttributes));
        OW::getEventManager()->trigger($event);

        $handleResult = $event->getData();

        if ($handleResult === false)
        {
            $this->setVisible(false);
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
        else
        {
            if ( !BOL_UserService::getInstance()->isApproved() && OW::getConfig()->getValue('base', 'mandatory_user_approve') )
            {
                $this->setVisible(false);
            }

            $user = OW::getUser()->getUserObject();

            if (BOL_UserService::getInstance()->isSuspended($user->getId()))
            {
                $this->setVisible(false);
            }

            if ( (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
            {
                $this->setVisible(false);
            }

            $this->useChat = BOL_AuthorizationService::STATUS_AVAILABLE;

            $this->assign('useChat', $this->useChat);
            $this->assign('msg', '');
        }
    }

    public function render()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . "jquery-ui.min.js");
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'underscore-min.js', 'text/javascript', 3000 );
       // OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'backbone-min.js', 'text/javascript', 3000 );
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'backbone.js', 'text/javascript', 3000 );
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'audio-player.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'mailbox.js', 'text/javascript', 3000);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'contactmanager.js', 'text/javascript', 3001);

        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('mailbox')->getStaticCssUrl().'mailbox.css' );

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $userId = OW::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
        $profileUrl = BOL_UserService::getInstance()->getUserUrl($userId);

        $jsGenerator = UTIL_JsGenerator::newInstance();
        $jsGenerator->setVariable('OWMailbox.documentTitle', OW::getDocument()->getTitle());
        $jsGenerator->setVariable('OWMailbox.soundEnabled', (bool) BOL_PreferenceService::getInstance()->getPreferenceValue('mailbox_user_settings_enable_sound', $userId));
        $jsGenerator->setVariable('OWMailbox.showOnlineOnly', (bool) BOL_PreferenceService::getInstance()->getPreferenceValue('mailbox_user_settings_show_online_only', $userId));
        $jsGenerator->setVariable('OWMailbox.showAllMembersMode', (bool)OW::getConfig()->getValue('mailbox', 'show_all_members') );
        $jsGenerator->setVariable('OWMailbox.soundSwfUrl', OW::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'js/player.swf');
        $jsGenerator->setVariable('OWMailbox.soundUrl', OW::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'sound/receive.mp3');
        $jsGenerator->setVariable('OWMailbox.defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $jsGenerator->setVariable('OWMailbox.serverTimezoneOffset', date('Z') / 3600);
        $jsGenerator->setVariable('OWMailbox.useMilitaryTime', (bool) OW::getConfig()->getValue('base', 'military_time'));
        $jsGenerator->setVariable('OWMailbox.getHistoryResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'getHistory'));
        $jsGenerator->setVariable('OWMailbox.openDialogResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'updateUserInfo'));
        $jsGenerator->setVariable('OWMailbox.attachmentsSubmitUrl', OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addFile'));
        $jsGenerator->setVariable('OWMailbox.attachmentsDeleteUrl',  OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'deleteFile'));
        $jsGenerator->setVariable('OWMailbox.authorizationResponderUrl',  OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'authorization'));
        $jsGenerator->setVariable('OWMailbox.responderUrl', OW::getRouter()->urlFor("MAILBOX_CTRL_Mailbox", "responder"));
        $jsGenerator->setVariable('OWMailbox.userListUrl', OW::getRouter()->urlForRoute('mailbox_user_list'));
        $jsGenerator->setVariable('OWMailbox.convListUrl', OW::getRouter()->urlForRoute('mailbox_conv_list'));
        $jsGenerator->setVariable('OWMailbox.pingResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'ping'));
        $jsGenerator->setVariable('OWMailbox.settingsResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'settings'));
        $jsGenerator->setVariable('OWMailbox.userSearchResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'rsp'));
        $jsGenerator->setVariable('OWMailbox.bulkOptionsResponderUrl', OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'bulkOptions'));

        $plugin_update_timestamp = 0;
        if ( OW::getConfig()->configExists('mailbox', 'plugin_update_timestamp') )
        {
            $plugin_update_timestamp = OW::getConfig()->getValue('mailbox', 'plugin_update_timestamp');
        }
        $jsGenerator->setVariable('OWMailbox.pluginUpdateTimestamp', $plugin_update_timestamp);

        $todayDate = date('Y-m-d', time());
        $jsGenerator->setVariable('OWMailbox.todayDate', $todayDate);
        $todayDateLabel = UTIL_DateTime::formatDate(time(), true);
        $jsGenerator->setVariable('OWMailbox.todayDateLabel', $todayDateLabel);

        $activeModeList = $conversationService->getActiveModeList();
        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        $this->assign('chatModeEnabled', $chatModeEnabled);
        $jsGenerator->setVariable('OWMailbox.chatModeEnabled', $chatModeEnabled);
        $jsGenerator->setVariable('OWMailbox.useChat', $this->useChat);

        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $this->assign('mailModeEnabled', $mailModeEnabled);
        $jsGenerator->setVariable('OWMailbox.mailModeEnabled', $mailModeEnabled);

        $isAuthorizedSendMessage = OW::getUser()->isAuthorized('mailbox', 'send_message') || OW::getUser()->isAdmin();
        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);

        $configs = OW::getConfig()->getValues('mailbox');
//        if ( !empty($configs['enable_attachments']))
//        {
        OW::getLanguage()->addKeyForJs('base', 'upload_analyze_massage');
        OW::getLanguage()->addKeyForJs('base', 'delete');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
//        }

        $this->assign('im_sound_url', OW::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'sound/receive.mp3');

        /* DEBUG MODE */
        $debugMode = false;
        $jsGenerator->setVariable('im_debug_mode', $debugMode);
        $this->assign('debug_mode', $debugMode);

        $variables = $jsGenerator->generateJs();

        $details = array(
            'userId' => $userId,
            'displayName' => $displayName,
            'profileUrl' => $profileUrl,
            'avatarUrl' => $avatarUrl
        );
        OW::getDocument()->addScriptDeclaration("OWMailbox.userDetails = " . json_encode($details) . ";\n " . $variables);
        OW::getDocument()->addScriptDeclaration("window.mailbox_remove_url = '" . OW::getRouter()->urlForRoute('mailbox_ajax_remove_message') . "'");
        OW::getDocument()->addScriptDeclaration("window.replyToMessage = null;");

        $language = OW::getLanguage();
        $language->addKeyForJs('mailbox', 'find_contact');
        $language->addKeyForJs('base', 'user_block_message');
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
        $language->addKeyForJs('mailbox','edited_message_tag');
        $language->addKeyForJs('mailbox','attachment');
        $language->addKeyForJs('mailbox', 'text');
        $language->addKeyForJs('mailbox', 'send');
        $language->addKeyForJs('mailbox', 'attachment');
        $language->addKeyForJs('base', 'cancel');

        $profile_image_info = BOL_AvatarService::getInstance()->getAvatarInfo($userId, $avatarUrl);
        $avatar_proto_data = array('url' => 1, 'src' => BOL_AvatarService::getInstance()->getDefaultAvatarUrl(), 'class' => 'talk_box_avatar ', 'profile_image_info' => $profile_image_info);
        $this->assign('avatar_proto_data', $avatar_proto_data);

        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $this->assign('avatarUrl', $avatarUrl);
        $this->assign('online_list_url', OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')));

        /**/

        $actionPromotedText = '';


        $this->assign('replyToMessageActionPromotedText', $actionPromotedText);
        $this->assign('isAuthorizedReplyToMessage', true);

        /**/

        $lastSentMessage = $conversationService->getLastSentMessage($userId);
        $lastMessageTimestamp = (int)($lastSentMessage ? $lastSentMessage->timeStamp : 0);

        $pingInterval = FRMSecurityProvider::getDefaultPingIntervalInSeconds() * 1000;

        $applicationParams = array(
            'pingInterval'=>$pingInterval,
            'lastMessageTimestamp' => $lastMessageTimestamp
        );

        $js = UTIL_JsGenerator::composeJsString('OW.Mailbox = new OWMailbox.Application({$params});', array('params'=>$applicationParams));
        OW::getDocument()->addOnloadScript($js, 3003);


        $js = "
        OW.Mailbox.contactManager = new MAILBOX_ContactManager;
        OW.Mailbox.contactManagerView = new MAILBOX_ContactManagerView({model: OW.Mailbox.contactManager});";

        OW::getDocument()->addOnloadScript($js, 3009);

        return parent::render();
    }
}
