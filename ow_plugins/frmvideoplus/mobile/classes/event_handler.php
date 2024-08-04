<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvideoplus.bol
 * @since 1.0
 */
class FRMVIDEOPLUS_MCLASS_EventHandler
{
    /**
     * @var FRMVIDEOPLUS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMVIDEOPLUS_MCLASS_EventHandler
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
        if( !FRMSecurityProvider::checkPluginActive('video', true)){
            return;
        }
        $service = FRMVIDEOPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ADD_LIST_TYPE_TO_VIDEO, array($service, 'addListTypeToVideo'));
        $eventManager->bind(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_VIDEO, array($service, 'getResultForListItemVideo'));
        $eventManager->bind(FRMEventManager::GET_RESULT_FOR_COUNT_ITEM_VIDEO, array($service, 'getResultForCountItemVideo'));
        $eventManager->bind(FRMEventManager::SET_TILE_HEADER_LIST_ITEM_VIDEO, array($service, 'setTtileHeaderListItemVideo'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_FORM_RENDERER, array($service, 'onBeforeVideoUploadFormRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_COMPONENT_RENDERER, array($service, 'onBeforeVideoUploadComponentRenderer'));
        OW::getEventManager()->bind(FRMVIDEOPLUS_BOL_Service::EVENT_AFTER_ADD, array($service, "onAfterEntryAdd"));
        OW::getEventManager()->bind(FRMVIDEOPLUS_BOL_Service::ON_VIDEO_VIEW_RENDER, array($service, "onVideoViewRender"));
        $eventManager->bind(FRMVIDEOPLUS_BOL_Service::ON_BEFORE_VIDEO_ADD, array($service, 'onBeforeVideoAdded'));
        OW::getEventManager()->bind(FRMVIDEOPLUS_BOL_Service::ON_VIDEO_LIST_VIEW_RENDER, array($service, "onVideoListViewRender"));
        OW::getEventManager()->bind(FRMVIDEOPLUS_BOL_Service::ADD_VIDEO_DOWNLOAD_LINK, array($service, "addVideoDownloadLink"));
        OW::getEventManager()->bind('get.video.thumbnail', array($service, "getVideoThumbnail"));
    }

}