<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.forum.classes
 * @since 1.6.0
 */
class FORUM_CLASS_EventHandler
{
    /**
     * @var FORUM_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FORUM_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct() { }

    public function addNewContentItem( BASE_CLASS_EventCollector $event )
    {
        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_files',
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('add-topic-default'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('forum', 'discussion')
        );

        if ( !OW::getUser()->isAuthorized('forum', 'edit') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit');

            if ( $status['status'] != BOL_AuthorizationService::STATUS_PROMOTED )
            {
                return;
            }

            $id = FRMSecurityProvider::generateUniqueId('add-new-forum-');
            $resultArray[BASE_CMP_AddNewContent::DATA_KEY_ID] = $id;

            $script = '$("#'.$id.'").click(function(){
                OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                return false;
            });';
            OW::getDocument()->addOnloadScript($script);
        }

        $event->add($resultArray);
    }

    public function deleteUserContent( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['deleteContent']) || !(bool) $params['deleteContent'] )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $forumService = FORUM_BOL_ForumService::getInstance();

            $forumService->deleteUserTopics($userId);
            $forumService->deleteUserPosts($userId);
        }
    }

    public function createSection( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['name']) || !isset($params['entity']) || !isset($params['isHidden']) )
        {
            return;
        }

        $forum_service = FORUM_BOL_ForumService::getInstance();

        $sectionDto = $forum_service->findSectionByEntity($params['entity']);

        if ( !isset($sectionDto) )
        {
            $sectionDto = new FORUM_BOL_Section();
            $sectionDto->name = $params['name'];
            $sectionDto->entity = $params['entity'];
            $sectionDto->isHidden = $params['isHidden'];
            $sectionDto->order = $forum_service->getNewSectionOrder();

            $forum_service->saveOrUpdateSection($sectionDto);
        }

    }

    public function deleteSection( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['name']) && !isset($params['entity']) )
        {
            return;
        }

        $forum_service = FORUM_BOL_ForumService::getInstance();

        if ( isset($params['name']) )
        {
            $section = $forum_service->getSection($params['name']);
        }

        if ( isset($params['entity']) )
        {
            $section = $forum_service->findSectionByEntity($params['entity']);
        }

        if ( !empty($section) )
        {
            $forum_service->deleteSection($section->getId());
        }
    }

    public function addWidget( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['place']) || !isset($params['section']) )
        {
            return;
        }

        try
        {
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FORUM_CMP_LatestTopicsWidget', false);
            $placeWidget = $widgetService->addWidgetToPlace($widget, $params['place']);
            $widgetService->addWidgetToPosition($placeWidget, $params['section'], 0);
        }
        catch ( Exception $e ) { }
    }

    public function installWidget( OW_Event $e )
    {
        $params = $e->getParams();

        $widgetService = BOL_ComponentAdminService::getInstance();

        try
        {
            $widget = $widgetService->addWidget('FORUM_CMP_LatestTopicsWidget', false);
            $widgetPlace = $widgetService->addWidgetToPlace($widget, $params['place']);
            $widgetService->addWidgetToPosition($widgetPlace, $params['section'], $params['order']);
            $e->setData($widgetPlace->uniqName);
        }
        catch ( Exception $exception )
        {
            $e->setData(false);
        }
    }

    public function deleteWidget( OW_Event $event )
    {
        BOL_ComponentAdminService::getInstance()->deleteWidget('FORUM_CMP_LatestTopicsWidget');
    }

    public function createGroup( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !$params['entity'] || !isset($params['name']) || !isset($params['description']) || !isset($params['entityId']) )
        {
            return;
        }

        $forumService = FORUM_BOL_ForumService::getInstance();

        $forumGroup = $forumService->findGroupByEntityId($params['entity'], $params['entityId']);

        if ( !isset($forumGroup) )
        {
            $section = $forumService->findSectionByEntity($params['entity']);

            $forumGroup = new FORUM_BOL_Group();
            $forumGroup->sectionId = $section->getId();
            $forumGroup->order = $forumService->getNewGroupOrder($section->getId());

            $forumGroup->name = $params['name'];
            $forumGroup->description = $params['description'];
            $forumGroup->entityId = $params['entityId'];

            $forumService->saveOrUpdateGroup($forumGroup);
        }
    }

    public function deleteGroup( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        $forumService = FORUM_BOL_ForumService::getInstance();
        $group = $forumService->findGroupByEntityId($params['entity'], $params['entityId']);

        if ( !empty($group) )
        {
            $forumService->deleteGroup($group->getId());
        }
    }

    public function editGroup( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        $forumService = FORUM_BOL_ForumService::getInstance();
        $group = $forumService->findGroupByEntityId($params['entity'], $params['entityId']);

        if ( !empty($group) )
        {
            if (!empty($params['name']))
            {
                $group->name = $params['name'];
            }

            if (!empty($params['description']))
            {
                $group->description = $params['description'];
            }

            $forumService->saveOrUpdateGroup($group);
        }
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'forum',
            'action' => 'forum-add_post',
            'sectionIcon' => 'ow_ic_forum',
            'sectionLabel' => OW::getLanguage()->text('forum', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('forum', 'email_notifications_setting_post'),
            'selected' => true
        ));
    }

    public function addPost( OW_Event $e )
    {
        $params = $e->getParams();

        $postId = $params['postId'];

        $forumService = FORUM_BOL_ForumService::getInstance();
        $post = $forumService->findPostById($postId);

        if ( !$post )
        {
            return;
        }

        $userIdList = FORUM_BOL_SubscriptionService::getInstance()->findTopicSubscribers($post->topicId);
        if ( empty($userIdList) )
        {
            return;
        }

        $params = array(
            'pluginKey' => 'forum',
            'entityType' => 'forum_topic_reply',
            'entityId' => $postId,
            'action' => 'forum-add_post',
            'time' => time()
        );

        $authorId = $post->userId;
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($authorId));
        $postUrl = $forumService->getPostUrl($post->topicId, $postId);
        $topicUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $post->topicId));
        $topic = $forumService->findTopicById($post->topicId);

        $data = array(
            'avatar' => $avatar[$authorId],
            'string' => array(
                'key' => 'forum+email_notification_post',
                'vars' => array(
                    'userName' => $avatar[$authorId]['title'],
                    'userUrl' => $avatar[$authorId]['url'],
                    'postUrl' => $postUrl,
                    'topicUrl' => $topicUrl,
                    'title' => strip_tags($topic->title)
                )
            ),
            'content' => strip_tags($post->text),
            'url' => $postUrl
        );

        // send notifications in batch to userIds
        $userIds = array_diff($userIdList, [$post->userId]);
        $event = new OW_Event('notifications.batch.add',
            ['userIds'=>$userIds, 'params'=>$params],
            $data);
        OW::getEventManager()->trigger($event);
    }

    public function adsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('forum');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'forum' => array(
                    'label' => $language->text('forum', 'auth_group_label'),
                    'actions' => array(
                        'edit' => $language->text('forum', 'auth_action_label_edit'),
                        'delete' => $language->text('forum', 'auth_action_label_delete'),
                        'view' => $language->text('forum', 'auth_action_label_view'),
                        'subscribe' => $language->text('forum', 'auth_action_label_subscribe'),
                        'move_topic_to_hidden' => $language->text('forum', 'auth_action_label_move_topic_to_hidden'),
                        'add_comment' => $language->text('base', 'add_comment'),
                    )
                )
            )
        );
    }

    public function feedOnEntityAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'forum-topic' && $params['entityType'] != 'forum-post' )
        {
            return;
        }
        $service = FORUM_BOL_ForumService::getInstance();
        if($params['entityType'] == 'forum-post')
        {
            $topicId=$data['topicId'];
            $postDto = $service->findPostById($params['entityId']);
        }else{
            $topicId = (int) $params['entityId'];
            $postDto = $service->findTopicFirstPost($topicId);
        }
        $topicDto = $service->findTopicById($topicId);
        $groupDto = $service->findGroupById($topicDto->groupId);
        $sectionDto = $service->findSectionById($groupDto->sectionId);
        $isHidden = (bool) $sectionDto->isHidden;

        if ( $postDto === null )
        {
            return;
        }

        if ( $groupDto->isPrivate )
        {
            return;
        }

        $topicUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $topicDto->id));
        $sentenceCorrected = false;
        if ( mb_strlen($postDto->text) > 200 )
        {
            $sentence = strip_tags($postDto->text);
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 200)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 200)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
        }
        if($sentenceCorrected){
            $content = $sentence.'...';
        }
        else{
            $content = UTIL_String::truncate(strip_tags($postDto->text), 200, "...");
        }
        $title = UTIL_String::truncate(strip_tags($topicDto->title), 100, '...');

        $data = array(
            'features' => array('likes'),
            'ownerId' => $topicDto->userId,
            'time' => (int) $postDto->createStamp,
            'content' => array(
                'format' => 'content',
                'vars' => array(
                    'title' => $title,
                    'description' => $content,
                    'url' => array(
                        "routeName" => 'topic-default',
                        "vars" => array('topicId' => $topicDto->id)
                    ),
                    'iconClass' => 'ow_ic_forum'
                )
            ),
            'time'=>time(),
            'toolbar' => array(array(
                'href' => $topicUrl,
                'label' => OW::getLanguage()->text('forum', 'feed_toolbar_discuss')
            ))
        );

        $group_id = $topicDto->groupId;
        $group_name = $service->findGroupById($group_id)->name;

        if ( $isHidden )
        {
            $data['params']['feedType'] = $sectionDto->entity;
            $data['params']['feedId'] = $groupDto->entityId;
            $data['params']['visibility'] = 2 + 4 + 8; // Visible for follows(2), autor (4) and current feed (8)
            $data['contextFeedType'] = $data['params']['feedType'];
            $data['contextFeedId'] = $data['params']['feedId'];
            if($params['entityType'] == 'forum-post')
            {
                if(isset($params["userId"]))
                    $data["ownerId"] = $params["userId"];
                $data['string'] = array('key' => 'forum+feed_activity_topic_post_string', 'vars' => array('topic_title' => $title));
            }else {
                $data['string'] = array('key' => 'forum+feed_activity_group_topic_string', 'vars' => array('group_name' => $group_name));
            }
        }
        else
        {
            if($params['entityType'] == 'forum-post')
            {
                if(isset($params["userId"]))
                    $data["ownerId"] = $params["userId"];
                $data['string'] = array('key' => 'forum+feed_activity_topic_post_string', 'vars' => array('topic_title' => $title));
            }else {
                $data['string'] = array('key' => 'forum+feed_activity_topic_string','vars'=>array('group_name' =>$group_name));
            }
        }

        $e->setData($data);
    }

    public function feedOnPostAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $eData = array(
            'pluginKey' => 'forum',
            'entityType' => 'forum-topic',
            'entityId' => $params['topicId'],
            'userId' => $params['userId'],
            'activityType' => 'forum-post',
            'activityId' => $params['postId'],
            'subscribe' => true
        );
        if(isset($params['entityId']) && $params['entityId']!=null && class_exists('GROUPS_BOL_Service')){
           $frmSecurityEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.change.group.privacy.to.private',
                array('groupId' => $params['entityId'])));
            if(isset($frmSecurityEvent->getData()['private']) && isset($frmSecurityEvent->getData()['visibility'])){
                $eData['visibility']= $frmSecurityEvent->getData()['visibility'];
            }
        }
        $event = new OW_Event('feed.activity',$eData, array(
            'postId' => $params['postId'],
            'string' => array('key' => 'forum+feed_activity_topic_reply_string')
        ));
        OW::getEventManager()->trigger($event);
    }

    public function feedOnItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $language = OW::getLanguage();

        if ( $params['action']['entityType'] != 'forum-topic' && $params['action']['entityType'] != 'forum-post' )
        {
            return;
        }
        if ( $params['action']['entityType'] == 'forum-topic' && $params["feedType"]!="groups") {
            $service = FORUM_BOL_ForumService::getInstance();
            $postCount = 0;
            if (isset($params['cache']['topics_posts'][$params['action']['entityId']])) {
                $postCount = sizeof($params['cache']['topics_posts'][$params['action']['entityId']]) - 1;
            } else {
                $postCount = $service->findTopicPostCount($params['action']['entityId']);
            }

            if ($postCount > 0) {
                if (is_array($data['toolbar'])) {
                    $data['toolbar'][] = array(
                        'label' => $language->text('forum', 'feed_toolbar_replies', array('postCount' => $postCount))
                    );
                }

                $event->setData($data);

                $postIds = array();
                foreach ($params['activity'] as $activity) {
                    if ($activity['activityType'] == 'forum-post') {
                        $postIds[] = $activity['data']['postId'];
                    }
                }

                if (empty($postIds)) {
                    return;
                }

                $curPid = 0;
                $postDto = null;
                foreach ($postIds as $pid) {
                    if($pid > $curPid) {
                        $postDto2 = null;
                        if (isset($params['cache']['topics_posts'][$params['action']['entityId']][$pid])) {
                            $postDto2 = $params['cache']['topics_posts'][$params['action']['entityId']][$pid];
                        }
                        if ($postDto2 == null) {
                            $postDto2 = $service->findPostById($pid);
                        }
                        if ($postDto2 !== null) {
                            $postDto = $postDto2;
                            $curPid = $pid;
                        }
                    }
                }

                if ($postDto === null) {
                    return;
                }

                $postUrlEmbed = '...';
                $content = UTIL_String::truncate(strip_tags(str_replace("&nbsp;", "", $postDto->text)), 100, $postUrlEmbed);
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
                if (isset($stringRenderer->getData()['string'])) {
                    $content = ($stringRenderer->getData()['string']);
                }
                $avatarData = array();
                if (isset($params['cache']['users_info'][$postDto->userId])) {
                    $avatarData = $params['cache']['users_info'][$postDto->userId];
                } else {
                    $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($postDto->userId), true, true, true, false);
                    $avatarData = $usersData[$postDto->userId];
                }

                $postUrl = $service->getPostUrl($postDto->topicId, $postDto->id, true, null, $params);

                if (is_array($data['content']) && !empty($data['content']['vars'])) {
                    $data['content']['vars']['activity'] = array(
                        'url' => $postUrl,
                        'title' => $language->text('forum', 'last_reply'),
                        'avatarData' => $avatarData,
                        'description' => $content
                    );
                }
            }

            $firstPost = null;
            if (isset($params['cache']['topics_posts'][$params['action']['entityId']])) {
                $topicPosts = $params['cache']['topics_posts'][$params['action']['entityId']];
                $minimumId = min(array_keys($topicPosts));
                if (isset($topicPosts[$minimumId])) {
                    $firstPost = $topicPosts[$minimumId];
                }
            }
            if ($firstPost == null) {
                $firstPost = $service->findTopicFirstPost($params['action']['entityId']);
            }
            if (isset($firstPost)) {
                $data['content']['vars']['description'] = $firstPost->text;
            }
            $data['content']['vars']['description'] = strip_tags($data['content']['vars']['description']);
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['description'])));
            if (isset($stringRenderer->getData()['string'])) {
                $data['content']['vars']['description'] = ($stringRenderer->getData()['string']);
            }
        }
        if (isset($data["string"]["key"])) {
            $forumService = FORUM_BOL_ForumService::getInstance();
            if ($data["string"]["key"] == "forum+feed_activity_topic_reply_string" || $data["string"]["key"] == "forum+feed_activity_topic_post_string") {
                $data["toolbar"][0]["href"] = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $data["content"]["vars"]["url"]["vars"]["topicId"]));
                if (isset($data["content"]["vars"]["url"]["vars"]["topicId"])) {
                    $topicTitle = null;
                    $topicId = $data["content"]["vars"]["url"]["vars"]["topicId"];
                    if (isset($params['cache']['topics'][$topicId])) {
                        $topic = $params['cache']['topics'][$topicId];
                        if (isset($topic->title)) {
                            $topicTitle = $topic->title;
                        }
                    }
                    if ($topicTitle == null) {
                        $topic = $forumService->getTopicInfo($topicId);
                        if (isset($topic) && isset($topic["title"])) {
                            $topicTitle = $topic["title"];
                        }
                    }
                    if ($topicTitle != null) {
                        if (isset($data["content"]["vars"]["title"]))
                            $data["content"]["vars"]["title"] = $topicTitle;
                        if (isset($data["string"]["vars"]["topic_title"]))
                            $data["string"]["vars"]["topic_title"] = $topicTitle;
                    }
                }
            }
            elseif ($data["string"]["key"] == "forum+feed_activity_topic_string" || $data["string"]["key"] == "forum+feed_activity_group_topic_string") {
                if (isset($data["content"]["vars"]["url"]["vars"]["topicId"]) && isset($data["string"]["vars"]["group_name"])) {
                    $topicId = $data["content"]["vars"]["url"]["vars"]["topicId"];
                    $topicDto = $forumService->getTopicInfo($topicId);
                    if (!empty($topicDto)) {
                        $data["content"]["vars"]["title"] = $topicDto["title"];
                        $data["content"]["vars"]["description"] = $forumService->findTopicFirstPost($topicId)->text;
                        $data['content']['vars']['description'] = strip_tags($data['content']['vars']['description']);
                        $data['content']['vars']['description'] = str_replace("\r\n", "", $data['content']['vars']['description']);
                        $groupId = $topicDto["groupId"];
                        $data["string"]["vars"]["group_name"] = $forumService->findGroupById($groupId)->name;
                    }

                }
            }
        }
        $event->setData($data);
    }

    public function feedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('forum', 'feed_content_label'),
            'activity' => 'create:forum-topic'
        ));

        $event->add(array(
            'label' => $language->text('forum', 'feed_content_replies_label'),
            'activity' => 'forum-post:forum-topic'
        ));
    }

    public function subscribeUser( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];
        $topicId = (int) $params['topicId'];

        if ( !$userId || ! $topicId )
        {
            return false;
        }

        $service = FORUM_BOL_SubscriptionService::getInstance();

        if ( $service->isUserSubscribed($userId, $topicId) )
        {
            return true;
        }

        $subs = new FORUM_BOL_Subscription();
        $subs->userId = $userId;
        $subs->topicId = $topicId;

        $service->addSubscription($subs);

        return true;
    }

    public function feedTopicLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'forum-topic' )
        {
            return;
        }

        $service = FORUM_BOL_ForumService::getInstance();
        $topic = $service->findTopicById($params['entityId']);
        $userId = $topic->userId;

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        if ( $params['userId'] == $userId )
        {
            return;
        }
        else
        {
            $string = array('key' => 'forum+feed_activity_topic_string_like', 'vars' => array('user' => $userEmbed));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'forum'
        ), array(
            'string' => $string
        )));
    }

    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'addNewContentItem'));
        $em->bind('forum.add_widget', array($this, 'addWidget'));
        $em->bind('forum.install_widget', array($this, 'installWidget'));
        $em->bind('forum.delete_widget', array($this, 'deleteWidget'));
        $em->bind('feed.on_item_render', array($this, 'feedOnItemRender'));
        $em->bind("base.collect_seo_meta_data", array($this, 'onCollectMetaData'));

        $forumService = FORUM_BOL_ForumService::getInstance();
        $em->bind('frmadvancesearch.on_collect_search_items', array($forumService, 'onCollectSearchItems'));
    }

    public function sosialSharingGetForumInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'forum_topic' )
        {
            $topicDto = FORUM_BOL_ForumService::getInstance()->findTopicById($params['entityId']);

            $forumGroup = FORUM_BOL_ForumService::getInstance()->findGroupById($topicDto->groupId);
            $forumSection = FORUM_BOL_ForumService::getInstance()->findSectionById($forumGroup->sectionId);

            if ( !empty($topicDto) )
            {
                $data['display'] = !$forumSection->isHidden && !$forumGroup->isPrivate && BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('forum', 'view');
            }

            $event->setData($data);
        }
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

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('forum', 'view') )
        {
            $offset = (int) $params['offset'];
            $limit  = (int) $params['limit'];
            $urls   = array();

            switch ( $params['entity'] )
            {
                case 'forum_topic' :
                    $topics = FORUM_BOL_ForumService::getInstance()->findLatestPublicTopicsIds($offset, $limit);

                    foreach ( $topics as $topicId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('topic-default', array(
                            'topicId' => $topicId
                        ));
                    }
                    break;

                case 'forum_group' :
                    $groups = FORUM_BOL_ForumService::getInstance()->findLatestPublicGroupsIds($offset, $limit);

                    foreach ( $groups as $groupId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('group-default', array(
                            'groupId' => $groupId
                        ));
                    }
                    break;

                case 'forum_section' :
                    $sections = FORUM_BOL_ForumService::getInstance()->findLatestPublicSectionsIds($offset, $limit);

                    foreach ( $sections as $sectionId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('section-default', array(
                            'sectionId' => $sectionId
                        ));
                    }
                    break;

                case 'forum_list' :
                    $urls[] = OW::getRouter()->urlForRoute('forum-default');
                    $urls[] = OW::getRouter()->urlForRoute('forum_advanced_search');
                    break;
            }

            if ( $urls )
            {
                $event->setData($urls);
            }
        }
    }

    public function onCollectMetaData( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();

        $items = array(
            array(
                "entityKey" => "home",
                "entityLabel" => $language->text("forum", "seo_meta_home_label"),
                "iconClass" => "ow_ic_house",
                "langs" => array(
                    "title" => "forum+meta_title_home",
                    "description" => "forum+meta_desc_home",
                    "keywords" => "forum+meta_keywords_home"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "advSearch",
                "entityLabel" => $language->text("forum", "seo_meta_adv_search_label"),
                "iconClass" => "ow_ic_lens",
                "langs" => array(
                    "title" => "forum+meta_title_adv_search",
                    "description" => "forum+meta_desc_adv_search",
                    "keywords" => "forum+meta_keywords_adv_searche"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "advSearchResult",
                "entityLabel" => $language->text("forum", "seo_meta_adv_search_result_label"),
                "iconClass" => "ow_ic_newsfeed",
                "langs" => array(
                    "title" => "forum+meta_title_adv_search_result",
                    "description" => "forum+meta_desc_adv_search_result",
                    "keywords" => "forum+meta_keywords_adv_searche_result"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "section",
                "entityLabel" => $language->text("forum", "seo_meta_section_label"),
                "iconClass" => "ow_ic_forum",
                "langs" => array(
                    "title" => "forum+meta_title_section",
                    "description" => "forum+meta_desc_section",
                    "keywords" => "forum+meta_keywords_section"
                ),
                "vars" => array("site_name", "section_name")
            ),
            array(
                "entityKey" => "group",
                "entityLabel" => $language->text("forum", "seo_meta_group_label"),
                "iconClass" => "ow_ic_forum",
                "langs" => array(
                    "title" => "forum+meta_title_group",
                    "description" => "forum+meta_desc_group",
                    "keywords" => "forum+meta_keywords_group"
                ),
                "vars" => array("site_name", "group_name", "group_description")
            ),
            array(
                "entityKey" => "topic",
                "entityLabel" => $language->text("forum", "seo_meta_topic_label"),
                "iconClass" => "ow_ic_forum",
                "langs" => array(
                    "title" => "forum+meta_title_topic",
                    "description" => "forum+meta_desc_topic",
                    "keywords" => "forum+meta_keywords_topic"
                ),
                "vars" => array("site_name", "topic_name", "topic_description")
            ),
            array(
                "entityKey" => "sectionSearch",
                "entityLabel" => $language->text("forum", "seo_meta_section_search_label"),
                "iconClass" => "ow_ic_lens",
                "langs" => array(
                    "title" => "forum+meta_title_section_search",
                    "description" => "forum+meta_desc_section_search",
                    "keywords" => "forum+meta_keywords_section_search"
                ),
                "vars" => array("site_name", "section_name")
            ),
            array(
                "entityKey" => "groupSearch",
                "entityLabel" => $language->text("forum", "seo_meta_group_search_label"),
                "iconClass" => "ow_ic_lens",
                "langs" => array(
                    "title" => "forum+meta_title_group_search",
                    "description" => "forum+meta_desc_group_search",
                    "keywords" => "forum+meta_keywords_group_search"
                ),
                "vars" => array("site_name", "group_name", "group_description")
            ),
            array(
                "entityKey" => "topicSearch",
                "entityLabel" => $language->text("forum", "seo_meta_topic_search_label"),
                "iconClass" => "ow_ic_lens",
                "langs" => array(
                    "title" => "forum+meta_title_topic_search",
                    "description" => "forum+meta_desc_topic_search",
                    "keywords" => "forum+meta_keywords_topic_search"
                ),
                "vars" => array("site_name", "topic_name", "topic_description")
            ),
        );

        foreach ($items as &$item)
        {
            $item["sectionLabel"] = $language->text("forum", "seo_meta_section");
            $item["sectionKey"] = "forum";
            $e->add($item);
        }
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'forum')
            return;

        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];
        if ($entityType == 'forum_topic_reply') {
            $post=FORUM_BOL_ForumService::getInstance()->findPostById($entityId);
            if(isset($post)) {
                $topicId = $post->topicId;
                $topic=FORUM_BOL_ForumService::getInstance()->findTopicById($topicId);
                if(isset($topic)) {
                    $notificationData["string"]["vars"]["title"] = $topic->title;
                }
            }
        }

        $event->setData($notificationData);
    }

    public function unsubscribeUsersFromTopics(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['userIds']) || !isset($params['groupId']))
        {
            return;
        }
        // delete all subscriptions
        FORUM_BOL_SubscriptionService::getInstance()->unsubscribeUsersFromGroupTopics($params['userIds'], $params['groupId']);
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'deleteUserContent'));
        $em->bind('forum.create_section', array($this, 'createSection'));
        $em->bind('forum.delete_section', array($this, 'deleteSection'));
        $em->bind('forum.create_group', array($this, 'createGroup'));
        $em->bind('forum.delete_group', array($this, 'deleteGroup'));
        $em->bind('forum.edit_group', array($this, 'editGroup'));
        $em->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $em->bind('forum.add_post', array($this, 'addPost'));
        $em->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $em->bind('feed.on_entity_add', array($this, 'feedOnEntityAdd'));
        $em->bind('feed.on_entity_update', array($this, 'feedOnEntityAdd'));
        $em->bind('forum.add_post', array($this, 'feedOnPostAdd'));
        $em->bind('feed.collect_configurable_activity', array($this, 'feedCollectConfigurableActivity'));
        $em->bind('forum.subscribe_user', array($this, 'subscribeUser'));
        $em->bind('feed.after_like_added', array($this, 'feedTopicLike'));
        $em->bind('group.forums.topics.unsubscribe', array($this, 'unsubscribeUsersFromTopics'));
        $em->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetForumInfo'));
        $em->bind("base.sitemap.get_urls", array($this, 'onSitemapGetUrls'));
        OW::getEventManager()->bind('notification.get_edited_data', array($this, 'getEditedDataNotification'));
    }
}