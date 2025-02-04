<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */
class FRMGUIDEDTOUR_CLASS_EventHandler
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
        $service = FRMGUIDEDTOUR_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRender'));
        $eventManager->bind('console.collect_items', array($this, 'collectItems'));
    }

    public function collectItems(OW_Event $event)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }

        $item = new FRMGUIDEDTOUR_CMP_ConsoleGuidedtour();
        $event->addItem($item);
    }
}