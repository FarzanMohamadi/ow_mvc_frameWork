<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/25/18
 * Time: 2:50 PM
 */

class FRMQUESTIONS_CLASS_EventHandler
{

    public function __construct()
    {
    }
    public function init()
    {
        $eventManager = OW::getEventManager();
        $service = FRMQUESTIONS_BOL_Service::getInstance();
        $eventManager->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        $eventManager->bind(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array($service, 'addButtonToNewsfeed'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array($service, 'addInputFieldsToNewsfeed'));
        $eventManager->bind('feed.after_activity', array($service, 'feedAdded'));
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'onFeedRender'));
        $eventManager->bind('newsfeed.generic_item_render', array($service, 'genericItemRender'));
        $eventManager->bind('feed.before_action_delete', array($service, 'deleteAction'));
        $eventManager->bind('feed.on_entity_action', array($service, 'onEntityAction'));
        $eventManager->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
        $eventManager->bind('base.on.before.forward.status.create', array($service, 'onForward'));
        OW::getEventManager()->bind('notification.get_edited_data', array($service, 'getEditedDataNotification'));
        OW::getEventManager()->bind('feed.on_item_render', array($service, "onNewsfeedItemRender"));
        $eventManager->bind('on.status.update.check.data', array($service, 'onStatusUpdateCheckData'));
    }
}
