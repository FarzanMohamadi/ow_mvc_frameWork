<?php
class MAILBOX_MCLASS_EventHandler
{
    const CONSOLE_ITEM_KEY = 'mailbox';
    const CONSOLE_PAGE_KEY = 'convers';

    public function init()
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        OW::getEventManager()->bind(MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGES, array($this, 'onConsolePagesCollect'));
        OW::getEventManager()->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, "onCollectProfileActions"));

        OW::getEventManager()->bind('mailbox.renderOembed', array($this, 'onRenderOembed'));
        OW::getEventManager()->bind('mailbox.send_message', array($conversationService, 'onSendMessageWebSocket'));
        OW::getEventManager()->bind('mailbox.send_message_attachment', array($conversationService, 'onSendMessageAttachmentWebSocket'));
        OW::getEventManager()->bind("mailbox.after_message_removed", array($conversationService, "onAfterMessageRemoved"));
        OW::getEventManager()->bind("mailbox.after_message_edited", array($conversationService, "onAfterMessageEdited"));
        OW::getEventManager()->bind("mailbox.mark_conversation", array($conversationService, "onMarkConversationWebSocket"));

//        OW::getEventManager()->bind(MBOL_ConsoleService::EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS, array($this, 'countNewItems'));
    }

    public function onCollectProfileActions( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        if ( !OW::getUser()->isAuthenticated() || OW::getUser()->getId() == $userId )
        {
            return;
        }

        $activeModes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        if (in_array('mail', $activeModes))
        {
            $linkId = FRMSecurityProvider::generateUniqueId('send_message');

            $script = UTIL_JsGenerator::composeJsString('
            $("#' . $linkId . '").click(function()
            {
                if ( {$isBlocked} )
                {
                    OWM.error({$blockError});
                    return false;
                }
            });
        ', array(
                'isBlocked' => BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId),
                'blockError' => OW::getLanguage()->text('base', 'user_block_message')
            ));

            OW::getDocument()->addOnloadScript($script);

            $event->add(array(
                "label" => OW::getLanguage()->text('mailbox', 'auth_action_label_send_message'),
                "href" => OW::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$userId)),
                "id" => $linkId
            ));
        }

        if (in_array('chat', $activeModes))
        {
            $userService = BOL_UserService::getInstance();

            $showPresence = true;
            // Check privacy permissions
            $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerId' => $userId,
                'viewerId' => OW::getUser()->getId()
            );
            try
            {
                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $e )
            {
                $showPresence = false;
            }
//            if($userService->findOnlineUserById($userId) && $showPresence) {
                $allowChat = OW::getEventManager()->call('base.online_now_click', array('userId' => OW::getUser()->getId(), 'onlineUserId' => $userId));
                if ($allowChat) {
                    $linkId = FRMSecurityProvider::generateUniqueId('send_chat');

                    $script = UTIL_JsGenerator::composeJsString('
            $("#' . $linkId . '").click(function()
            {
                if ( {$isBlocked} )
                {
                    OWM.error({$blockError});
                    return false;
                }
            });
        ', array(
                        'isBlocked' => BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId),
                        'blockError' => OW::getLanguage()->text('base', 'user_block_message')
                    ));

                    OW::getDocument()->addOnloadScript($script);

                    $event->add(array(
                        "label" => OW::getLanguage()->text('base', 'user_list_chat_now'),
                        "href" => OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId' => $userId)),
                        "id" => $linkId
                    ));

                }
//            }
        }
    }

    public function onConsolePagesCollect(BASE_CLASS_EventCollector $event)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'underscore-min.js', 'text/javascript', 3000);
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'backbone-min.js', 'text/javascript', 3000);
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'backbone.js', 'text/javascript', 3000 );

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'mobile_mailbox.js', 'text/javascript', 3000);

        $userListUrl = OW::getRouter()->urlForRoute('mailbox_user_list');
        $convListUrl = OW::getRouter()->urlForRoute('mailbox_conv_list');
        $authorizationResponderUrl = OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'authorization');
        $pingResponderUrl = OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'ping');
        $getHistoryResponderUrl = OW::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'getHistory');
        $userId = OW::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
        $profileUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $lastSentMessage = MAILBOX_BOL_ConversationService::getInstance()->getLastSentMessage($userId);
        $lastMessageTimestamp = (int)($lastSentMessage ? $lastSentMessage->timeStamp : 0);

        $addToRightMenu = true;
        $menuEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.on.before.conversation.page.add'));
        if (isset($menuEvent->getData()['add'])) {
            $addToRightMenu = $menuEvent->getData()['add'];
        }

        $params = array(
            'getHistoryResponderUrl' => $getHistoryResponderUrl,
            'pingResponderUrl' => $pingResponderUrl,
            'authorizationResponderUrl' => $authorizationResponderUrl,
            'userListUrl' => $userListUrl,
            'convListUrl' => $convListUrl,
            'pingInterval' => FRMSecurityProvider::getDefaultPingIntervalInSeconds() * 1000,
            'lastMessageTimestamp' => $lastMessageTimestamp,
            'addConverse' => $addToRightMenu,
            'user' => array(
                'userId' => $userId,
                'displayName' => $displayName,
                'profileUrl' => $profileUrl,
                'avatarUrl' => $avatarUrl
            )
        );

        $js = UTIL_JsGenerator::composeJsString('OWM.Mailbox = new MAILBOX_Mobile({$params});', array('params' => $params));
        OW::getDocument()->addOnloadScript($js, 0);

        if ($addToRightMenu){
            $event->add(array(
                'key' => 'convers',
                'cmpClass' => 'MAILBOX_MCMP_ConsoleConversationsPage',
                'order' => 2
            ));
        }
    }

    public function onRenderOembed( OW_Event $event )
    {
        $params = $event->getParams();
        if (isset($params['getMessage']) && $params['getMessage'])
        {
            $content = $params['message'];
        } else {
            $tempCmp = new MAILBOX_CMP_OembedAttachment($params['message'], $params);
            $content = $tempCmp->render();
        }
        $event->setData($content);
    }

//    public function countNewItems( OW_Event $event )
//    {
//        $params = $event->getParams();
//
//        if ( $params['page'] == self::CONSOLE_PAGE_KEY )
//        {
//            $event->add(
//                array('mailbox' => 12)
//            );
//        }
//    }
}

