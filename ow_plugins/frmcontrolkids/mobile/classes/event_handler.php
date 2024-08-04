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
class FRMCONTROLKIDS_MCLASS_EventHandler
{
    /**
     * @var FRMVIDEOPLUS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMVIDEOPLUS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }


    public function init()
    {
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array($service, 'onBeforeJoinFormRender'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_USER_REGISTER, array($service, 'onBeforeUserRegistered'));
        $eventManager->bind('base.add_main_console_item', array($service, 'onAddMainConsoleItem'));
        $eventManager->bind(FRMEventManager::ON_MOBILE_ADD_ITEM, array($service, 'onMobileAddItem'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'removeUserInformation'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'checkUsersParentInfoExists'));
    }

}