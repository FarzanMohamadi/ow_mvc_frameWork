<?php
/**
 * @package ow.ow_plugins.blogs.classes
 * @since 1.6.0
 */
class BLOGS_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var BLOGS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BLOGS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        $service = PostService::getInstance();
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
        OW::getEventManager()->bind('feed.on_item_render', array($this, "onFeedItemRenderDisableActions"));
        OW::getEventManager()->bind('base.mobile_top_menu_add_options', array($this, 'onMobileTopMenuAddLink'));
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
    }

    public function onFeedItemRenderDisableActions(OW_Event $event)
    {
        $params = $event->getParams();

        if (!in_array($params["action"]["entityType"], array('blog-post'))) {
            return;
        }

        $data = $event->getData();

        if (isset($data['content']['vars']['description'])) {
            $data['content']['vars']['description'] = trim(preg_replace('/\s+/', ' ', $data['content']['vars']['description']));
            $data['content']['vars']['description'] = preg_replace('/^(<br\s*\/?>)*|(<br\s*\/?>)*$/i', '', $data['content']['vars']['description']);
            $data['content']['vars']['description'] = preg_replace("/^(<br \/>)/", '', trim($data['content']['vars']['description']));
        }
        $data["disabled"] = false;
        if (isset($data["string"]["key"]) && $data["string"]["key"] == "blogs+feed_add_item_label"
        && isset($data["respond"]["text"]) && isset($params["lastActivity"]["data"]["string"]["key"]) ) {
            $userName = BOL_UserService::getInstance()->getDisplayName($data["ownerId"]);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($data["ownerId"]);
            if ($params["lastActivity"]["data"]["string"]["key"] == "blogs+feed_activity_post_string")
                $data["respond"]["text"] = OW::getLanguage()->text('blogs', 'feed_activity_post_string', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
            elseif ($params["lastActivity"]["data"]["string"]["key"] == "blogs+feed_activity_post_string_like")
                $data["respond"]["text"] = OW::getLanguage()->text('blogs', 'feed_activity_post_string_like', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
        }
        $event->setData($data);
    }

    public function onMobileTopMenuAddLink(BASE_CLASS_EventCollector $event)
    {
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('blogs', 'add')) {
            $event->add(array(
                'prefix' => 'blogs',
                'key' => 'mobile_main_menu_list',
                'url' => OW::getRouter()->urlForRoute('post-save-new')
            ));
        }
    }

    public function onNotificationRender(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $params['data'];

        if (empty($params['entityType']) || ($params['entityType'] !== 'blogs-add_comment')) {
            return;
        }

        $data = $params['data'];
        $event->setData($data);
    }
}