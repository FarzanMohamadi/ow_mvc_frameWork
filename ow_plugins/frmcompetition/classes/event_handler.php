<?php
class FRMCOMPETITION_CLASS_EventHandler
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
        $service = FRMCOMPETITION_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMCOMPETITION_BOL_Service::ON_ADD_COMPRTITION, array($service, 'onAddCompetitionEnt'));
        $eventManager->bind(FRMCOMPETITION_BOL_Service::ON_ADD_POINT_TO_GROUP, array($service, 'onAddPointToGroup'));
        $eventManager->bind(FRMCOMPETITION_BOL_Service::ON_ADD_POINT_TO_USER, array($service, 'onAddPointToUser'));
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
        OW::getEventManager()->bind('notification.get_edited_data', array($service, 'getEditedDataNotification'));
    }
}