<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmphotoplus.bol
 * @since 1.0
 */
class FRMPHOTOPLUS_MCLASS_EventHandler
{
    /**
     * @var FRMPHOTOPLUS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMPHOTOPLUS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }


    public function init()
    {
        if( !FRMSecurityProvider::checkPluginActive('photo', true)){
            return;
        }
        $service = FRMPHOTOPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ADD_LIST_TYPE_TO_PHOTO, array($service, 'addListTypeToPhoto'));
        $eventManager->bind(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_PHOTO, array($service, 'getResultForListItemPhoto'));
        $eventManager->bind(FRMEventManager::SET_TILE_HEADER_LIST_ITEM_PHOTO, array($service, 'setTtileHeaderListItemPhoto'));
        $eventManager->bind(FRMEventManager::GET_VALID_LIST_FOR_PHOTO, array($service, 'getValidListForPhoto'));
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'appendPhotosToFeed'));
        $eventManager->bind('feed.after_like_removed', array($service, 'removeNotification'));
    }

}