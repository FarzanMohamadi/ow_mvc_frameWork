<?php
class FRMNEWSFEEDPIN_CLASS_EventHandler
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
        $masterPlugin = OW::getPluginManager()->isPluginActive('newsfeed');
        if( !$masterPlugin ){
            return;
        }
        $service = FRMNEWSFEEDPIN_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('feed.entity_id_specified', array($service, 'loadNewItem'));
        $eventManager->bind('newsfeed.generic_item_render', array($service, 'genericItemRender'));
        $eventManager->bind('newsfeed.find_action_list_by_feed', array($service, 'findActionListByFeed'));
        $eventManager->bind('newsfeed.find_action_list_by_user', array($service, 'findActionListByUser'));
        $eventManager->bind('newsfeed.find_action_list_by_site', array($service, 'findActionListBySite'));
        $eventManager->bind('newsfeed.find_action_list_by_public_hashtag', array($service, 'findActionListByHashtagPublic'));
        $eventManager->bind('newsfeed.find_action_list_by_user_hashtag', array($service, 'findActionListByHashtagUser'));
        $eventManager->bind('newsfeed.after_status_component_addition', array($service, 'afterStatusComponentAddition'));
        $eventManager->bind('newsfeed.load_new_feed_item_html', array($service, 'loadNewFeedItemHTML'));
    }


}