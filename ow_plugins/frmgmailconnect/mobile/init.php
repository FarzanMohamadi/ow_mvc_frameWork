<?php
$plugin = OW::getPluginManager()->getPlugin('frmgmailconnect');


OW::getRouter()->addRoute(new OW_Route('frmgmailconnect_oauth', 'frmgmailconnect/oauth', 'FRMGMAILCONNECT_MCTRL_Connect', 'oauth'));

$configs = OW::getConfig()->getValues('frmgmailconnect');
if ( !empty($configs['client_id']) && !empty($configs['client_secret']) ) {
    $registry = OW::getRegistry();
    $registry->addToArray(BASE_CTRL_Join::JOIN_CONNECT_HOOK, array(new FRMGMAILCONNECT_MCMP_ConnectButton(), 'render'));
    $registry->addToArray(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, array(new FRMGMAILCONNECT_MCMP_ConnectButton(), 'render'));
}

$eventHandler = new FRMGMAILCONNECT_MCLASS_EventHandler();
$eventHandler->init();