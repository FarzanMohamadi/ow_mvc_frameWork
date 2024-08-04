<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmuserlogin.bol
 * @since 1.0
 */
class FRMUSERLOGIN_MCLASS_EventHandler
{
    /**
     * @var FRMUSERLOGIN_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMUSERLOGIN_MCLASS_EventHandler
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
        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array($service, 'onPreferenceAddFormElement'));
        $eventManager->bind(OW_EventManager::ON_USER_LOGIN, array($service, 'onUserLogin'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRenderer'));
        OW::getEventManager()->bind('base.ping', array($service, 'onPing') , 1500);
        if(OW::getConfig()->configExists('frmuserlogin','update_active_details') && OW::getConfig()->getValue('frmuserlogin','update_active_details')) {
            $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'onAfterRoute'));
            $eventManager->bind(OW_EventManager::ON_USER_LOGOUT, array($service, 'onUserLogout'));
        }
    }

}