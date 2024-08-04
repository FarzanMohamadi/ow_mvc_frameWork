<?php
final class FRMFORUMPLUS_BOL_Service
{
    const ON_GET_LATEST_TOPICS = 'frmforumplus.on.get.latest.topics';
    const ON_CREATE_MENU = 'frmforumplus.on.create.menu';
    const ON_BEFORE_FORUM_ATTACHMENTS_ICON_RENDER = 'frm.on.before.attachments.icon.render';

    /**
     * vew topics by latest
     */
    const LATEST_TOPICS = 'latest';

    /**
     * vew topics by selected groups
     */
    const SELECTED_GROUPS_TOPICS= 'groups';


    private static $classInstance;
    /**
     * @var FORUM_BOL_TopicDao
     */
    private $topicDao;
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->topicDao = FRMFORUMPLUS_BOL_TopicDao::getInstance();
    }

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function onCreateMenu(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['createMenu']) && isset($params['forumWidget']) ) {
            $lang = OW::getLanguage();
            $menuItems['latestPosts'] = array(
                'label' => $lang->text('frmforumplus', 'menu_latest_posts'),
                'id' => 'forum-widget-menu-latestPosts',
                'contId' => 'forum-widget-latestPosts',
                'active' => true
            );

            $menuItems['latestTopics'] = array(
                'label' => $lang->text('frmforumplus', 'menu_latest_topics'),
                'id' => 'forum-widget-menu-latestTopics',
                'contId' => 'forum-widget-latestTopics'
            );
            $menuItems['mostViewedTopics'] = array(
                'label' => $lang->text('frmforumplus', 'menu_most_viewed'),
                'id' => 'forum-widget-menu-mostViewedTopics',
                'contId' => 'forum-widget-mostViewedTopics'
            );
            $forumWidget = $params['forumWidget'];
            $forumWidget->addComponent('menu', new BASE_CMP_WidgetMenu($menuItems));
            $forumWidget->assign('items', $menuItems);
        }
    }

    public function onGetLatestTopics(OW_Event $event)
    {
        $params = $event->getParams();
        $data=$event->getData();
        if (isset($params['createMenu']) && isset($params['forumWidget']) &&isset($params['confTopicCount']) && isset($params['excludeGroupIdList']) ) {
            $forumWidget = $params['forumWidget'];
            $eventMenu = new OW_Event('frmforumplus.on.create.menu', array('forumWidget' => $params['forumWidget'], 'createMenu' => $params['createMenu']));
            OW::getEventManager()->trigger($eventMenu);
            $period = null;
            if (isset($params['period'])) {
                $period = $params['period'];
            }
            //get last topics
            $lastTopics = $this->getCustomLatestTopicList($params['confTopicCount'], $params['excludeGroupIdList'],true,false,false, $period);
            $forumWidget->assign('latestTopics', $lastTopics);
            if ($lastTopics) {
                $this->createTopicData($lastTopics,'lastTopic',$forumWidget);
            }
            //get most viewed topics
            $mostViewedTopics = $this->getCustomLatestTopicList($params['confTopicCount'], $params['excludeGroupIdList'],false,true,false, $period);
            $forumWidget->assign('mostViewedTopics', $mostViewedTopics);
            if ($mostViewedTopics) {
                $this->createTopicData($mostViewedTopics,'mostViewedTopic',$forumWidget);
            }
            $groupIds=array();
            foreach ( $lastTopics as $item) {
                if(!in_array($item['groupId'],$groupIds)) {
                    $groupIds[] = $item['groupId'];
                }
            }
            $data['groupIds']=$groupIds;
            $event->setData($data);
        }
    }

    /**
     * @param array $topics
     * @param $topicName
     * @param $forumWidget
     */
    public function createTopicData($topics = array() ,$topicName,$forumWidget){
        $userIds = array();
        $groupIds = array();
        $toolbars = array();

        foreach ($topics as $topic) {
            if (!in_array($topic['lastPost']['userId'], $userIds)) {
                array_push($userIds, $topic['lastPost']['userId']);
            }

            if (!in_array($topic['groupId'], $groupIds)) {
                array_push($groupIds, $topic['groupId']);
            }
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds, true, false);
        $forumWidget->assign($topicName.'Avatars', $avatars);

        $urls = BOL_UserService::getInstance()->getUserUrlsForList($userIds);

        // toolbars
        foreach ($topics as $key => $topic) {
            $userId = $topic['lastPost']['userId'];
            $toolbars[$topic['lastPost']['postId']]['user'] = array(
                'class' => 'ow_icon_control ow_ic_user',
                'href' => !empty($urls[$userId]) ? $urls[$userId] : '#',
                'label' => !empty($avatars[$userId]['title']) ? $avatars[$userId]['title'] : ''
            );

            $toolbars[$topic['lastPost']['postId']]['date'] = array(
                'label' => $topic['lastPost']['createStamp'],
                'class' => 'ow_ipc_date'
            );
        }
        $forumWidget->assign($topicName.'Toolbars', $toolbars);

        $groups = FORUM_BOL_ForumService::getInstance()->findGroupByIdList($groupIds);

        $groupList = array();

        $sectionIds = array();

        foreach ($groups as $group) {
            $groupList[$group->id] = $group;

            if (!in_array($group->sectionId, $sectionIds)) {
                array_push($sectionIds, $group->sectionId);
            }
        }
        $forumWidget->assign($topicName.'Groups', $groupList);

        $sectionList = FORUM_BOL_ForumService::getInstance()->findSectionsByIdList($sectionIds);
        $forumWidget->assign($topicName.'Sections', $sectionList);
    }

    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * Get period
     *
     * @param string $period
     * @return array
     */
    protected function getPeriod($period)
    {
        switch ($period)
        {
            case 'last_six_months' :
                return array(
                    strtotime('first day of 6 months ago 00:00:00'),
                    strtotime('today 23:59:59')
                );

            case 'last_three_months' :
                return array(
                    strtotime('first day of 3 months ago 00:00:00'),
                    strtotime('today 23:59:59')
                );

            case 'last_two_months' :
                return array(
                    strtotime('first day of 2 months ago 00:00:00'),
                    strtotime('today 23:59:59')
                );

            case 'last_month' :
                return array(
                    strtotime('first day of last month 00:00:00'),
                    strtotime('today 23:59:59')
                );

            case 'last_week' :
                return array(
                    strtotime('monday last week'),
                    strtotime('today 23:59:59')
                );

            case 'today' :
            default      :
                return array(
                    strtotime('today'),
                    strtotime('today 23:59:59')
                );
        }
    }

    /**
     * @param $topicLimit
     * @param null $excludeGroupIdList
     * @param bool $lastTopics
     * @param bool $mostViewed
     * @param bool $lastPosts
     * @param null $period
     * @return array
     */
    public function getCustomLatestTopicList( $topicLimit, $excludeGroupIdList = null,$lastTopics=true,$mostViewed=false,$lastPosts=false, $period=null )
    {
        $timeStart=0;
        $timeEnd=0;
        if ( isset($period) )
        {
            list($timeStart, $timeEnd) = $this->getPeriod($period);
        }
        $topicList = $this->topicDao->findCustomLastTopicList($topicLimit, $excludeGroupIdList,$lastTopics,$mostViewed,$lastPosts ,$timeStart,$timeEnd);

        if ( !$topicList )
        {
            return array();
        }
        $topics = array();
        $postIds = array();
        foreach ($topicList as $topic) {
            $postIds[] = $topic['lastPostId'];
            $topicIds[] = $topic['id'];
        }

        $postList = FORUM_BOL_ForumService::getInstance()->getTopicLastReplyList($postIds);
        foreach ( $topicList as $topic )
        {
            if ( empty($postList[$topic['id']]) )
            {
                continue;
            }
            //prepare post Info
            $postInfo = $postList[$topic['id']];
            $postInfo['postUrl'] = FORUM_BOL_ForumService::getInstance()->getLastPostUrl($topic['id'], $topic['postCount'], $postInfo['postId']);

            //prepare topic info
            $topic['lastPost'] = $postInfo;
            $topic['topicUrl'] = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $topic['id']));
            $topics[] = $topic;
        }
        return $topics;
    }

    /***
     * @param $name
     * @return string
     */
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmforumplus')->getStaticUrl(). 'images/'.$name.'.svg';
    }

    /***
     * @param $ext
     * @return string
     */
    public function getProperIcon($ext){
        $videoFormats = array('mov','mkv','mp4','avi','flv','ogg','mpg','mpeg');

        $wordFormats = array('docx','doc','docm','dotx','dotm');

        $excelFormats = array('xlsx','xls','xlsm');

        $zipFormats = array('zip','rar');

        $imageFormats =array('jpg','jpeg','gif','tiff','png');

        if(in_array($ext,$videoFormats)){
            return $this->getIconUrl('avi');
        }
        else if(in_array($ext,$wordFormats)){
            return $this->getIconUrl('doc');
        }
        else if(in_array($ext,$excelFormats)){
            return $this->getIconUrl('xls');
        }
        else if(in_array($ext,$zipFormats)){
            return $this->getIconUrl('zip');
        }
        else if(in_array($ext,$imageFormats)){
            return $this->getIconUrl('jpg');
        }
        else if(strcmp($ext,'pdf')==0){
            return $this->getIconUrl('pdf');
        }
        else if(strcmp($ext,'txt')==0){
            return $this->getIconUrl('txt');
        }
        else{
            return $this->getIconUrl('file');
        }
    }

    public function addIconsToForumAttachments(OW_Event $event){
        $attachments = $event->getParams()['attachments'];
        foreach ($attachments as &$postAttachment)
        {
            foreach ($postAttachment as &$attachment) {
                $fileNameArr = explode('.', $attachment['fileName']);
                $fileNameExt = end($fileNameArr);
                $iconUrl = FRMFORUMPLUS_BOL_Service::getInstance()->getProperIcon(strtolower($fileNameExt));
                $attachment['iconUrl'] = $iconUrl;
            }
        }
        $event->setData($attachments);
    }

    public function onHandleMoreInForum(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['post']))
        {
            $post = $params['post'];
            $text = explode("<!--more-->", $post['text']);
            $isPreview = count($text) > 1;
            if ( $isPreview ){
                $post['showMore'] = true;
                $post['beforeMoreText'] = $text[0];
                $post['afterMoreText'] = $text[1];
                $event->setData(array('post'=>$post));
            }
            else{
                $post['beforeMoreText'] = $post['text'];
                $event->setData(array('post'=>$post));
            }
        }
    }

    public function onLoadPostListInForum(OW_Event $event)
    {
        $jsUrl = OW::getPluginManager()->getPlugin('frmforumplus')->getStaticJsUrl() . 'frmforumplus.js';
        OW::getLanguage()->addKeyForJs('base', 'empty_list');
        OW::getDocument()->addScript($jsUrl);
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmforumplus")->getStaticCssUrl() . 'frmforumplus.css');
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'forum',
            'action' => 'group-topic-add',
            'sectionIcon' => 'ow_ic_forum',
            'sectionLabel' => OW::getLanguage()->text('forum', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('frmforumplus', 'email_notifications_group_topic'),
            'selected' => true
        ));
    }

    public function onForumGroupTopicAdd( OW_Event $event ){
        try {
            $params = $event->getParams();
            if (isset($params['groupId'])) {
                $userId = OW::getUser()->getId();
                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
                $avatar = $avatars[$userId];
                $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);
                $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
                $topicUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $params['topicId']));
                $notificationParams = array(
                    'pluginKey' => 'frmforumplus',
                    'action' => 'group-topic-add',
                    'entityType' => 'group-topic-add',
                    'entityId' => $params['topicId'],
                    'userId' => null,
                    'time' => time()
                );

                $notificationData = array(
                    'string' => array(
                        "key" => 'frmforumplus+notify_add_group_topic',
                        "vars" => array(
                            'groupTitle' => $group->title,
                            'groupUrl' => $groupUrl,
                            'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                            'topicUrl' => $topicUrl,
                            'topicTitle' => $params['topicTitle'],
                            'userUrl' => $userUrl
                        )
                    ),
                    'avatar' => $avatar,
                    'content' => '',
                    'url' => $topicUrl
                );
                $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($group->id);

                // send notifications in batch to userIds
                $userIds = array_diff($userIds, [OW::getUser()->getId()]);
                $event = new OW_Event('notifications.batch.add',
                    ['userIds'=>$userIds, 'params'=>$notificationParams],
                    $notificationData);
                OW::getEventManager()->trigger($event);

                // check if group users should follow forum
                $config = OW::getConfig();
                if ($config->configExists('frmforumplus', 'subscribe_group_users_to_topic') &&
                    $config->getValue('frmforumplus', 'subscribe_group_users_to_topic') &&
                    FRMSecurityProvider::checkPluginActive('forum', true)) {
                    FORUM_BOL_SubscriptionService::getInstance()->addMultipleSubscription($userIds, $params['topicId']);
                }
            }
        } catch (Exception $e) {
        }
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'frmforumplus')
            return;

        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];
        if ($entityType == 'group-topic-add') {
            if (FRMSecurityProvider::checkPluginActive('forum', true)) {
                $forumTopic = FORUM_BOL_ForumService::getInstance()->findTopicById($entityId);
                if(isset($forumTopic)) {
                    $notificationData["string"]["vars"]["topicTitle"] = $forumTopic->title;
                    $groupId = $forumTopic->groupId;
                    $group=FORUM_BOL_ForumService::getInstance()->findGroupById($groupId);
                    if(isset($group)) {
                        $notificationData["string"]["vars"]["groupTitle"] = $group->name;
                    }
                }
            }
        }

        $event->setData($notificationData);
    }


    /**
     * @param OW_Event $event
     */
    public function addButtonShowInGroupForum(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!OW::getUser()->isAdmin() || !OW::getUser()->isAuthorized('groups'))
        {
            return;
        }

        if(!isset($params['setting']) || !isset($params['groupId']))
        {
            return;
        }

        $groupId = $params['groupId'];
        $setting = $params['setting'];

        if(!isset($setting['toolbar']))
        {
            return;
        }

        $toolbar = $setting['toolbar'];

        $config = OW::getConfig();

        $addCode='';
        $removeCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>rand(1,10000),'isPermanent'=>true,'activityType'=>'add_group_topic_widget')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $addCode = $frmSecuritymanagerEvent->getData()['code'];
        }

        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>rand(1,10000),'isPermanent'=>true,'activityType'=>'remove_group_topic_widget')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $removeCode= $frmSecuritymanagerEvent->getData()['code'];
        }

        $addButton=array();
        $addButton['label'] = OW::getLanguage()->text('frmforumplus', 'add_to_topic_forum_group_widget');
        $addButton['href'] =  OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMFORUMPLUS_CTRL_TopicGroup','addGroupForumTopicToWidget', array('groupId' => $groupId)), array('code' => $addCode));

        $removeButton=array();
        $removeButton['label'] = OW::getLanguage()->text('frmforumplus', 'remove_to_topic_forum_group_widget');
        $removeButton['href'] =   OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('FRMFORUMPLUS_CTRL_TopicGroup','removeGroupForumTopicToWidget', array('groupId' => $groupId)), array('code' => $removeCode));

        if(!$config->configExists('frmforumplus','selected_groups_forums'))
        {
            $whichButton = $addButton;
        }else{
             $selectedGroupsForum  = json_decode($config->getValue('frmforumplus','selected_groups_forums'),true);
            if ( !in_array($groupId,$selectedGroupsForum) ){
                $whichButton = $addButton;
            }else{
                $whichButton = $removeButton;
            }
        }
        array_push($toolbar, $whichButton);
        $event->setData(['toolbar'=>$toolbar]);
    }
}