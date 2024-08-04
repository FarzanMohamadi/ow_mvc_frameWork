<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmreveal.classes
 * @since 1.0
 */
class FRMREVEAL_CLASS_EventHandler
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
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onAfterRoute'));
    }

    public function onAfterRoute(OW_Event $event)
    {
        if(!OW::getConfig()->configExists('frmreveal', 'already_loaded')){
            OW::getConfig()->saveConfig('frmreveal', 'already_loaded', false);
        }

        if(!OW::getConfig()->getValue('frmreveal', 'already_loaded')) {
            OW::getConfig()->saveConfig('frmreveal', 'already_loaded', true);
            $jsDir = OW::getPluginManager()->getPlugin('frmreveal')->getStaticJsUrl();
            OW::getDocument()->addScript($jsDir . 'frmreveal.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmreveal')->getStaticCssUrl() . 'frmreveal.css', "all", 100000);

            $css = '
            .curtain__panel.curtain__panel--left{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmreveal')->getStaticUrl(). 'img/first_left.jpg' . '");
            }
            .curtain__panel.curtain__panel--right{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmreveal')->getStaticUrl(). 'img/first_right.jpg' . '");
            }
            .curtain__panel2.curtain__panel--left2{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmreveal')->getStaticUrl(). 'img/second_left.jpg' . '");
            }
            .curtain__panel2.curtain__panel--right2{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmreveal')->getStaticUrl(). 'img/second_right.jpg' . '");
            }
            ';

            OW::getDocument()->addStyleDeclaration($css);
        }
    }

}