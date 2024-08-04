<?php
class FRMGMAILCONNECT_MCLASS_EventHandler
{


    public function genericInit()
    {
        $frmGmailConnectService = FRMGMAILCONNECT_BOL_Service::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($frmGmailConnectService, "afterUserRegistered"));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_EDIT, array($frmGmailConnectService, "afterUserSynchronized"));
        OW::getEventManager()->bind(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST,array($frmGmailConnectService, 'connectEventAddButton'));
        OW::getEventManager()->bind('admin.add_admin_notification',array($frmGmailConnectService, 'connectAddAdminNotification'));
        OW::getEventManager()->bind('base.members_only_exceptions',array($frmGmailConnectService, 'connectAddAccessException'));
        OW::getEventManager()->bind('base.password_protected_exceptions', array($frmGmailConnectService, 'connectAddAccessException'));
        OW::getEventManager()->bind('base.splash_screen_exceptions', array($frmGmailConnectService,'connectAddAccessException'));
    }

    public function init()
    {
        $this->genericInit();
    }
}