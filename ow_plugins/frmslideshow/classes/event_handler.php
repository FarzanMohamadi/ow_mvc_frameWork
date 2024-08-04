<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_CLASS_EventHandler
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
        $eventManager->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'after_plugin_activate'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'before_plugin_deactivate'));
    }

    public function after_plugin_activate(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['pluginKey']))
            return;
        if( $params['pluginKey'] == "frmnews"){
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_NewsWidget', false);
            $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
            $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
        }
        if( $params['pluginKey'] == "forum"){
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_ForumWidget', false);
            $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
            $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
        }
    }
    public function before_plugin_deactivate(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['pluginKey']))
            return;

        if( $params['pluginKey'] == "frmnews"){
            BOL_ComponentAdminService::getInstance()->deleteWidget('FRMSLIDESHOW_MCMP_NewsWidget');
        }
        if( $params['pluginKey'] == "forum"){
            BOL_ComponentAdminService::getInstance()->deleteWidget('FRMSLIDESHOW_MCMP_ForumWidget');
        }
    }
}