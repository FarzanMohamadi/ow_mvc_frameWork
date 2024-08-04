<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordchangeinterval.bol
 * @since 1.0
 */
class FRMPASSWORDCHANGEINTERVAL_CLASS_EventHandler
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
        $service = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'onAfterRoute'));
        $eventManager->bind(FRMEventManager::ON_AFTER_PASSWORD_UPDATE, array($service, 'onAfterPasswordUpdate'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_RESET_PASSWORD_FORM_RENDERER, array($service, 'onBeforeResetPasswordFormRenderer'));
        $eventManager->bind('notifications.collect_actions', array($service, 'on_notify_actions'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'catchAllRequestsExceptions'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        $eventManager->bind('user.password.updated',array($service,'userPasswordUpdate'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'onUserUnregister'));
        $eventManager->bind('after.console.item.collected', array($service, 'onAfterConsoleItemCollected'));
    }
}