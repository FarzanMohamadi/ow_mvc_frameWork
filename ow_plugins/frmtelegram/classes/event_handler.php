<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_CLASS_EventHandler
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
        $eventManager->bind('console.collect_items', array($this, 'collectItems'));

        $eventManager->bind('frmtelegram.add_widget', array($this, 'addWidgetToOthers'));
        $eventManager->bind('frmtelegram.delete_widget', array($this, 'deleteWidget'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'pluginDeactivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'pluginUninstall'));
    }

    public function collectItems(OW_Event $event)
    {
        if (OW::getConfig()->getValue('frmtelegram', 'icon_type') == 2) {
            if (OW::getConfig()->getValue('frmtelegram', 'link') != "") {
                $item = new FRMTELEGRAM_CMP_ConsoleTelegram();
                $event->addItem($item);
            }
        }
        else if (OW::getConfig()->getValue('frmtelegram', 'icon_type') == 3) {
            $item = new FRMTELEGRAM_CMP_ConsoleTelegram();
            $event->addItem($item);
        }
    }

    public function deleteWidget( OW_Event $event )
    {
        BOL_ComponentAdminService::getInstance()->deleteWidget('FRMTELEGRAM_CMP_FeedWidget');
    }


    public function pluginDeactivate( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmtelegram' )
        {
            return;
        }
        if(OW::getConfig()->configExists('groups', 'is_telegram_connected'))
        {
            $event = new OW_Event('frmtelegram.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function pluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmtelegram' )
        {
            return;
        }
        if(OW::getConfig()->configExists('groups', 'is_telegram_connected'))
        {
            $event = new OW_Event('frmtelegram.delete_widget');
            OW::getEventManager()->trigger($event);
        }
    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
        $cssFile = OW::getPluginManager()->getPlugin('frmtelegram')->getStaticCssUrl() . 'frmtelegram.css';
        OW::getDocument()->addStyleSheet($cssFile);

        $css = '
    a.ow_ic_telegram.console_item_search {
        background-image: url("' . OW::getPluginManager()->getPlugin('frmtelegram')->getStaticCssUrl() . 'ic_telegram.svg' . '") ;
        background-size: contain;
    }';
        OW::getDocument()->addStyleDeclaration($css);
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
            $widget = $widgetService->addWidget('FRMTELEGRAM_CMP_FeedWidget', false);
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