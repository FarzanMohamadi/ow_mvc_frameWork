<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.0
 */
$plugin = OW::getPluginManager()->getPlugin('mailbox');

$classesToAutoload = array(
    'CreateConversationForm' => $plugin->getRootDir() . 'classes' . DS . 'create_conversation_form.php',
);

OW::getAutoloader()->addClassArray($classesToAutoload);

OW::getRouter()->addRoute(new OW_Route('mailbox_messages_default', 'messages', 'MAILBOX_CTRL_Messages', 'index'));

OW::getRouter()->addRoute(new OW_Route('mailbox_default', 'mailbox', 'MAILBOX_CTRL_Messages', 'index'));

OW::getRouter()->addRoute(new OW_Route('mailbox_conversation', 'messages/mail/:convId', 'MAILBOX_CTRL_Messages', 'index'));
OW::getRouter()->addRoute(new OW_Route('mailbox_file_upload', 'mailbox/conversation/:entityId/:formElement', 'MAILBOX_CTRL_Mailbox', 'fileUpload'));
//OW::getRouter()->addRoute(new OW_Route('mailbox_admin_config', 'admin/plugins/mailbox', 'MAILBOX_CTRL_Admin', 'index'));

OW::getRouter()->addRoute(new OW_Route('mailbox_chat_conversation', 'messages/chat/:userId', 'MAILBOX_CTRL_Messages', 'chatConversation'));
OW::getRouter()->addRoute(new OW_Route('mailbox_mail_conversation', 'messages/mail/:convId', 'MAILBOX_CTRL_Messages', 'index'));

OW::getRouter()->addRoute(new OW_Route('mailbox_user_list', 'mailbox/users', 'MAILBOX_CTRL_Mailbox', 'users'));
OW::getRouter()->addRoute(new OW_Route('mailbox_conv_list', 'mailbox/convs', 'MAILBOX_CTRL_Mailbox', 'convs'));

OW::getRouter()->addRoute(new OW_Route('mailbox_ajax_autocomplete', 'mailbox/ajax/autocomplete', 'MAILBOX_CTRL_Ajax', 'autocomplete'));
OW::getRouter()->addRoute(new OW_Route('mailbox_compose_mail_conversation', 'messages/compose/:opponentId', 'MAILBOX_MCTRL_Messages', 'composeMailConversation'));

OW::getRouter()->addRoute(new OW_Route('mailbox_ajax_remove_message', 'mailbox/ajax/remove_message', 'MAILBOX_CTRL_Ajax', 'removeMessage'));
OW::getRouter()->addRoute(new OW_Route('mailbox_edit_message', 'mailbox/ajax/edit_message', 'MAILBOX_CTRL_Ajax', 'editMessage'));
OW::getRouter()->addRoute(new OW_Route('mailbox_search_content.mailbox_responder', 'mailbox/ajax/responder', 'MAILBOX_CTRL_Ajax', 'mailbox_responder'));

$eventHandler = new MAILBOX_CLASS_EventHandler();
$eventHandler->genericInit();
$eventHandler->init();