<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */
class FRMMENTION_BOL_Service
{
    private static $classInstance;
    private $regex_view = '((( |^|\n|\t|>|:|\(|\))@)(\w+))';
    private $notifications_action = 'mentioned';

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

    /***
     * @param BASE_CLASS_EventCollector $event
     */
    public function privacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $privacyValueEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD,
            array('key' => 'frmmention_search_my_username')));
        $defaultValue = 'everybody';
        if(isset($privacyValueEvent->getData()['value'])){
            $defaultValue = $privacyValueEvent->getData()['value'];
        }
        $action = array(
            'key' => 'frmmention_search_my_username',
            'pluginKey' => 'frmmention',
            'label' => $language->text('frmmention', 'privacy_action_search_my_username'),
            'description' => '',
            'defaultValue' => $defaultValue
        );

        $event->add($action);
    }

    /***
     * @param $authorId
     * @param $mentionedUserId
     * @param $textLink
     * @param $textDicKey
     * @param $entityType
     * @param $entityId
     */
    public function addToNotificationList($authorId, $mentionedUserId, $textLink, $textDicKey, $entityType, $entityId)
    {
        //add new notif
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($authorId));
        $authorName = BOL_UserService::getInstance()->getDisplayName($authorId);
        $authorUrl = BOL_UserService::getInstance()->getUserUrl($authorId);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $textLink)));
        if(isset($stringRenderer->getData()['string'])){
            $textLink = $stringRenderer->getData()['string'];
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $authorUrl)));
        if(isset($stringRenderer->getData()['string'])){
            $authorUrl = $stringRenderer->getData()['string'];
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $avatars[$authorId]['src'])));
        if(isset($stringRenderer->getData()['string'])){
            $avatars[$authorId]['src'] = $stringRenderer->getData()['string'];
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $avatars[$authorId]['url'])));
        if(isset($stringRenderer->getData()['string'])){
            $avatars[$authorId]['url'] = $stringRenderer->getData()['string'];
        }

        // Check if user is blocked
        $blocked = BOL_UserService::getInstance()->isBlocked($authorId, $mentionedUserId);
        if (!$blocked) {
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'frmmention',
                'entityType' => $entityType,
                'entityId' => $entityId,
                'action' => $this->notifications_action,
                'userId' => $mentionedUserId,
                'time' => time()
            ), array(
                'avatar' => $avatars[$authorId],
                'string' => array(
                    'key' => $textDicKey,
                    'vars' => array(
                        'userName' => $authorName,
                        'userUrl' => $authorUrl,
                        'textLink'=>$textLink
                    )
                ),
                'url' => $textLink
            ));
            OW::getEventManager()->trigger($event);
        }
    }

    /***
     * @param $content
     * @return mixed|string
     */
    public function fixMentionPaste($content)
    {
        $content=str_replace("@&zwnj;","@",$content);
        $content=utf8_encode($content);
        $content=str_replace("@â","@",$content);
        $content=utf8_decode($content);
        $content=str_replace("&nbsp;"," ",$content);
        return $content;
    }

    /***
     * @param $entityType
     * @param $entityId
     */
    public function deleteAllNotificationsByEntity($entityType, $entityId)
    {
        if(FRMSecurityProvider::checkPluginActive('notifications', true)){
            NOTIFICATIONS_BOL_Service::getInstance()->deleteNotificationByEntityAndAction($entityType, $entityId, $this->notifications_action);
        }
    }

    /***
     * @param $content
     * @param $entityId
     * @param $entityType
     * @param $authorId
     * @param $textLink
     * @param $isComment
     * @param NEWSFEED_BOL_Action|null $action
     */
    private function findAndNotifyFromContent($content, $entityId, $entityType, $authorId, $textLink, $isComment, $action=null){
        $textDicKey = 'frmmention+console_notification_newsfeed';
        if($isComment)
            $textDicKey = 'frmmention+console_notification_comment';
        $content=$this->fixMentionPaste($content);
        preg_match_all('/'.$this->regex_view.'/', $content, $matches);
        if(isset($matches[4])){
            foreach($matches[4] as $match){
                $user = BOL_UserService::getInstance()->findByUsername($match);
                if($user){
                    if($user->getId() != $authorId) {
                        if($this->canSendMentionNotification($entityType, $user, $action)) {
                            $this->addToNotificationList($authorId, $user->getId(), $textLink, $textDicKey, $entityType, $entityId);
                        }
                    }
                }
            }
        }
    }

    /***
     * @param $params
     */
    public function notifyFromComment ( $params ) {
        $entityId = $params['entityId'];
        $entityType = $params['entityType'];
        $comment = BOL_CommentService::getInstance()->findComment($params['commentId']);
        $content = $comment->getMessage();

        $action = null;
        if (isset($params['action'])){
            $action = $params['action'];
        }

        if(isset($params['pluginKey']) && $params['pluginKey'] == 'groups') {
            $entityType = 'base_profile_wall';
            $entityId = $comment->id;
            if (isset($params['entityType']) && $params['entityType'] == 'groups-join') {
                if ($action == null) {
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($params['entityType'], $params['entityId']);
                }
                if($action == null) {
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction('user-status', $params['entityId']);
                }
                if($action == null) {
                    $groupId = $this->findGroupIdByEntityId($params['entityId']);
                    $g = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                    if(empty($g)){
                        return;
                    }
                    $textLink = GROUPS_BOL_Service::getInstance()->getGroupUrl($g);
                }else{
                    $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
                }
            }
        }
        else if(isset($params['pluginKey']) && $params['pluginKey'] == 'newsfeed') {
            $entityType = 'status_comment';
            $entityId = $comment->id;
            if (isset($params['entityType']) && $params['entityType'] == 'groups-status') {
                $entityType = 'base_profile_wall';
                if ($action == null) {
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($params['entityType'], $params['entityId']);
                }
                if($action == null){
                    $groupId = $this->findGroupIdByEntityId($params['entityId']);
                    $g = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                    if(empty($g)){
                        return;
                    }
                    $textLink = GROUPS_BOL_Service::getInstance()->getGroupUrl($g);
                }else {
                    $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
                }
            }
        }else if($entityType=="photo_comments") {
            $textLink = OW::getRouter()->urlForRoute('view_photo', array('id' => $entityId));
        }else if($entityType=="groups-wal") {
            $g = GROUPS_BOL_Service::getInstance()->findGroupById($entityId);
            $textLink = GROUPS_BOL_Service::getInstance()->getGroupUrl($g);
        }else if ($entityType == 'event') {
            $textLink = EVENT_BOL_EventService::getInstance()->getEventUrl($entityId);
        }else if($entityType=="blog-post") {
            $blog = PostService::getInstance()->findById($entityId);
            $textLink = PostService::getInstance()->getPostUrl($blog);
        }

        //find author and text link
        if(!isset($textLink)){
            if ($action == null) {
                $action = NEWSFEED_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);
            }
            if(!isset($action)) {
                $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
                if(!isset($action))
                    return;
            }
            $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
        }

        //add to notifications from new comment
        $authorId = isset($params['authorId'])?$params['authorId']:OW::getUser()->getId();
        $this->findAndNotifyFromContent($content, $entityId, $entityType, $authorId, $textLink, true);
    }
    /***
     * @param OW_Event $e
     */
    public function onAddComment( OW_Event $e )
    {
        $params = $e->getParams();
        $this->notifyFromComment($params);
    }

    /***
     * @param $content
     * @param array $params
     * @return null|string|string[]
     */
    private function findAndReplaceUsernamesFromView($content, $params = array()){
        $content = $this->fixMentionPaste($content);
        $callbackClass = new REGEX_CALLBACK($params);
        $replace1 = preg_replace_callback('/'.$this->regex_view.'/', array($callbackClass, 'callback') ,  $content);

        return $replace1;
    }

    /***
     * @param $content
     * @return mixed
     */
    public function findUsernamesFromView($content){
        $usernameList = array();
        $content = $this->fixMentionPaste($content);
        preg_match_all ('/'.$this->regex_view.'/', $content, $usernameList);

        return $usernameList[4];
    }

    /***
     * @param $event
     * @param $key
     * @return mixed
     */
    private function findAndProcessKeyFromEvent($event, $key){
        $params = $event->getParams();
        $data = $event->getData();
        if(isset($data[$key])) {
            $string = $data[$key];
        }else{
            if (isset($params[$key])) {
                $string = $params[$key];
            }
        }

        if(isset($string)){
            $string = $this->findAndReplaceUsernamesFromView($string, $params);
            $data[$key] = $string;
        }
        return $data;
    }

    /***
     * @param OW_Event $event
     * @return mixed
     */
    public function renderNewsfeed( OW_Event $event )
    {
        $data = $this->findAndProcessKeyFromEvent($event, 'content');
        $event->setData($data);
        return $data;
    }

    /***
     * @param BASE_CLASS_EventProcessCommentItem $event
     */
    public function renderComments( BASE_CLASS_EventProcessCommentItem $event )
    {
        $string = $event->getDataProp('content');
        $params = $event->getParams();

        $string = $this->findAndReplaceUsernamesFromView($string, $params);

        $event->setDataProp('content', $string);
    }

    /***
     * @param OW_Event $event
     * @return mixed
     */
    public function renderString( OW_Event $event )
    {
        $data = $this->findAndProcessKeyFromEvent($event, 'string');
        $event->setData($data);
        return $data;
    }

    /**
     * @param OW_Event $event
     */
    public function onCommentDelete(OW_Event $event){
        $params = $event->getParams();
        /** @var BOL_Comment $comment */
        $comment = $params['comment'];
        if(isset($comment)){
            preg_match_all('/'.$this->regex_view.'/u', $comment->getMessage(), $commentMatches);
            if(isset($commentMatches) &&  isset($commentMatches[4]) && count($commentMatches[4]) > 0) {
                if ($params['entityType'] == 'user-status') {
                    $this->deleteAllNotificationsByEntity('status_comment', $params['commentId']);
                }else if ($params['entityType'] == 'groups-status') {
                    $this->deleteAllNotificationsByEntity('base_profile_wall', $params['commentId']);
                }else {
                    $this->onEntityUpdate(new OW_Event('', array('entityType' => $params['entityType'], 'entityId' => $params['entityId'], 'pluginKey' => $params['pluginKey'])));
                }
            }
        }
    }

    /***
     * @param BASE_CLASS_EventCollector $e
     */
    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        //register notif to be shown
        $e->add(array(
            'section' => 'frmmention',
            'action' => $this->notifications_action,
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('frmmention', 'title'),
            'description' => OW::getLanguage()->text('frmmention', 'you_are_mentioned'),
            'selected' => true
        ));
    }

    /***
     * @param OW_Event $e
     */
    public function onNotificationRender( OW_Event $e )
    {
        //how to show
        $params = $e->getParams();
        if ( $params['pluginKey'] != 'frmmention')
        {
            return;
        }
        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();

        $user = null;
        if (isset($params['cache']['users']['username'][$data['avatar']['urlInfo']['vars']['username']])) {
            $user = $params['cache']['users']['username'][$data['avatar']['urlInfo']['vars']['username']];
        }

        if ($user == null) {
            $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        }

        if ( !$user )
        {
            return;
        }
        $e->setData($data);
    }

    /***
     * @param OW_Event $e
     */
    public function onNotificationDuplicate( OW_Event $e )
    {
        // problem with overriding groups-status notifications
        $params = $e->getParams();
        $notification = $params['notificationDto'];
        if ( $notification->pluginKey == 'frmmention' && $notification->entityType == 'groups-status')
        {
            $e->setData(['cancel'=>true]);
        }
    }

    /***
     * @param $actionId
     * @param $entityId
     * @param $type
     * @return null
     */
    public function findGroupIdByActionId($actionId, $entityId, $type){
        $activityId = null;
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByActionIds(array($actionId));
        foreach($activities as $activity){
            if($activity->activityType=='create'){
                $activityId = $activity->id;
            }
        }
        if($activityId!=null){
            $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids(array($activityId));
            $feedList = $feedList[$activityId];
            foreach ($feedList as $feed) {
                if ($feed->feedType == $type) {
                    return $feed->feedId;
                }
            }
        }else {
            $groupId = $this->findGroupIdByEntityId($entityId);
            if($groupId == null){
                return null;
            }else{
                return $groupId;
            }
        }
        return null;
    }

    /***
     * @param $entityId
     * @return null
     */
    public function findGroupIdByEntityId($entityId){
        if($entityId == null){
            return null;
        }
        $groupStatus = NEWSFEED_BOL_StatusDao::getInstance()->findById($entityId);
        if($groupStatus == null || $groupStatus->feedType != 'groups'){
            return null;
        }else if($groupStatus != null && $groupStatus->feedType == 'groups'){
            return $groupStatus->feedId;
        }
        return null;
    }


    /***
     * @param OW_Event $e
     */
    public function onEntityUpdate(OW_Event $e )
    {
        $params = $e->getParams();
        $params['event_name'] = $e->getName();
        $params['author_id'] = OW::getUser()->getId();
        $valid = FRMSecurityProvider::sendUsingRabbitMQ($params, 'processMentionEntityUpdate');
        if (!$valid) {
            $this->processMentionEntityUpdate($params);
        }
    }


    /**
     * @param OW_EVENT $event
     */
    public function onRabbitMQNotificationRelease(OW_EVENT $event) {
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }
        $params = $data->body;
        $params = (array) json_decode($params);
        if (isset($params['itemType']) && $params['itemType'] == 'processMentionEntityUpdate') {
            $this->processMentionEntityUpdate($params);
        }
    }

    /**
     * @param $params
     */
    public function processMentionEntityUpdate($params)
    {
        $entityId = $params['entityId'];
        $entityType = $params['entityType'];
        $authorId = $params['author_id'];
        $action = null;
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return;
        }
        $textLink = null;
        $content = isset($params['newContent'])?$params['newContent']:'';
        if($entityType=='news-entry') {
            $entry = EntryService::getInstance()->findById($entityId);
            if (isset($entry) && !$entry->isDraft()) {
                $content = $entry->entry;
                $textLink = EntryService::getInstance()->getEntryUrl($entry);
            }
        }elseif ($entityType=='blog-post'){
            $entry = PostService::getInstance()->findById($entityId);
            if (isset($entry) && !$entry->isDraft()) {
                $content = $entry->post;
                $textLink = PostService::getInstance()->getPostUrl($entry);
            }
        }else if(FRMSecurityProvider::checkPluginActive('groups', true) && $entityType==GROUPS_BOL_Service::FEED_ENTITY_TYPE) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
            if (isset($action)) {
                $jsonTmp = json_decode($action->data, true);
                $content = $jsonTmp["content"]["vars"]["description"];
            }
            $entityType = "groups-status";
        }else if($entityType=='groups-status') {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
            if (isset($action)) {
                if (strcmp($params['event_name'], 'hashtag.edit_newsfeed') == 0) {
                    $content = nl2br($params['text']);
                } else {
                    $jsonTmp = json_decode($action->data, true);
                    $content = nl2br($jsonTmp["status"]);
                }
                $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
            }else if (isset($params['actionId'])){
                $action = NEWSFEED_BOL_ActionDao::getInstance()->findActionById($params['actionId']);
                if (empty($action)) {
                    return;
                }
                $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
            }
        }else if($entityType=='event') {
            $event = EVENT_BOL_EventService::getInstance()->findByIdList([$entityId]);
            if(count($event)==1) {
                $event = $event[0];
                $content = $event->description;
                $textLink = EVENT_BOL_EventService::getInstance()->getEventUrl($entityId);
            }
        }else if($entityType=='video_comments') {
            $clip = VIDEO_BOL_ClipService::getInstance()->findClipById($entityId);
            if(isset($clip)) {
                $content = $clip->description;
                $textLink = VIDEO_BOL_ClipService::getInstance()->getVideoUrl($clip);
            }
        }else if($entityType=='forum-post'){
            $post = FORUM_BOL_ForumService::getInstance()->findPostById($entityId);
            if (isset($post)) {
                $content = strip_tags(UTIL_HtmlTag::stripTags($post->text));
                $textLink = FORUM_BOL_ForumService::getInstance()->getPostUrl($post->topicId,$post->id);
            }
        }else if($entityType=='user-status') {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
            if (isset($action)) {
                $jsonTmp = json_decode($action->data, true);
                $content = nl2br($jsonTmp["status"]);
            }
        }else if($entityType=='photo_comments') {
            $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($entityId);
            if (isset($photo)) {
                $content = strip_tags(UTIL_HtmlTag::stripTags($photo->description));
                $textLink = OW::getRouter()->urlForRoute('view_photo', array('id' => $photo->id));
            }
        }else{
            return;
        }

        // delete all related notifications
        $this->deleteAllNotificationsByEntity($entityType, $entityId);
        $commentsEntityType = $entityType;
        if ($entityType == 'user-status') {
            $commentsEntityType = 'status_comment';
        }else if ($entityType == 'groups-status') {
            $commentsEntityType = 'base_profile_wall';
        }
        $comments = BOL_CommentService::getInstance()->findFullCommentList($params['entityType'], $params['entityId']);
        foreach ($comments as $comment) {
            $this->deleteAllNotificationsByEntity($commentsEntityType, $comment->id);
        }

        if($params['event_name']!='feed.delete_item') {
            //send notification
            if(!isset($textLink)){
                $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
                if(!isset($action))
                    return;
                $textLink = NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId());
            }
            if(isset($jsonTmp) && isset($jsonTmp['data']['userId'])){
                $authorId = $jsonTmp['data']['userId'];
            }

            $this->findAndNotifyFromContent($content, $entityId, $entityType, $authorId, $textLink, false, $action);
            $comments = BOL_CommentService::getInstance()->findFullCommentList($params['entityType'], $params['entityId']);
            foreach ($comments as $comment) {
                $params['commentId'] = $comment->id;
                $params['authorId'] = $comment->userId;
                $this->notifyFromComment($params);
            }
        }
    }

    /**
     * @param $entityType
     * @param BOL_User $user
     * @param NEWSFEED_BOL_Action|null $action
     * @return bool
     */
    private function canSendMentionNotification($entityType, BOL_User $user, NEWSFEED_BOL_Action $action=null)
    {

        if ($action && OW::getPluginManager()->isPluginActive('groups') && $entityType == GROUPS_BOL_Service::GROUP_FEED_ENTITY_TYPE) {
            $actionData = json_decode($action->data);
            $groupId = (int)$actionData->contextFeedId;

            if ($actionData->contextFeedType == GROUPS_BOL_Service::ENTITY_TYPE_GROUP) {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                $userId = OW::getUser()->getId();

                $query = "SELECT DISTINCT ".OW_DB_PREFIX."base_user.id
                FROM ".OW_DB_PREFIX."base_user join ".OW_DB_PREFIX."base_question_data on ".OW_DB_PREFIX."base_user.id=".OW_DB_PREFIX."base_question_data.userId
                WHERE ".OW_DB_PREFIX."base_user.id IN (
                    SELECT DISTINCT userId
                        FROM " . OW_DB_PREFIX . "friends_friendship
                        WHERE friendId=:uId AND status='active'
                        UNION
                        SELECT DISTINCT friendId
                        FROM " . OW_DB_PREFIX . "friends_friendship
                        WHERE userId=:uId AND status='active'
                )";

                $groupUserIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
                if ($group->whoCanView == GROUPS_BOL_Service::WCV_ANYONE) {

                    $friendIds = array();
                    if (OW::getPluginManager()->isPluginActive('friends')) {
                        $friendIds = OW::getDbo()->queryForColumnList($query, array('uId' => $userId));
                    }

                    if (!in_array($user->getId(), $groupUserIds) && !in_array($user->getId(), $friendIds)) {
                        return false;
                    }

                } else if ($group->whoCanView == GROUPS_BOL_Service::WCV_INVITE) {
                    if (!in_array($user->getId(), $groupUserIds)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function addGroupIdToJs() {
        $appendToJs = "";
        $uriParams = OW::getRequest()->getUriParams();
        if (!is_null($uriParams) && array_key_exists("groupId", $uriParams) && UTIL_String::startsWith(OW::getRouter()->getUri(), "groups/")) {
            $groupId = (int)$uriParams['groupId'];
            $appendToJs .= ";var groupId=" . $groupId . ";";
        }
        return $appendToJs;
    }

    /***
     * @param $kw
     * @param null $limit
     * @return array
     */
    public function findPrioritizedUsers( $kw, $limit = null) {

        $userId = OW::getUser()->getId();
        $limitStr = $limit === null ? '' : 'LIMIT 0, ' . intval($limit*2);

        $queryPart = "";
        $params = array('kw' =>  '%'.$kw . '%');
        $friendsQuery = "SELECT DISTINCT userId
                    FROM ".OW_DB_PREFIX."friends_friendship
                    WHERE friendId=:uId AND status='active'
                    UNION
                    SELECT DISTINCT friendId
                    FROM ".OW_DB_PREFIX."friends_friendship
                    WHERE userId=:uId AND status='active'
                    ";
        $union = "
                UNION
                 ";
        $groupQuery = "
                    SELECT DISTINCT userId
                    FROM ow_groups_group_user
                    WHERE ow_groups_group_user.groupId=:gId ";

        if (OW::getPluginManager()->isPluginActive('groups') && isset($_GET['groupId']) && is_numeric($_GET['groupId'])) {
            $groupId = (int)$_GET['groupId'];
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
            if ($group->whoCanView == GROUPS_BOL_Service::WCV_ANYONE) {
                if (OW::getPluginManager()->isPluginActive('friends')) {
                    $queryPart .= $friendsQuery . $union;
                    $params['uId'] = $userId;
                }
            }
            $queryPart .= $groupQuery;
            $params['gId'] = $groupId;
        } else if (OW::getPluginManager()->isPluginActive('friends')) {
            $queryPart .= $friendsQuery;
            $params['uId'] = $userId;
        } else {
            return array();
        }

        //SELECT FROM FRIENDS
        $query = "SELECT DISTINCT ".OW_DB_PREFIX."base_user.id
            FROM ".OW_DB_PREFIX."base_user join ".OW_DB_PREFIX."base_question_data on ".OW_DB_PREFIX."base_user.id=".OW_DB_PREFIX."base_question_data.userId
            WHERE ".OW_DB_PREFIX."base_user.id IN (
                " . $queryPart . "
            )
            AND (username like :kw  or( questionName='realname' and  textValue like :kw )) ". $limitStr;;


        $all_users = OW::getDbo()->queryForColumnList($query, $params);


        return $all_users;
    }

    /***
     * @param $userIdList
     * @return array
     */
    public function getUserInfoForUserIdList( $userIdList ) {
        if (empty($userIdList))
        {
            return array();
        }

        $userInfoList = BOL_UserDao::getInstance()->findByIdList($userIdList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        $avatars =  $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);

        $result = array();
        foreach ($userInfoList as $item) {
            /* @var $item BOL_User*/
            $src = $avatars[$item->getId()]['src'];
            $itemInformation = array();
            $itemInformation['username'] = $item->getUsername();
            $itemInformation['title'] = $item->getUsername();
            $itemInformation['displayName'] = $displayNames[$item->getId()];
            $itemInformation['userUrl'] = OW::getRouter()->urlForRoute('base_user_profile', ['username' => $item->getUsername()]);
            $itemInformation['id'] = $item->getId();
            $itemInformation['link'] = OW::getRouter()->urlForRoute('base_user_profile', ['username' => $item->getUsername()]);
            $itemInformation['image'] = $src;
            $itemInformation['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo((int)$item->getId(), $src);
            $result[] = $itemInformation;
        }

        return $result;
    }
}

class REGEX_CALLBACK {
    private $params;

    function __construct($params) {
        $this->params = $params;
    }

    public function callback($matches) {
        $matches1 = $matches[4];
        $url = BOL_UserService::getInstance()->getUserUrlForUsername($matches1);
        $username = $matches1;
        $user = null;
        if (isset($this->params['data']['cache']['username'][$username])) {
            $user = $this->params['data']['cache']['username'][$username];
        }
        if (isset($this->params['username'][$username])) {
            $user = $this->params['username'][$username];
        }
        if ($user == null) {
            $user = BOL_UserService::getInstance()->findByUsername($username);
        }
        if($user!== null){
            return $matches[3] . '<a class="frmmention_person" href="'.$url.'">@&#8235;'.$matches1.'</a>';
        }
        return $matches[3] . '@&#8235;'.$matches1.'';
    }
}
