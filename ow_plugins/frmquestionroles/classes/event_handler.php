<?php
/**
 * frmquestionroles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

class FRMQUESTIONROLES_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function init()
    {
        $service = FRMQUESTIONROLES_BOL_Service::getInstance();
        OW::getEventManager()->bind('base.add_main_console_item', array($service, 'addRoleManagementConsoleItem'));
        OW::getEventManager()->bind(FRMEventManager::HAS_USER_AUTHORIZE_TO_MANAGE_USERS, array($service, 'hasUserAuthorizeToManageUsers'));
        OW::getEventManager()->bind(FRMEventManager::FIND_MODERATOR_FOR_USER, array($service, 'findModeratorForUser'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        OW::getEventManager()->bind('frmquestionroles.getUserRolesToManageSpecificUsers', array($service, 'getUserRolesToManageSpecificUsersEvent'));
        OW::getEventManager()->bind('frmquestionroles.getUsersByRolesData', array($service, 'getUsersByRolesDataEvent'));
    }
}