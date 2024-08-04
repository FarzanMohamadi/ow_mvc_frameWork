<?php
/**
 * Mobile forum event handler
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.forum.mobile.classes
 * @since 1.6.0
 */
class FORUM_MCLASS_EventHandler
{
    /**
     * @var FORUM_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FORUM_MCLASS_EventHandler
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

    public function feedOnItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $language = OW::getLanguage();

        if ( $params['action']['entityType'] != 'forum-topic' )
        {
            return;
        }

        $service = FORUM_BOL_ForumService::getInstance();
        $postCount = $service->findTopicPostCount($params['action']['entityId']) - 1;

        if ( !$postCount )
        {
            if (isset($data['content']['vars']['description'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['description'])));
                if (isset($stringRenderer->getData()['string'])) {
                    $data['content']['vars']['description'] = ($stringRenderer->getData()['string']);
                }
                $event->setData($data);
            }
            return;
        }

        $postIds = array();
        foreach ( $params['activity'] as $activity )
        {
            if ( $activity['activityType'] == 'forum-post' )
            {
                $postIds[] = $activity['data']['postId'];
            }
        }

        if ( empty($postIds) )
        {
            return;
        }

        $postDto = null;
        foreach ( $postIds as $pid )
        {
            $postDto = $service->findPostById($pid);
            if ( $postDto !== null )
            {
                break;
            }
        }

        if ( $postDto === null )
        {
            return;
        }

        $postUrlEmbed = '...';
        $content = UTIL_String::truncate(strip_tags(str_replace("&nbsp;", "", $postDto->text)), 100, $postUrlEmbed);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
        if (isset($stringRenderer->getData()['string'])) {
            $content = ($stringRenderer->getData()['string']);
        }
        if (isset($data['content']['vars']['description'])) {
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['description'])));
            if (isset($stringRenderer->getData()['string'])) {
                $data['content']['vars']['description'] = ($stringRenderer->getData()['string']);
            }
        }
        $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($postDto->userId), true, true, true, false);

        $avatarData = $usersData[$postDto->userId];
        //$postUrl = $service->getPostUrl($postDto->topicId, $postDto->id);

        if ( is_array($data['content']) && !empty($data['content']['vars']) )
        {
            $data['content']['vars']['activity'] = array(
                'title' => $language->text('forum', 'latest_reply_from', array('url' => $avatarData['url'], 'user' => $avatarData['title'])),
                'avatarData' => $avatarData,
                'description' => $content
            );
        }
        if (isset($data["string"]["key"])){
            if ($data["string"]["key"] == "forum+feed_activity_topic_reply_string")
                $data["toolbar"][0]["href"] = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $data["content"]["vars"]["url"]["vars"]["topicId"]));
            elseif ($data["string"]["key"] == "forum+feed_activity_topic_string") {
                if (isset($data["content"]["vars"]["url"]["vars"]["topicId"]) && isset($data["string"]["vars"]["group_name"])) {
                    $forumService = FORUM_BOL_ForumService::getInstance();
                    $topicId = $data["content"]["vars"]["url"]["vars"]["topicId"];
                    $topicDto = $forumService->getTopicInfo($topicId);
                    $data["content"]["vars"]["title"] = $topicDto["title"];
                    $data["content"]["vars"]["description"] = $forumService->findTopicFirstPost($topicId)->text;
                    $groupId = $topicDto["groupId"];
                    $data["string"]["vars"]["group_name"] = $forumService->findGroupById($groupId)->name;
                }
            }
        }
        $event->setData($data);
    }

    public function onMobileTopMenuAddLink( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAuthenticated()  && OW::getUser()->isAuthorized('forum', 'subscribe'))
        {
            $event->add(array(
                'prefix' => 'forum',
                'key' => 'forum_mobile',
                'url' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('forum-default'), array('add_topic' => 1))
            ));
        }
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] == 'forum'
            && $params['entityType'] == 'forum_topic_reply')
        {
            $data = $params['data'];
            $e->setData($data);
        }
    }

    public function init()
    {
        FORUM_CLASS_EventHandler::getInstance()->genericInit();

        $em = OW::getEventManager();

        $em->bind('feed.on_item_render', array($this, 'feedOnItemRender'));
        $em->bind('base.mobile_top_menu_add_options', array($this, 'onMobileTopMenuAddLink'));
        $em->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
    }
}