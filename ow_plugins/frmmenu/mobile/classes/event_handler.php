<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmenu.bol
 * @since 1.0
 */
class FRMMENU_MCLASS_EventHandler
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
        $service = FRMMENU_BOL_Service::getInstance();
        $eventManager->bind('on.before.context.menu.render', array($service, 'onBeforeContextMenuRender'));
    }
}