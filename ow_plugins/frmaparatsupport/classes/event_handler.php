<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

class FRMAPARATSUPPORT_CLASS_EventHandler
{
    /**
     * @var FRMAPARATSUPPORT_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMAPARATSUPPORT_CLASS_EventHandler
     */
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
        $service = FRMAPARATSUPPORT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_FORM_RENDERER, array($service, 'onBeforeVideoUploadFormRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_COMPONENT_RENDERER, array($service, 'onBeforeVideoUploadComponentRenderer'));
    }
}