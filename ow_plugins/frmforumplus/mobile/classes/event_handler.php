<?php
/**
 * 
 * All rights reserved.
 * frmforumplus
 */


class FRMFORUMPLUS_MCLASS_EventHandler
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
        $service = FRMFORUMPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMFORUMPLUS_BOL_Service::ON_BEFORE_FORUM_ATTACHMENTS_ICON_RENDER, array($service, 'addIconsToForumAttachments'));
        $eventManager->bind('on.handle.more.in.forum', array($service, 'onHandleMoreInForum'));
        $eventManager->bind('on.load.post.list.in.forum', array($service, 'onLoadPostListInForum'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        $eventManager->bind('on.forum.group.topic.add', array($service, 'onForumGroupTopicAdd'));
        OW::getEventManager()->bind('notification.get_edited_data', array($service, 'getEditedDataNotification'));
    }
    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmforumplus'|| ($params['entityType'] != 'group-topic-add'))
        {
            return;
        }
        $data = $params['data'];
        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }
        $e->setData($data);
    }

}