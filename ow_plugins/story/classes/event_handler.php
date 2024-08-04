<?php

class STORY_CLASS_EventHandler
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
        $service = STORY_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
//        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
//        $eventManager->bind(FRMCOMPETITION_BOL_Service::ON_ADD_COMPRTITION, array($service, 'onAddCompetitionEnt'));
    }
}