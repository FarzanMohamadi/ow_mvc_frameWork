<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmforumplus.classes
 * @since 1.0
 */
class FRMFORUMPLUS_CLASS_EventHandler
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
        $service = FRMFORUMPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMFORUMPLUS_BOL_Service::ON_CREATE_MENU, array($service, 'onCreateMenu'));
        $eventManager->bind(FRMFORUMPLUS_BOL_Service::ON_GET_LATEST_TOPICS, array($service, 'onGetLatestTopics'));
        $eventManager->bind(FRMFORUMPLUS_BOL_Service::ON_BEFORE_FORUM_ATTACHMENTS_ICON_RENDER, array($service, 'addIconsToForumAttachments'));
        $eventManager->bind('on.handle.more.in.forum', array($service, 'onHandleMoreInForum'));
        $eventManager->bind('on.load.post.list.in.forum', array($service, 'onLoadPostListInForum'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        $eventManager->bind('on.forum.group.topic.add', array($service, 'onForumGroupTopicAdd'));
        $eventManager->bind('notification.get_edited_data', array($service, 'getEditedDataNotification'));
        $eventManager->bind('on.load.group.forum.widget', array($service, 'addButtonShowInGroupForum'));
    }



}