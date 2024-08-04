<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurefileurl.bol
 * @since 1.0
 */
class FRMSECUREFILEURL_CLASS_EventHandler
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
    
    private $service;
    
    private function __construct()
    {
        $this->service = FRMSECUREFILEURL_BOL_Service::getInstance();
    }
    
    public function init()
    {
        $service = FRMSECUREFILEURL_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_BEFORE_GET_FILE_URL, array($service, "processFileUrl"));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, "addStaticFiles"));
        $eventManager->bind('base.members_only_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.splash_screen_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.password_protected_exceptions', array($service, 'onAddMembersOnlyException'));
    }
}