<?php
/**
 * frminstagram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
class FRMINSTAGRAM_CLASS_EventHandler
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

        $eventManager->bind('frminstagram.add_widget', array($this, 'addWidgetToOthers'));
        $eventManager->bind('frminstagram.delete_widget', array($this, 'deleteWidget'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'pluginDeactivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'pluginUninstall'));
    }

    public function deleteWidget( OW_Event $event )
    {
        BOL_ComponentAdminService::getInstance()->deleteWidget('FRMINSTAGRAM_CMP_FeedWidget');
    }


    public function pluginDeactivate( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frminstagram' )
        {
            return;
        }
        if ( OW::getConfig()->getValue('groups', 'is_instagram_connected') )
        {
            $event = new OW_Event('frminstagram.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function pluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frminstagram' )
        {
            return;
        }
        if ( OW::getConfig()->getValue('groups', 'is_instagram_connected') )
        {
            $event = new OW_Event('frminstagram.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function addWidgetToOthers(OW_Event $event)
    {
        $params = $event->getParams();

        if ( !isset($params['place']) || !isset($params['section']) )
        {
            return;
        }
        try
        {
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMINSTAGRAM_CMP_FeedWidget', false);
            $widgetUniqID = $params['place'] . '-' . $widget->className;

            //*remove if exists
            $widgets = $widgetService->findPlaceComponentList($params['place']);
            foreach ( $widgets as $w )
            {
                if($w['uniqName'] == $widgetUniqID)
                    $widgetService->deleteWidgetPlace($widgetUniqID);
            }
            //----------*/

            //add
            $placeWidget = $widgetService->addWidgetToPlace($widget, $params['place'], $widgetUniqID);
            $widgetService->addWidgetToPosition($placeWidget, $params['section'], -1);
        }
        catch ( Exception $e ) { }
    }
}