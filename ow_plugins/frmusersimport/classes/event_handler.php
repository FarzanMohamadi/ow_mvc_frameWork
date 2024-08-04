<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmusersimport.bol
 * @since 1.0
 */
class FRMUSERSIMPORT_CLASS_EventHandler
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
    
    private function __construct()
    {
    }
    
    public function init()
    {
        $service = FRMUSERSIMPORT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('store.users.import.data', array($service, 'storeUsersImportData'));
        $eventManager->bind('on.users.import.register', array($service, 'checkAdminVerified'));
    }

}