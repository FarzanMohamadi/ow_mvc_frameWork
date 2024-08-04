<?php
/**
 * 
 * All rights reserved.
 */


class FRMNEWSFEEDPLUS_MCLASS_EventHandler
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
        if( !FRMSecurityProvider::checkPluginActive('newsfeed', true) ){
            return;
        }
        $service = FRMNEWSFEEDPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array($service, 'addAttachmentInputFieldsToNewsfeed'));
        $eventManager->bind('feed.on_entity_action', array($service,'saveAttachments'));
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'appendAttachmentsToFeed'));
        $eventManager->bind('feed.before_action_delete', array($service, "onBeforeActionDelete"));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array($service, 'onBeforeUpdateStatusFormRenderer'));
        $eventManager->bind('newsfeed.generic_item_render', array($service, 'genericItemRender'));
        $eventManager->bind('attachment.add.parameters',array($service,'attachmentAddParameters'));
        $eventManager->bind('newsfeed.after_status_component_addition', array($service, 'afterStatusComponentAddition'));
        $eventManager->bind('change.newsfeed.action.query', array($service, 'changeNewsfeedActionQuery'));
        $eventManager->bind('newsfeed.can_forward_post', array($service, 'canForwardPostEvent'));
        $eventManager->bind('on.status.update.check.data', array($service, 'onStatusUpdateCheckData'));
    }

}