<?php
/**
 * frmadvancedstyles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancedstyles
 * @since 1.0
 */

class FRMADVANCEDSTYLES_MCLASS_EventHandler
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
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
        $path = FRMADVANCEDSTYLES_BOL_Service::getInstance()->getScssURL(true);
        if (!empty($path)) {
            OW::getDocument()->addStyleSheet($path);
        }
    }
}