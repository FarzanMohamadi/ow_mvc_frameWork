<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.6.1
 */
OW::getRouter()->addRoute(new OW_Route('mailbox_user_list', 'mailbox/users', 'MAILBOX_CTRL_Mailbox', 'users'));
OW::getRouter()->addRoute(new OW_Route('mailbox_conv_list', 'mailbox/convs', 'MAILBOX_CTRL_Mailbox', 'convs'));
OW::getRouter()->addRoute(new OW_Route('mailbox_chat_conversation', 'messages/chat/:userId', 'MAILBOX_MCTRL_Messages', 'chatConversation'));
OW::getRouter()->addRoute(new OW_Route('mailbox_mail_conversation', 'messages/mail/:convId', 'MAILBOX_MCTRL_Messages', 'mailConversation'));
OW::getRouter()->addRoute(new OW_Route('mailbox_compose_mail_conversation', 'messages/compose/:opponentId', 'MAILBOX_MCTRL_Messages', 'composeMailConversation'));
OW::getRouter()->addRoute(new OW_Route('mailbox_ajax_remove_message', 'mailbox/ajax/remove_message', 'MAILBOX_CTRL_Ajax', 'removeMessage'));
OW::getRouter()->addRoute(new OW_Route('mailbox_edit_message', 'mailbox/ajax/edit_message', 'MAILBOX_CTRL_Ajax', 'editMessage'));

$eventHandler = new MAILBOX_CLASS_EventHandler();
$eventHandler->genericInit();

OW::getEventManager()->bind(BASE_CTRL_Ping::PING_EVENT . '.mobileMailboxConsole', array($eventHandler, 'onPing'));

$eventHandler = new MAILBOX_MCLASS_EventHandler();
$eventHandler->init();