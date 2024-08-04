<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontrolkids.bol
 * @since 1.0
 */
class FRMCONTROLKIDS_CLASS_EventHandler
{
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array($service, 'onBeforeJoinFormRender'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_USER_REGISTER, array($service, 'onBeforeUserRegistered'));
        $eventManager->bind('base.add_main_console_item', array($service, 'onAddMainConsoleItem'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'removeUserInformation'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'checkUsersParentInfoExists'));
    }
}