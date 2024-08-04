<?php

class FRMGROUPSRSS_MCLASS_EventHandler
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
        $service = FRMGROUPSRSS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('add.group.setting.elements', array($service, 'addGroupSettingElements'));
        $eventManager->bind($service::SET_RSS_FOR_GROUP_ON_CREATE, array($service, 'setRssForGroupOnCreate'));
        $eventManager->bind($service::SET_RSS_FOR_GROUP_ON_EDIT, array($service, 'setRssForGroupOnEdit'));
    }
}
