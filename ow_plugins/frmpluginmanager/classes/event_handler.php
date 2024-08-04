<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpluginmanager.bol
 * @since 1.0
 */
class FRMPLUGINMANAGER_CLASS_EventHandler
{
    private static $classInstance;
    
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
        $service = FRMPLUGINMANAGER_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('admin.plugins_list_view', array($service, 'onPluginListView'));
        $eventManager->bind('admin.themes_list_view', array($service, 'onThemesListView'));
        $eventManager->bind('core.before_theme_install', array($service, 'onBeforeThemeInstall'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($service, 'onBeforePluginUnistall'));
        $eventManager->bind('core.before_plugin_install', array($service, 'onBeforePluginInstall'));
    }
}