<?php
class MAILBOX_CLASS_EventHandler
{
    const CONSOLE_ITEM_KEY = 'mailbox';

    /**
     *
     * @var MAILBOX_BOL_ConversationService
     */
    private $service;

    /**
     * @var MAILBOX_BOL_AjaxService
     */
    private $ajaxService;

    public function __construct()
    {
        $this->service = MAILBOX_BOL_ConversationService::getInstance();
        $this->ajaxService = MAILBOX_BOL_AjaxService::getInstance();
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('ads.enabled_plugins', array($this, 'mailboxAdsEnabled'));
        OW::getEventManager()->bind('plugin.mailbox.on_plugin_init.handle_controller_attributes', array($this, 'onHandleControllerAttributes'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActions'));
        OW::getEventManager()->bind('base.online_now_click', array($this, 'onShowOnlineButton'));
        OW::getEventManager()->bind('base.ping', array($this, 'onPing'));
        OW::getEventManager()->bind('base.ping.notifications', array($this, 'onApiPing'), 1);
        OW::getEventManager()->bind('mailbox.ping', array($this, 'onPing'));
        OW::getEventManager()->bind('mailbox.mark_as_read', array($this, 'onMarkAsRead'));
        OW::getEventManager()->bind('mailbox.mark_unread', array($this, 'onMarkUnread'));
        OW::getEventManager()->bind('mailbox.get_conversation_id', array($this, 'getConversationId'));
        OW::getEventManager()->bind('mailbox.delete_conversation', array($this, 'onDeleteConversation'));
        OW::getEventManager()->bind('mailbox.create_conversation', array($this, 'onCreateConversation'));
        OW::getEventManager()->bind('mailbox.authorize_action', array($this, 'onAuthorizeAction'));
        OW::getEventManager()->bind('mailbox.find_user', array($this, 'onFindUser'));
        OW::getEventManager()->bind('mailbox.isConversationMutedByUser', array($this->service, 'isConversationMutedByUserEvent'));

        if (OW::getPluginManager()->isPluginActive('ajaxim'))
        {
            try
            {
                BOL_PluginService::getInstance()->uninstall('ajaxim');
            }
            catch(LogicException $e)
            {

            }
        }

        if (OW::getPluginManager()->isPluginActive('im'))
        {
            try
            {
                BOL_PluginService::getInstance()->uninstall('im');
            }
            catch(LogicException $e)
            {

            }
        }

        OW::getEventManager()->bind('mailbox.get_unread_message_count', array($this, 'getUnreadMessageCount'));
        OW::getEventManager()->bind('mailbox.get_chat_user_list', array($this, 'getChatUserList'));
        OW::getEventManager()->bind('mailbox.post_message', array($this, 'postMessage'));
        OW::getEventManager()->bind('mailbox.post_reply_message', array($this, 'postReplyMessage'));
        OW::getEventManager()->bind('mailbox.get_new_messages', array($this, 'getNewMessages'));
        OW::getEventManager()->bind('mailbox.get_new_messages_for_conversation', array($this, 'getNewMessagesForConversation'));
        OW::getEventManager()->bind('mailbox.get_messages', array($this, 'getMessages'));
        OW::getEventManager()->bind('mailbox.get_history', array($this, 'getHistory'));
        OW::getEventManager()->bind('mailbox.show_send_message_button', array($this, 'showSendMessageButton'));
        OW::getEventManager()->bind('mailbox.get_active_mode_list', array($this, 'onGetActiveModeList'));
        OW::getEventManager()->bind('friends.request-accepted', array($this, 'onFriendRequestAccepted'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_LOGIN, array($this, 'resetAllUsersLastData'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'messageAndConversationDeleteOnUserRemove'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'resetAllUsersLastData'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_BLOCK, array($this,'onBlockUser'));
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'updatePlugin'));
        
        OW::getEventManager()->bind('base.after_avatar_update', array($this, 'onChangeUserAvatar'));

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($conversationService, "onRabbitMQNotificationRelease"));
    }

    public function init()
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'sendPrivateMessageActionTool'));


        OW::getEventManager()->bind('mailbox.send_message', array($conversationService, 'onSendMessageWebSocket'));
        OW::getEventManager()->bind('mailbox.send_message_attachment', array($conversationService, 'onSendMessageAttachmentWebSocket'));
        OW::getEventManager()->bind("mailbox.mark_conversation", array($conversationService, "onMarkConversationWebSocket"));
        OW::getEventManager()->bind("mailbox.after_message_removed", array($conversationService, "onAfterMessageRemoved"));
        OW::getEventManager()->bind("mailbox.after_message_edited", array($conversationService, "onAfterMessageEdited"));

        OW::getEventManager()->bind('mailbox.send_message', array($this, 'onSendMessage'));
        OW::getEventManager()->bind('base.on_avatar_toolbar_collect', array($this, 'onAvatarToolbarCollect'));

        OW::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_MARK_CONVERSATION, array($this, 'markConversation'));
        OW::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_DELETE_CONVERSATION, array($this, 'deleteConversation'));

        OW::getEventManager()->bind('notifications.send_list', array($this, 'consoleSendList'));

        OW::getEventManager()->bind('base.attachment_uploaded', array($this, 'onAttachmentUpload'));

        OW::getEventManager()->bind('console.collect_items', array($this, 'onCollectConsoleItems'));
        OW::getEventManager()->bind('console.load_list', array($this, 'onLoadConsoleList'));
        OW::getEventManager()->bind('mailbox.renderOembed', array($this, 'onRenderOembed'));
    }

    public function updatePlugin()
    {
        if (OW::getConfig()->configExists('mailbox', 'updated_to_messages'))
        {
            /**
             * Update to Messages
             */
            $updated_to_messages = (int)OW::getConfig()->getValue('mailbox', 'updated_to_messages');

            if ($updated_to_messages === 0)
            {
                $e = new BASE_CLASS_EventCollector('usercredits.action_add');

                $actions = array();

                $mailboxEvent = new OW_Event('mailbox.admin.add_auth_labels');
                OW::getEventManager()->trigger($mailboxEvent);

                $data = $mailboxEvent->getData();
                if (!empty($data))
                {
                    foreach ($data['actions'] as $name=>$langLabel)
                    {
                        $actions[] = array('pluginKey' => 'mailbox', 'action' => $name, 'amount' => 0);
                    }

                }


                foreach ( $actions as $action )
                {
                    $e->add($action);
                }

                OW::getEventManager()->trigger($e);

                OW::getConfig()->saveConfig('mailbox', 'updated_to_messages', 1);
            }
        }

        if (OW::getConfig()->configExists('mailbox', 'install_complete'))
        {
            $installComplete = (int)OW::getConfig()->getValue('mailbox', 'install_complete');

            if (!$installComplete)
            {
                $groupName = 'mailbox';
                $authorization = OW::getAuthorization();
                if(BOL_AuthorizationService::getInstance()->findGroupByName($groupName) == null) {
                    $authorization->addGroup($groupName, 0);
                }
                $mailboxEvent = new OW_Event('mailbox.admin.add_auth_labels');
                OW::getEventManager()->trigger($mailboxEvent);

                $data = $mailboxEvent->getData();
                if (!empty($data))
                {
                    foreach ($data['actions'] as $name=>$langLabel)
                    {
                        $authorization->addAction($groupName, $name);
                    }
                }


                OW::getConfig()->saveConfig('mailbox', 'install_complete', 1);
                
            }
        }
    }

    public function sendPrivateMessageActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( OW::getUser()->getId() == $userId )
        {
            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $activeModeList = $this->service->getActiveModeList();
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        if (!$mailModeEnabled)
        {
            if (!$chatModeEnabled)
            {
                return;
            }
            else
            {

                if ( !OW::getUser()->isAuthorized('mailbox', 'send_chat_message') && !OW::getUser()->isAdmin())
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'send_chat_message');
                    if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
                    {
                        $linkId = 'mb' . rand(10, 1000000);
                        $linkSelector = '#' . $linkId;
                        $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');

                });', array('linkSelector'=>$linkSelector));

                        OW::getDocument()->addOnloadScript($script);

                        $resultArray = array(
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('mailbox', 'send_message'),
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
                        );

                        $event->add($resultArray);
                    }

/*                    return;*/
                }

                $checkResult = $this->service->checkUser(OW::getUser()->getId(), $userId);

                if (!$checkResult['isSuspended'])
                {
                    $canInvite = $this->service->getInviteToChatPrivacySettings(OW::getUser()->getId(), $userId);
                    if (!$canInvite)
                    {
                        $checkResult['isSuspended'] = true;
                        $checkResult['suspendReasonMessage'] = OW::getLanguage()->text('mailbox', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($userId)));
                    }
                }

                if ( $checkResult['isSuspended'] )
                {
                    $linkId = 'mb' . rand(10, 1000000);
                    $script = "\$('#" . $linkId . "').click(function(){

                window.OW.error(".json_encode($checkResult['suspendReasonMessage']).");

            });";

                    OW::getDocument()->addOnloadScript($script);
                }
                else
                {
                    $linkId = 'mb' . rand(10, 1000000);
                    $linkSelector = '#' . $linkId;
                    $data = $this->service->getUserInfo($userId);
                    $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                var userData = {$data};

                $.post(OWMailbox.openDialogResponderUrl, {
                    userId: userData.opponentId,
                    checkStatus: 2
                }, function(data){

                    if ( typeof data != \'undefined\'){
                        if ( typeof data[\'warning\'] != \'undefined\' && data[\'warning\'] ){
                            OW.message(data[\'message\'], data[\'type\']);
                            return;
                        }
                        else{
                            if (data[\'use_chat\'] && data[\'use_chat\'] == \'promoted\'){
                                OW.Mailbox.contactManagerView.showPromotion();
                            }
                            else{
                                OW.Mailbox.usersCollection.add(data);
                                OW.trigger(\'mailbox.open_dialog\', {convId: data[\'convId\'], opponentId: data[\'opponentId\'], mode: \'chat\'});
                            }
                        }
                    }
                }, \'json\').complete(function(){

                        $(\'#ow_chat_now_\'+userData.opponentId).removeClass(\'ow_hidden\');

                        $(\'#ow_preloader_content_\'+userData.opponentId).addClass(\'ow_hidden\');
                    });

            });', array('linkSelector'=>$linkSelector, 'data'=>$data));

                    OW::getDocument()->addOnloadScript($script);
                }

                $resultArray = array(
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('mailbox', 'chat'),
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1
                );

                $event->add($resultArray);

                return;
            }
        }

        if ( !OW::getUser()->isAuthorized('mailbox', 'send_message') || OW::getUser()->isAdmin())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'send_message');
            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $linkId = 'mb' . rand(10, 1000000);
                $linkSelector = '#' . $linkId;
                $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');

                });', array('linkSelector'=>$linkSelector));

                OW::getDocument()->addOnloadScript($script);

                $resultArray = array(
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('mailbox', 'create_conversation_button'),
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
                );

                $event->add($resultArray);
            }

            return;
        }

        $checkResult = $this->service->checkUser(OW::getUser()->getId(), $userId);

        if ( $checkResult['isSuspended'] )
        {
            $linkId = 'mb' . rand(10, 1000000);
            $script = "\$('#" . $linkId . "').click(function(){

                window.OW.error(".json_encode($checkResult['suspendReasonMessage']).");

            });";

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $linkId = 'mb' . rand(10, 1000000);
            $linkSelector = '#' . $linkId;
            $data = $this->service->getUserInfo($userId);
            $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                var data = {$data};

                OW.trigger("mailbox.open_new_message_form", data);

            });', array('linkSelector'=>$linkSelector, 'data'=>$data));

            OW::getDocument()->addOnloadScript($script);
        }

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('mailbox', 'create_conversation_button'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
        );

        $event->add($resultArray);
    }
    public function onSendMessage( OW_Event $e )
    {
        $params = $e->getParams();
        $this->emailMessageToRecipientUser($params);
        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['senderId'] ));
        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['recipientId'] ));
    }

    public function onBlockUser( OW_Event $e )
    {
        $params = $e->getParams();
        $conversationId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById( $params['userId'], $params['blockedUserId'] );

    }

    public function emailMessageToRecipientUser($params)
    {
        if(isset($params['recipientId'])) {
            $userOnlineDto=BOL_UserOnlineDao::getInstance()->findByUserId($params['recipientId']);
            if(!isset($userOnlineDto)) {
                $user = BOL_UserService::getInstance()->findUserById($params['recipientId']);
                $conversationUrl=MAILBOX_BOL_ConversationService::getInstance()->getConversationUrl($params['conversationId']);
                $body= OW::getLanguage()->text('mailbox', 'email_chat_message', array(
                    'userName' => BOL_UserService::getInstance()->getDisplayName($params['senderId']),
                    'userUrl' => BOL_UserService::getInstance()->getUserUrl($params['senderId']),
                    'conversationUrl' => $conversationUrl,
                    'message'=>$params['message']
                ));

                try{
                    $mail = OW::getMailer()->createMail();
                    $mail->addRecipientEmail($user->email);
                    $mail->setSubject(OW::getLanguage()->text('mailbox','new_message_title', ['userName'=>BOL_UserService::getInstance()->getDisplayName($params['senderId'])]));
                    $mail->setHtmlContent($body);
                    $mail->setTextContent($body);
                    OW::getMailer()->addToQueue($mail);
                }
                catch(InvalidArgumentException $e){
                    OW::getLogger()->writeLog(OW_Log::WARNING, 'cannot_send_email', ['actionType' => OW_Log::CREATE, 'enType'=>'mailbox', 'enId' => $user->getId(), 'error'=>'Cannot send email', 'exception' => $e]);
                    return false;
                }
            }
        }
    }

    public function onAvatarToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'title' => OW::getLanguage()->text('mailbox', 'mailbox'),
            'iconClass' => 'ow_ic_mail',
            'url' => OW::getRouter()->urlForRoute('mailbox_default'),
            'order' => 2
        ));
    }

    public function mailboxAdsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('mailbox');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $mailboxEvent = new OW_Event('mailbox.admin.add_auth_labels');
        OW::getEventManager()->trigger($mailboxEvent);
        $groupName = 'mailbox';

        $data = $mailboxEvent->getData();
        if (!empty($data))
        {

            $actions = $data['actions'];
        }
        else
        {

            $actions = array();

        }

        $event->add(
            array(
                'mailbox' => array(
                    'label' => $language->text('mailbox', 'auth_group_label'),
                    'actions' => $actions
                )
            )
        );
    }

    public function markConversation( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int)$params['userId'];

        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_NEW_CONVERSATION_COUNT . ($userId) ));
        //OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($userId) ));
    }

    public function deleteConversation( OW_Event $event )
    {
        $params = $event->getParams();
        $dto = $params['conversationDto'];
        /* @var $dto MAILBOX_BOL_Conversation */
        if ( $dto )
        {
            OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->initiatorId) ));
            OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->interlocutorId) ));
        }
    }

    public function consoleSendList( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];

        $conversationListByUserId = $this->service->getConversationListForConsoleNotificationMailer($userIdList);

        $conversationIdList = array();

        foreach ( $conversationListByUserId as $recipientId => $conversationList )
        {
            foreach ( $conversationList as $conversation )
            {
                $conversationIdList[$conversation['id']] = $conversation['id'];
            }
        }

        $result = $this->service->getConversationListByIdList($conversationIdList);
        $conversationList = array();

        foreach( $result as $conversation )
        {
            $conversationList[$conversation->id] = $conversation;
        }

        foreach ( $conversationListByUserId as $recipientId => $list )
        {
            foreach ( $list as $conversation )
            {
                $senderId = ($conversation['initiatorId'] == $recipientId) ? $conversation['interlocutorId'] : $conversation['initiatorId'];

                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array( $senderId ) );
                $avatar = $avatars[$senderId];

                if ($conversation['subject'] == MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT)
                {
                    $actionName = 'mailbox-new_chat_message';
                    $conversationUrl = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$senderId));
                }
                else
                {
                    $actionName = 'mailbox-new_message';
                    $conversationUrl = $this->service->getConversationUrl($conversation['id']);
                }

                $trigger = true;
                $content = $conversation['text'];
                $contentImageSrc   = null;
                $contentImageUrl   = null;
                $contentImageTitle = null;

                // try to render the system message
                if ( $conversation['isSystem'] )
                {
                    $textParams = json_decode($content, true);

                    if ( $textParams['entityType'] == 'mailbox' && $textParams['eventName'] == 'renderOembed' )
                    {
                        $content = !empty($textParams['params']['message'])
                            ? $textParams['params']['message']
                            : null;

                        $contentImageSrc = !empty($textParams['params']['thumbnail_url'])
                            ? $textParams['params']['thumbnail_url']
                            : null;

                        $contentImageUrl = !empty($textParams['params']['href'])
                            ? $textParams['params']['href']
                            : null;

                        $contentImageTitle = !empty($textParams['params']['title'])
                            ? $textParams['params']['title']
                            : null;
                    }

                    if (!$contentImageSrc)
                    {
                        $trigger = false;
                    }
                }

                if ( $trigger )
                {
                    $event->add(array(
                        'pluginKey' => 'mailbox',
                        'entityType' => 'mailbox-conversation',
                        'entityId' => $conversation['id'],
                        'userId' => $recipientId,
                        'action' => $actionName,
                        'time' => $conversation['timeStamp'],

                        'data' => array(
                            'avatar' => $avatar,
                            'string' => OW::getLanguage()->text('mailbox', 'email_notifications_comment', array(
                                'userName' => BOL_UserService::getInstance()->getDisplayName($senderId),
                                'userUrl' => BOL_UserService::getInstance()->getUserUrl($senderId),
                                'conversationUrl' => $conversationUrl
                            )),
                            'content' => $content,
                            'contentImage' => array(
                                'src' => $contentImageSrc,
                                'url' => $contentImageUrl,
                                'title' => $contentImageTitle
                            )
                        )
                    ));
                }

                if ( !empty($conversationList[$conversation['id']]) )
                {
                    $conversationList[$conversation['id']]->notificationSent = 1;
                    $this->service->saveConversation($conversationList[$conversation['id']]);
                }
            }
        }
    }

    public function onPluginInit()
    {
        $handlerAttributes = OW::getRequestHandler()->getHandlerAttributes();
        $event = new OW_Event('plugin.mailbox.on_plugin_init.handle_controller_attributes', array('handlerAttributes'=>$handlerAttributes));
        OW::getEventManager()->trigger($event);

        $handleResult = $event->getData();

        if ($handleResult === false)
        {
            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        else
        {

            if ( !BOL_UserService::getInstance()->isApproved() )
            {
                return;
            }

            $user = OW::getUser()->getUserObject();

            if (BOL_UserService::getInstance()->isSuspended($user->getId()))
            {
                return;
            }

            if ( (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
            {
                return;
            }
        }

        $im_toolbar = new MAILBOX_CMP_Toolbar();
        OW::getDocument()->appendBody($im_toolbar->render());
    }

    public function onHandleControllerAttributes( OW_Event $event )
    {
        $params = $event->getParams();

        $handlerAttributes = $params['handlerAttributes'];

        if ($handlerAttributes['controller'] == 'BASE_CTRL_MediaPanel')
        {
            $event->setData(false);
        }

        if ($handlerAttributes['controller'] == 'SUPPORTTOOLS_CTRL_Client')
        {
            $event->setData(false);
        }
    }

    public function onCollectPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $activeModes = $this->service->getActiveModeList();

        if (in_array('chat', $activeModes))
        {
            $privacyValueEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD, array('key' => 'mailbox_invite_to_chat')));
            $defaultValue = 'everybody';
            if(isset($privacyValueEvent->getData()['value'])){
                $defaultValue = $privacyValueEvent->getData()['value'];
            }
            $action = array(
                'key' => 'mailbox_invite_to_chat',
                'pluginKey' => 'mailbox',
                'label' => $language->text('mailbox', 'privacy_action_invite_to_chat'),
                'description' => $language->text('mailbox', 'privacy_action_invite_to_chat_description'),
                'defaultValue' => $defaultValue
            );
            $event->add($action);
        }
    }

    public function onShowOnlineButton( OW_Event $event )
    {
        $params = $event->getParams();

        if (empty($params['userId']))
            return false;

        $blockedByUsers = array();
        $blockedUsers = array();
        if (isset($params['blockedByUsers'])) {
            $blockedByUsers = $params['blockedByUsers'];
        }
        if (isset($params['blockedUsers'])) {
            $blockedUsers = $params['blockedUsers'];
        }

        $activeModes = $this->service->getActiveModeList();

        if (!in_array('chat', $activeModes))
        {
            return false;
        }

        $isBlocked = false;
        if (isset($params['blockedByUsers'])) {
            // Its for current user
            if (isset($blockedUsers[$params['onlineUserId']]) && $blockedUsers[$params['onlineUserId']]) {
                $isBlocked = true;
            }
            if (isset($blockedByUsers[$params['onlineUserId']]) && $blockedByUsers[$params['onlineUserId']]) {
                $isBlocked = true;
            }
        } else {
            $isBlocked = BOL_UserService::getInstance()->isBlocked($params['userId'], $params['onlineUserId']);
        }
        if ($isBlocked)
        {
            return false;
        }

        $eventParams = array(
            'action' => 'mailbox_invite_to_chat',
            'ownerId' => $params['onlineUserId'],
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }


        return true;
    }

    public function onApiPing( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $model = new MAILBOX_CLASS_Model();
        $model->updateWithData($params);

        $data = $event->getData();

        if (empty($data))
        {
            $data = array();
            $data['mailbox'] = $model->getResponse();
        }
        else if (is_array($data))
        {
            $data['mailbox'] = $model->getResponse();
        }

        $event->setData($data);
    }

    public function onPing( OW_Event $event )
    {
        $eventParams = $event->getParams();
        $params = $eventParams['params'];
        $socketEnabled = FRMSecurityProvider::isSocketEnable(true);


        if ($eventParams['command'] == 'mailbox_api_ping')
        {
            return $this->onApiPing($event);
        }

        if ($eventParams['command'] != 'mailbox_ping')
        {
            return;
        }

        if ( empty(OW_Session::getInstance()->get('lastRequestTimestamp')) )
        {
            OW_Session::getInstance()->set('lastRequestTimestamp', (int)$params['lastRequestTimestamp']);
        }

        if ( ((int)$params['lastRequestTimestamp'] - (int) OW_Session::getInstance()->get('lastRequestTimestamp')) < 3 )
        {
            $event->setData(array('error'=>"Too much requests"));
        }

        OW_Session::getInstance()->set('lastRequestTimestamp', (int)$params['lastRequestTimestamp']);

        if ( !OW::getUser()->isAuthenticated() )
        {
            $event->setData(array('error'=>"You have to sign in"));
        }

        if ( !OW::getRequest()->isAjax() )
        {
            $event->setData(array('error'=>"Ajax request required"));
        }

        $userId = OW::getUser()->getId();

        /** SET **/

        if (!empty($params['readMessageList']))
        {
            $this->service->markMessageIdListRead($params['readMessageList'], $params['lastRequestTimestamp']);
            $this->service->resetUserLastData($userId);
        }

        if (!empty($params['viewedConversationList']))
        {
            $this->service->setConversationViewedInConsole($params['viewedConversationList'], OW::getUser()->getId());
            $this->service->resetUserLastData($userId);
        }

        $ajaxActionResponse = array();
        if (!empty($params['ajaxActionData']))
        {
            $this->service->resetUserLastData($userId);

            $cachedParams = array();
            $conversationIds = array();
            foreach($params['ajaxActionData'] as $action) {
                if (isset($action['data']['convId'])) {
                    $conversationIds[] = $action['data']['convId'];
                }
            }
            $cachedParams['cache']['conversations_items'] = MAILBOX_BOL_ConversationDao::getInstance()->getConversationsItem($conversationIds);
            $cachedParams['cache']['conversations'] = MAILBOX_BOL_ConversationDao::getInstance()->findByConversationIds($conversationIds);

            foreach($params['ajaxActionData'] as $action)
            {
                switch($action['name'])
                {
                    case 'postMessage':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->postMessage($action['data']);

                        if (!empty($ajaxActionResponse[$action['uniqueId']]['message']))
                        {
                            $params['lastMessageTimestamp'] = $ajaxActionResponse[$action['uniqueId']]['message']['timeStamp'];
                        }
                        break;
                    case 'getLog':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->getLog($action['data'], $cachedParams);
                        break;
                    case 'markConversationUnRead':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->markConversationUnRead($action['data']);
                        break;
                    case 'markConversationRead':
                        $this->ajaxService->markConversationRead($action['data']);
                        break;
                    case 'loadMoreConversations':

                        if (isset($action['data']['searching']) && $action['data']['searching'] == 1)
                        {
                            $conversationIds = MAILBOX_BOL_ConversationDao::getInstance()->findConversationByKeyword($action['data']['kw'], 8, $action['data']['from']);
                            $ajaxActionResponse[$action['uniqueId']] = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdList( $conversationIds );
                        }
                        else
                        {
                            $ajaxActionResponse[$action['uniqueId']] = $this->service->getConversationListByUserId( OW::getUser()->getId(), $action['data']['from'], 10 );
                        }
                        break;
                    case 'bulkActions':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->bulkActions($action['data']);
                        break;
                }
            }
        }
        /** **/

        /** GET **/
        $response = $this->service->getLastDataAlt($params);
        if (!empty($ajaxActionResponse))
        {
            $response['ajaxActionResponse'] = $ajaxActionResponse;
        }

        $markedUnreadConversationList = $this->service->getMarkedUnreadConversationList( OW::getUser()->getId() );
        if (count($markedUnreadConversationList) > 0)
        {
            $response['markedUnreadConversationList'] = $markedUnreadConversationList;
        }

        if(isset($params['currentMessageList']) || !empty($params['currentMessageList'])){
            $currentMessageListIds = $params['currentMessageList'];
            foreach ($currentMessageListIds as $currentMessageListId){
                $opponentUnreadMessageData[$currentMessageListId] = array();
                $opponentLastDataId[$currentMessageListId] = -1;
            }

            if(!$socketEnabled && sizeof($currentMessageListIds) > 0) {
                $userOpponentsUnreadMessage = $this->service->findUserOpponentsUnreadMessages($userId, $currentMessageListIds);
                foreach ($userOpponentsUnreadMessage as $message){
                    $opponentUnreadMessageData[$message->recipientId][] = $message->id;
                }
                $response['opponentUnreadMessageData'] = $opponentUnreadMessageData;

                $findUserOpponentsLastReadMessages = MAILBOX_BOL_ConversationService::getInstance()->findLastOpponentReadMessageByConversationIdListAndUserIdList( $userId, $currentMessageListIds );
                foreach ($findUserOpponentsLastReadMessages as $message){
                    $opponentLastDataId[$message['recipientId']] = $message['id'];
                }
                $response['opponentLastDataId'] = $opponentLastDataId;
            }
        }

        /** **/
        if(!$socketEnabled && isset($params['currentOpponentId']) && $params['currentOpponentId'] > 0){
            $opponentId = $params['currentOpponentId'];
            $conversationId = MAILBOX_BOL_ConversationDao::getInstance()->findChatConversationIdWithUserById(OW::getUser()->getId(),$opponentId);
            $changedMessages = MAILBOX_BOL_ConversationService::getInstance()->getConversationChanges($conversationId);
            if (isset($changedMessages) && isset($changedMessages['changed'])) {
                $response['changes'] = array();
                foreach ($changedMessages['changed'] as $message) {
                    $response['changes'][] = array('id'=>$message->id,'text'=>MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($message->text));
                }
            }
            if (isset($changedMessages) && isset($changedMessages['deleted'])) {
                $response['deleted'] = array();
                foreach ($changedMessages['deleted'] as $message) {
                    $response['deleted'][] = array('id'=>$message->deletedId);
                }
            }
        }

        $event->setData($response);
    }

    public function onAttachmentUpload( OW_Event $event )
    {
        $params = $event->getParams();

        if ($params['pluginKey'] != 'mailbox')
        {
            return;
        }

        //mailbox_dialog_{convId}_{opponentId}_{hash}
        $uidParams = explode('_', $params['uid']);

        if (count($uidParams) != 5)
        {
            return;
        }

        if ($uidParams[0] != 'mailbox')
        {
            return;
        }

        if ($uidParams[1] != 'dialog' && $uidParams[1] != 'conversation')
        {
            return;
        }

        $conversationId = $uidParams[2];
        $userId = OW::getUser()->getId();
//        $opponentId = $uidParams[3];

        $files = $params['files'];
        if (!empty($files))
        {
            if ($conversationId == "0"){
                $conversationId = get_object_vars($this->service->findConversationList(OW::getUser()->getId(), $uidParams[3])[0])['id'];
            }
            $conversation = $this->service->getConversation($conversationId);
            try
            {
                $messages = array();
                $i=0;
                foreach ($files as $file)
                {
                    if($i==0)
                    {
                        $caption = !empty($_POST['caption'])?$_POST['caption']:OW::getLanguage()->text('mailbox', 'attachment');
                    }else{
                        $caption = OW::getLanguage()->text('mailbox', 'attachment');
                    }
                    $message = $this->service->createMessage($conversation, $userId, $caption);
                    $messages[] = $message;
                    $this->service->addMessageAttachments($message->id, array($file));
                    $i++;
                }
                $this->service->addMessageAttachmentsThumbnails($messages, $params['thumbnails']);
            }
            catch(InvalidArgumentException $e)
            {

            }
        }
    }

    public function onRenderOembed( OW_Event $event )
    {
        $params = $event->getParams();

        if (isset($params['getMessage']) && $params['getMessage'])
        {
            $content = $params['message'];
        } else if (isset($params['getPreview']) && $params['getPreview'])
        {
            $content = $params['href'];
        }
        else
        {
            $tempCmp = new MAILBOX_CMP_OembedAttachment($params['message'], $params);
            $content = $tempCmp->render();
        }
        $event->setData($content);
    }

    public function onCollectConsoleItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if (OW::getUser()->isAuthenticated())
        {
            $item = new MAILBOX_CMP_ConsoleMailbox();
            $event->addItem($item, 4);
        }
    }

    public function onLoadConsoleList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $userId = OW::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $conversations = $this->service->getConsoleConversationList($userId, 0, 8, $params['console']['time'], $params['ids']);

        $conversationIdList = array();
        foreach ( $conversations as $conversationData )
        {
            if (!in_array($conversationData['conversationId'], $conversationIdList))
            {
                $conversationIdList[] = $conversationData['conversationId'];
            }

            $mode = $this->service->getConversationMode($conversationData['conversationId']);
            $conversationItem = $this->service->getConversationItem($mode, $conversationData['conversationId']);
            $item = new MAILBOX_CMP_ConsoleMessageItem($conversationItem);

            $event->addItem($item->render(), $conversationData['conversationId']);
        }

        $this->service->setConversationViewedInConsole($conversationIdList, $userId);
    }

    /**
     * Application event methods
     */
    public function getUnreadMessageCount( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $ignoreList = !empty($params['ignoreList']) ? (array)$params['ignoreList'] : array();
        $time = !empty($params['time']) ? (int)$params['time'] : time();

        $data = $this->service->getUnreadMessageCount($userId, $ignoreList, $time);

        $event->setData( $data );

        return $data;
    }

    public function getChatUserList( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        $from = 0;
        $count = 10;

        if (isset($params['from']))
        {
            $from = (int)$params['from'];
        }

        if (isset($params['count']))
        {
            $count = (int)$params['count'];
        }

        $list = $this->service->getChatUserList($userId, $from, $count);
        $event->setData( $list );

        return $list;
    }

    public function postMessage( OW_Event $event )
    {
       $params = $event->getParams();

        if (empty($params['mode']) && empty($params['conversationId']))
        {
            $data = array('error'=>true, 'message'=>'Undefined conversation');
            $event->setData($data);
            return $data;
        }

        $checkResult = $this->service->checkUser($params['userId'], $params['opponentId']);

        if ($checkResult['isSuspended'])
        {
            $data = array('error'=>true, 'message'=>$checkResult['suspendReasonMessage'], 'suspendReason'=>$checkResult['suspendReason']);

            $event->setData($data);
            return $data;
        }

            $conversationId = $this->service->getChatConversationIdWithUserById($params['userId'], $params['opponentId']);



        if (!empty($params['mode']) && $params['mode'] == 'chat')
        {
            if (empty($conversationId))
            {
                $conversation = $this->service->createChatConversation($params['userId'], $params['opponentId']);
                $conversationId = $conversation->getId();
            }

            $conversation = $this->service->getConversation($conversationId);

            $isSystem = isset($params['isSystem']) && $params['isSystem'];
            $message = $this->service->createMessage($conversation, $params['userId'], $params['text'], null,$isSystem);

            $this->service->markUnread(array($conversationId), $params['opponentId']);

            $messageData = $this->service->getMessageDataForApi($message);

            $data = array('error'=>false, 'message'=>$messageData);

            $event->setData($data);


            return $data;
        }
    }

    public function postReplyMessage( OW_Event $event )
    {
       $params = $event->getParams();

        if (empty($params['mode']) && empty($params['conversationId']))
        {
            $data = array('error'=>true, 'message'=>'Undefined conversation');
            $event->setData($data);
            return $data;
        }

        $checkResult = $this->service->checkUser($params['userId'], $params['opponentId']);

        if ($checkResult['isSuspended'])
        {
            $data = array('error'=>true, 'message'=>$checkResult['suspendReasonMessage'], 'suspendReason'=>$checkResult['suspendReason']);

            $event->setData($data);
            return $data;
        }

        $conversationId = $params['conversationId'];


        if (!empty($params['mode']) && $params['mode'] == 'mail')
        {
            $conversation = $this->service->getConversation($conversationId);

            $message = $this->service->createMessage($conversation, $params['userId'], $params['text']);

            if ( isset($params['isSystem']) && $params['isSystem'] )
            {
                $this->service->markMessageAsSystem($message->id);
            }

            $this->service->markUnread(array($conversationId), $params['opponentId']);

            $messageData = $this->service->getMessageDataForApi($message);

            $data = array('error'=>false, 'message'=>$messageData);

            $event->setData($data);


            return $data;
        }
    }

    public function getNewMessages( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $lastMessageTimestamp = $params['lastMessageTimestamp'];

        $data = $this->service->getChatNewMessages($userId, $opponentId, $lastMessageTimestamp);

        $event->setData($data);

        return $data;
    }

    public function getNewMessagesForConversation( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['conversationId']) )
        {
            $event->setData(array());
            
            return array();
        }

        $conversationId = (int)$params['conversationId'];
        $lastMessageTimestamp = !empty($params['lastMessageTimestamp']) ? (int)$params['lastMessageTimestamp'] : null;
        $messages = $this->service->getNewMessagesForConversation($conversationId, $lastMessageTimestamp);
        $event->setData($messages);

        return $messages;
    }

    public function getMessages( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        
        if ( empty($params['conversationId']) ) // Backward compatibility
        {
            if ( !empty($params['opponentId']) )
            {
                $conversationId = $this->service->getChatConversationIdWithUserById($userId, $params['opponentId']);
            }
        }
        else
        {
            $conversationId = $params['conversationId'];
        }
        
        $data = $this->service->getMessagesForApi($userId, $conversationId);

        $event->setData($data);

        return $data;
    }

    public function getHistory( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $beforeMessageId = $params['beforeMessageId'];

        $data = array();

        $conversationId = $this->service->getChatConversationIdWithUserById($userId, $opponentId);
        if ($conversationId)
        {
            $data = $this->service->getConversationHistoryForApi($conversationId, $beforeMessageId);
        }

        $event->setData($data);

        return $data;
    }
    /**
     *
     */

    public function showSendMessageButton( OW_Event $event )
    {
        $event->setData(true);
    }

    public function onFriendRequestAccepted(OW_Event $event)
    {
        $params = $event->getParams();

        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['senderId']);
        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['recipientId']);
    }

    public function resetAllUsersLastData(OW_Event $event)
    {
        MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();
    }

    public function onUserUnregister(OW_Event $event)
    {
        $params = $event->getParams();

        if(!isset($params['userId']))
        {
            return;
        }
        MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();

        $userId = (int) $params['userId'];

        $messageList = MAILBOX_BOL_MessageDao::getInstance()->findUserSentUnreadMessages($userId);
        $messageIdList = array();
        /**
         * @var MAILBOX_BOL_Message $message
         */
        foreach($messageList as $message)
        {
            MAILBOX_BOL_ConversationService::getInstance()->markMessageIdListReadByUser(array($message->id), $message->recipientId);
        }
    }

    public function messageAndConversationDeleteOnUserRemove(OW_Event $event)
    {
        $params = $event->getParams();

        if(isset($params['deleteContent']) && $params['deleteContent'])
        {
            $userID=$params['userId'];

            MAILBOX_BOL_ConversationDao::getInstance()->deleteSentConversationsByUserId($userID);
            MAILBOX_BOL_ConversationDao::getInstance()->deleteReceivedConversationsByUserId($userID);

            MAILBOX_BOL_MessageDao::getInstance()->deleteSentMessagesByUserId($userID);
            MAILBOX_BOL_MessageDao::getInstance()->deleteReceivedMessagesByUserId($userID);
        }

    }
    
    public function onChangeUserAvatar(OW_Event $event)
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['userId']);
        }
    }

    public function onMarkAsRead( OW_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->markRead(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function onMarkUnread( OW_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->markUnread(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function getConversationId( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['opponentId']) )
        {
            $event->setData(null);

            return null;
        }

        $userId = (int)$params['userId'];
        $opponentId = (int)$params['opponentId'];

        $conversationId = $this->service->getChatConversationIdWithUserById($userId, $opponentId);
        $event->setData($conversationId);

        return $conversationId;
    }
    
    public function onDeleteConversation( OW_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->deleteConversation(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function onCreateConversation( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $text = $params['text'];
        $subject = $params['subject'];

        $userSendMessageIntervalOk = $this->service->checkUserSendMessageInterval($userId);

        if ( !$userSendMessageIntervalOk )
        {
            $send_message_interval = (int)OW::getConfig()->getValue('mailbox', 'send_message_interval');
            throw new InvalidArgumentException(OW::getLanguage()->text('mailbox', 'feedback_send_message_interval_exceed', array('send_message_interval'=>$send_message_interval)));
        }

        // check recipient's blocked status
        $isBlocked  = BOL_UserService::getInstance()->isBlocked($userId, $opponentId);

        if ( $isBlocked )
        {
            throw new InvalidArgumentException(OW::getLanguage()->text('base', 'user_cant_chat_with_this_user'));
        }

        $conversation = $this->service->createConversation($userId, $opponentId, $subject, $text);

        $event->setData($conversation);

        return $conversation;
    }

    public function onGetActiveModeList( OW_Event $event )
    {
        $activeModeList = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $event->setData($activeModeList);

        return $activeModeList;
    }

    public function onAuthorizeAction( OW_Event $event )
    {
        $params = $event->getParams();
        $result = $this->ajaxService->authorizeActionForApi( $params );
        $event->setData($result);
        return $result;
    }

    public function onFindUser( OW_Event $event )
    {
        $result = array();
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $event->setData($result);
            return $result;
        }

        $kw = empty($params['term']) ? null : $params['term'];
        $idList = empty($params['idList']) ? null : $params['idList'];

        $context = empty($params["context"]) ? 'api' : $params["context"];
        $userId = OW::getUser()->getId();

        $result = $this->ajaxService->getSuggestEntries($userId, $kw, $idList, $context);

        $event->setData($result);
        return $result;
    }
}