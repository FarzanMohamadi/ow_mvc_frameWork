<?php

class PASSWORDSECURITY_CLASS_EventHandler
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
        $service = PASSWORDSECURITY_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('passwordsecurity.passwordneeded', array($service, 'onPasswordNeededSection'));
    }
}