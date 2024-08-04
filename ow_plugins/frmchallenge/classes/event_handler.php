<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.bol
 * @since 1.0
 */
class FRMCHALLENGE_CLASS_EventHandler
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
        $eventManager = OW::getEventManager();
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $eventManager->bind('admin.add_auth_labels', array($generalService, 'addAuthLabels'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
    }
}