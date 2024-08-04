<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcertedu.classes
 * @since 1.0
 */
class FRMCERTEDU_CLASS_EventHandler
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
        $service = FRMCERTEDU_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'loadStaticFiles'));
    }
}
