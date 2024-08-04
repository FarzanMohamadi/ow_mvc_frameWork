<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.frmnews.classes
 * @since 1.6.0
 */
class FRMNEWS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMNEWS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMNEWS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Get sitemap urls
     *
     * @param OW_Event $event
     * @return void
     */
    public function onSitemapGetUrls( OW_Event $event )
    {
        $params = $event->getParams();

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('frmnews', 'view') )
        {
            $offset = (int) $params['offset'];
            $limit  = (int) $params['limit'];
            $urls   = array();
            switch ( $params['entity'] )
            {
                case 'news' :
                    $entrieIds = EntryService::getInstance()->findLatestPublicEntryIds($offset, $limit);
                    foreach ( $entrieIds as $entryId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('entry', array(
                            'id' => $entryId->id
                        ));
                    }
                    break;
            }
            if ( $urls )
            {
                $event->setData($urls);
            }
        }
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'frmnews')
            return;

        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];
        if ($entityType == 'news-add_comment') {
            $comment=BOL_CommentService::getInstance()->findComment($entityId);
            if(isset($comment)) {
                $commentEntityId = $comment->commentEntityId;
                $commentEntity=BOL_CommentService::getInstance()->findCommentEntityById($commentEntityId);
                if(isset($commentEntity)) {
                    $entry=EntryService::getInstance()->findById($commentEntity->entityId);
                    if(isset($entry)) {
                        $notificationData["string"]["vars"]["title"] = UTIL_String::truncate( $entry->title, 60, '...' );
                    }
                }
                $notificationData["string"]["vars"]["comment"] = UTIL_String::truncate( $comment->getMessage(), 120, '...' );
            }
        } //publish news
        elseif ($entityType == 'news-add_news') {
            $entry=EntryService::getInstance()->findById($entityId);
            if(isset($entry)) {
                $notificationData["string"]["vars"]["title"] = $entry->title;
                if(!empty($entry->image))
                    $notificationData["contentImage"]["src"] =EntryService::getInstance()->generateImageUrl($entry->image, true, true);
                else
                    $notificationData["contentImage"] =null;



            }
        }

        $event->setData($notificationData);
    }
    public function init()
    {
        $this->genericInit();
        OW::getEventManager()->bind('notifications.on_item_render', array($this, 'desktopOnNotificationRender'));
    }
    public function desktopOnNotificationRender( OW_Event $e ){
        $params = $e->getParams();

        if ($params['pluginKey'] != 'frmnews' || ($params['entityType'] != 'news-add_comment' && $params['entityType'] != 'news-add_news')) {
            return;
        }

        $data = $params['data'];

        if (empty($data["contentImage"]["src"])){
            $data["contentImage"]["src"]=EntryService::getInstance()->generateImageUrl();
            $data["contentImage"]["newsImageInfo"]= BOL_AvatarService::getInstance()->getAvatarInfo($params['entityId'], $data["contentImage"]["src"],'news');
        }

        $e->setData($data);
    }
    public function genericInit()
    {
        $service = EntryService::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_USER_SUSPEND, array($service, 'onAuthorSuspend'));

        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'onUnregisterUser'));
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
        OW::getEventManager()->bind('base_add_comment', array($service, 'onAddNewsEntryComment'));
        OW::getEventManager()->bind('base_add_news', array($service, 'onAddNewsEnt'));
        //OW::getEventManager()->bind('base_delete_comment',                array($this, 'onDeleteComment'));
        OW::getEventManager()->bind('ads.enabled_plugins', array($service, 'onCollectEnabledAdsPages'));

        OW::getEventManager()->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($service, 'onCollectFeedConfigurableActivity'));
//        OW::getEventManager()->bind('feed.collect_privacy', array($this, 'onCollectFeedPrivacyActions'));
//        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActionList'));
//        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangeActionPrivacy'));

        OW::getEventManager()->bind('feed.on_entity_add', array($service, 'onAddNewsEntry'));
        OW::getEventManager()->bind('feed.on_entity_update', array($service, 'onUpdateNewsEntry'));
        OW::getEventManager()->bind('feed.after_comment_add', array($service, 'onFeedAddComment'));
        OW::getEventManager()->bind('feed.after_like_added', array($service, 'onFeedAddLike'));

        OW::getEventManager()->bind('socialsharing.get_entity_info', array($service, 'sosialSharingGetNewsInfo'));
        OW::getEventManager()->bind("base.sitemap.get_urls", array($this, "onSitemapGetUrls"));
        OW::getEventManager()->bind('base_delete_comment', array($service, 'deleteComment'));
        OW::getEventManager()->bind('feed.on_item_render', array($service, 'feedOnItemRenderActivity'));

        OW::getEventManager()->bind('notification.get_edited_data', array($this, 'getEditedDataNotification'));
    }


}