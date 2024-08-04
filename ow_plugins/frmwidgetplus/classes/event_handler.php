<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmwidgetplus
 * @since 1.0
 */
class FRMWIDGETPLUS_CLASS_EventHandler
{
    private static $classInstance;

    /***
     * @return FRMWIDGETPLUS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * FRMWIDGETPLUS_CLASS_EventHandler constructor.
     */
    private function __construct()
    {
    }

    /***
     *
     */
    public function init()
    {
        $service=FRMWIDGETPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'addWidgetJS'));
    }

}