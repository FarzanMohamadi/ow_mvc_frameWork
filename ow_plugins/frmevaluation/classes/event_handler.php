<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmevaluation.bol
 * @since 1.0
 */
class FRMEVALUATION_CLASS_EventHandler
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
    
    private $service;
    
    private function __construct()
    {
        $this->service = FRMEVALUATION_BOL_Service::getInstance();
    }
    
    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('base.add_main_console_item', array($this, 'onAddMainConsoleItem'));
    }

    public function onAddMainConsoleItem(OW_Event $event){
        $service = FRMEVALUATION_BOL_Service::getInstance();
        if($service->checkUserPermission()) {
            $event->add(array('label' => OW::getLanguage()->text('frmevaluation', 'main_menu_item'), 'url' => OW::getRouter()->urlForRoute('frmevaluation.index')));
        }
    }
}