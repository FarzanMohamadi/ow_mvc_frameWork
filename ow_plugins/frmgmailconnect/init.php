<?php
$plugin = OW::getPluginManager()->getPlugin('frmgmailconnect');


OW::getRouter()->addRoute(new OW_Route('frmgmailconnect_oauth', 'frmgmailconnect/oauth', 'FRMGMAILCONNECT_CTRL_Connect', 'oauth'));
OW::getRouter()->addRoute(new OW_Route('frmgmailconnect_admin_main','admin/plugins/frmgmailconnect','FRMGMAILCONNECT_CTRL_Admin', 'index'));

$configs = OW::getConfig()->getValues('frmgmailconnect');
$whoCanJoin = OW::getConfig()->getValue('base', 'who_can_join');
if (!empty($configs['client_id']) && !empty($configs['client_secret']) && $whoCanJoin!= BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS) {
    $registry = OW::getRegistry();
    $registry->addToArray(BASE_CTRL_Join::JOIN_CONNECT_HOOK, array(new FRMGMAILCONNECT_CMP_ConnectButton(), 'render'));
    $registry->addToArray(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, array(new FRMGMAILCONNECT_CMP_ConnectButton(), 'render'));
}

$eventHandler = new FRMGMAILCONNECT_CLASS_EventHandler();
$eventHandler->init();