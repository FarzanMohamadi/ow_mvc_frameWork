<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

class FRMTHEMEMANAGER_CLASS_EventHandler
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
        $service = FRMTHEMEMANAGER_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmthememanager.on.before.theme.style.renderer', array($service, 'getCustomTheme'));
        $eventManager->bind('frmthememanager.get.all.themes', array($service, 'getAllTheme'));
        $eventManager->bind('frmthememanager.on.before.document.render.add.footer.custom.tags', array($service, 'addFooterCustomTags'));
        $eventManager->bind('frmthememanager.update.all.child.themes.from.parent.theme', array($service, 'updateAllThemesList'));
    }
}