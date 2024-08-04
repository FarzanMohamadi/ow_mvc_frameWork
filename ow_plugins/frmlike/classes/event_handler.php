<?php
class FRMLIKE_CLASS_EventHandler
{
    /**
     *
     * @var FRMLIKE_BOL_Service
     */
    private $service;

    public function __construct()
    {
        $this->service = FRMLIKE_BOL_Service::getInstance();
    }

    public function init()
    {
        $service=FRMLIKE_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'addLikeStaticData'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onCollectNotificationActions'));
        $eventManager->bind('add.newsfeed.comment.like.component', array($service, 'addNewsfeedCommentLikeComponent'));
        OW::getEventManager()->bind('admin.add_admin_notification', array($this, 'frmlike_merge_tables_notification'));

    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $sectionLabel = OW::getLanguage()->text('frmlike','notification_section_label');
        $e->add(array(
            "section" => 'frmlike',
            "action" => 'frmlike-comment',
            "description" => OW::getLanguage()->text('frmlike','comment_notifications_setting'),
            "selected" => true,
            "sectionLabel" => $sectionLabel,
            "sectionIcon" => 'ow_ic_write'
        ));
    }

    public function addLikeStaticData()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmlike')->getStaticJsUrl() . 'frmlike.js');
    }
    
    function frmlike_merge_tables_notification( BASE_CLASS_EventCollector $event ) {
        $newsfeedLikeTableExists = OW::getDbo()->tableExist(FRMLIKE_BOL_Service::getInstance()->getNewsfeedLikeTableName());

        $newsfeedLikeCount = -1;
        if ($newsfeedLikeTableExists) {
            $newsfeedLikeCount = FRMLIKE_BOL_Service::getInstance()->countNewsfeedLikeTable();
        }
        if ($newsfeedLikeCount > 0) {
            $url = OW::getRouter()->urlForRoute('frmlike.admin');
            $event->add(OW::getLanguage()->text('frmlike', 'admin_merge_tables_notification', array('url' => $url)));
        } else if ($newsfeedLikeTableExists) {
            $url = OW::getRouter()->urlForRoute('frmlike.admin');
            $event->add(OW::getLanguage()->text('frmlike', 'admin_remove_newsfeed_like_table_notification', array('url' => $url)));
        }
    }
}
