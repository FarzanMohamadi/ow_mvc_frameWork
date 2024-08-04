<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordstrengthmeter.bol
 * @since 1.0
 */
class FRMPASSWORDSTRENGTHMETER_CLASS_EventHandler
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
    }
    
    public function init()
    {
        $service = FRMPASSWORDSTRENGTHMETER_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onAfterDocumentRenderer'));
        $eventManager->bind(FRMEventManager::ON_PASSWORD_VALIDATION_IN_JOIN_FORM, array($service, 'onPasswordValidationInJoinForm'));
        $eventManager->bind(FRMEventManager::GET_PASSWORD_REQUIREMENT_PASSWORD_STRENGTH_INFORMATION, array($service, 'getMinimumReqirementPasswordStrengthInformation'));
    }

}