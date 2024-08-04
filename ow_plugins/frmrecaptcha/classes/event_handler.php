<?php
class FRMRECAPTCHA_CLASS_EventHandler
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

        $service = FRMRECAPTCHA_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_USER_REGISTER, array($service, 'verifyRecaptchaResponse'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_JOIN_PAGE_RENDER, array($service, 'activateRecaptcha'));

    }


}