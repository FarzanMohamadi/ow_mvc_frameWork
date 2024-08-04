<?php
final class FRMCOMMENTPLUS_BOL_Service
{

    private static $classInstance;

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function frmcommentplus_afterComment_notification( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        $viewer = OW::getUser();
        $lang = OW::getLanguage();

        $action = null;
        if (isset($params['action'])){
            $action = $params['action'];
        }

        if(!FRMSecurityProvider::checkPluginActive('newsfeed')) {
            return;
        }
        $eventData = array(
            'commentId' => $params['commentId']
        );

        if ($action == null) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);
        }

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = NEWSFEED_BOL_Service::getInstance()->findActivity( NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $ownerPostId = $cActivity->userId;
        }
        else
        {
            $ownerPostId = $actionData['data']['userId'];
        }

        $entitySpec = $this->getEntitySpecifications( $action, $params['entityType'], $params['entityId']);
        $actionType = $entitySpec['actionType'];
        $entityType = $entitySpec['entityType'];
        $pluginKey = $entitySpec['pluginKey'];
        $contextUrl = $entitySpec['contextUrl'];

        $commentList =  BOL_CommentService::getInstance()->findFullCommentList($params['entityType'], $params['entityId']);
        $lastComment = end($commentList);
        $userSentList = array();
        foreach($commentList as $comment) {
            // skip status Owner and user is commenting
            if($comment->userId == $ownerPostId || $comment->userId == $params['userId']) continue;
            if (in_array($comment->userId, $userSentList)) continue;
            // notification
            $userService = BOL_UserService::getInstance();
            $notificationParams = array(
                'pluginKey' => $pluginKey ,
                'entityType' => $entityType,
                'entityId' => $lastComment->getId(),
                'action' => $actionType,
                'userId' => $comment->userId,
                'time' => time()
            );

            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']));
            $userName = $userService->getDisplayName($params['userId']);
            $userUrl = $userService->getUserUrl($params['userId']);

            $ownerUserName = $userService->getDisplayName($ownerPostId);
            $ownerUserUrl = $userService->getUserUrl($ownerPostId);
            $keyString = $this->getNotificationString($action,$params['userId'],$userName,$userUrl,$ownerUserUrl,$ownerUserName,$contextUrl,$params['commentMessage']);
            $data = array(
                'avatar' => $avatars[$params['userId']],
                'string' => array(
                    'key' => $keyString['keyString'],
                    'vars' => $keyString['vars']
                ),
                'content' => '',
                'url' => $contextUrl
            );

            $event = new OW_Event('notifications.add', $notificationParams, $data);
            OW::getEventManager()->trigger($event);

            // push notification, integrate with PUSH NOTIFICATIONS | PLUGIN
            $langValue = explode("+", $data['string']['key']);
            $this->pushNotificationIntegration($comment->userId, $lang->text($langValue[0], $langValue[1]));
            $userSentList[] = $comment->userId;
        }

    }

    public function getEntitySpecifications( $action, $entityType=null,$entityId=null ){

        if($entityType == "blog-post"){
            $actionType ='blogs-add_comment';
            $entityType='blogs-add_comment';
            $pluginKey='blogs';
            $contextUrl = OW::getRouter()->urlForRoute('post', array('id' => $entityId));
        }
        else if ($entityType == "news-entry"){
            $actionType ='news-add_comment';
            $entityType='news-add_comment';
            $pluginKey='frmnews';
            $contextUrl = OW::getRouter()->urlForRoute('entry', array('id' => $entityId));
        }
        else if ($entityType == "event"){
            $actionType ='event-add_comment';
            $entityType='event';
            $pluginKey='event';
            $contextUrl = OW::getRouter()->urlForRoute('event.view' , array('eventId'=>$entityId));
        }
        else if ($entityType == "video_comments"){
            $actionType ='video-add_comment';
            $entityType='video-add_comment';
            $pluginKey='video';
            $contextUrl = OW::getRouter()->urlForRoute('view_clip', array('id' => $entityId));
        }
        else if ($entityType == "photo_comments"){
            $actionType ='photo-add_comment';
            $entityType='photo-add_comment';
            $pluginKey='photo';
            $contextUrl = OW::getRouter()->urlForRoute('view_photo', array('id' => $entityId));
        }else{
            $contextUrl = $this->getContentUrl($action);
            $actionType='base_add_user_comment';
            $entityType='base_profile_wall';
            $pluginKey='base';
        }
        return $entitySpec=array('actionType'=>$actionType, 'entityType'=>$entityType, 'pluginKey'=>$pluginKey, 'contextUrl'=>$contextUrl);
    }


    public function deleteComment( OW_Event $e )
    {
        $params = $e->getParams();

        $entityType='base_profile_wall';

        if($params['entityType'] == "blog-post") {
            $entityType = 'blogs-add_comment';
        }
        else if ($params['entityType'] == "news-entry") {
            $entityType = 'news-add_comment';
        }
        else if ($params['entityType'] == "event") {
            $entityType = 'event';
        }
        else if ($params['entityType'] == "video_comments"){
            $entityType='video-add_comment';
        }
        else if ($params['entityType'] == "photo_comments") {
            $entityType = 'photo-add_comment';
        }
        $commentList =  BOL_CommentService::getInstance()->findFullCommentList($params['entityType'], $params['entityId']);
        foreach($commentList as $comment) {
            if ($comment->userId == $params["userId"])
                break;
            else
                OW::getEventManager()->call('notifications.remove', array(
                    'entityType' => $entityType,
                    'entityId' => $comment->id,
                    'userId' => $params["userId"]
                ));
        }
    }

    public function getNotificationString($action,$userId,$userName,$userUrl,$ownerUserUrl,$ownerUserName,$contextUrl, $comment) {
        $keyString = array();
        $keyString['keyString'] = 'frmcommentplus+comment_notification_string';
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $ownerUserUrl)));
        if(isset($stringRenderer->getData()['string'])){
            $ownerUserUrl = $stringRenderer->getData()['string'];
        }
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $contextUrl)));
        if(isset($stringRenderer->getData()['string'])){
            $contextUrl = $stringRenderer->getData()['string'];
        }
        if($action->entityType=='news-entry'){
            $newsService = EntryService::getInstance();
            $news = $newsService->findById($action->entityId);
            if(isset($news)){
                $keyString['keyString'] = 'frmcommentplus+comment_news_string';
                $keyString['vars'] = array(
                    'actor' => $userName,
                    'actorUrl' => $userUrl,
                    'contextUrl' => $contextUrl,
                    'comment' => UTIL_String::truncate( $comment, 120, '...' )
                );
            }
        }else{
            $keyString['vars'] = array(
                'actor' => $userName,
                'actorUrl' => $userUrl,
                'ownerUrl' => $ownerUserUrl,
                'ownerName' => $ownerUserName,
                'contextUrl' => $contextUrl,
                'comment' => UTIL_String::truncate( $comment, 120, '...' )
            );
        }

        if($action->entityType == 'confession-confession') {
            $keyString['keyString'] = 'frmcommentplus+comment_notification_string_for_confession';
        }

        return $keyString;
    }

    public function getContentUrl($action) {
        $content = BOL_ContentService::getInstance()->getContent($action->entityType, $action->entityId);
        return OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $action->id)) . '?ft=site';
    }

    public function pushNotificationIntegration($userId, $text) {
        $notification = new OW_Event('pushnotifications.send_notification', array(
                'userId'=> $userId,
                'content'=> $text)
        );

        OW::getEventManager()->trigger($notification);
    }

    public function onAfterFeedLikeNotification(OW_Event $event){
        $plugin = BOL_PluginService::getInstance()->findPluginByKey('newsfeed');
        if ($plugin == null || !$plugin->isActive()) {
            return;
        }
        $params = $event->getParams();
        $action = NEWSFEED_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);
        if ( empty($action) ){
            return;
        }
        $entrySpec = $this->getEntitySpecifications( $action, $params['entityType'], $params['entityId'] );
        $likesList = BOL_VoteService::getInstance()->findEntityLikes($params['entityType'],$params['entityId']);
        $userIds = array_column($likesList,'userId');
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds, true, true, true, true);
        foreach ($likesList as $likeItem) {
            if ($params['userId'] != (int)$likeItem->userId) {
                $avatar = $avatars[$params['userId']];
                $string['key'] = 'frmcommentplus+like_notification_string_for_likeList';
                $string['vars'] = array(
                    'actor' => $avatars[$params['userId']]['title'],
                    'actorUrl' => $avatars[$params['userId']]['url'],
                    'contextUrl' =>$entrySpec['contextUrl'],
                );
                $event = new OW_Event('notifications.add', array(
                    'pluginKey' => $entrySpec['pluginKey'],
                    'entityType' => $likeItem->entityType,
                    'entityId' => $likeItem->entityId,
                    'action' => 'like-notification',
                    'userId' => $likeItem->userId,
                    'time' => time()
                ), array(
                    'avatar' => $avatar,
                    'string' => $string,
                    'url' => $entrySpec['contextUrl'],
                ));

                OW::getEventManager()->trigger($event);

                $this->pushNotificationIntegration($likeItem->userId,  OW::getLanguage()->text('frmcommentplus', 'like_notification_string_for_likeList',$string['vars']));
            }
        }

    }

    public function notificationActionAdd(OW_Event $event){
        $event->add(array(
            'section' => 'newsfeed',
            'action' => 'like-notification',
            'sectionIcon' => 'ow_like',
            'sectionLabel' => OW::getLanguage()->text('frmcommentplus', 'notifications_section_label'),
            'description' => OW::getLanguage()->text('frmcommentplus', 'notifications_setting_status_comment'),
            'selected' => true
        ));
    }

    public function checkReplyPostCommentRequestParams(OW_Event $event) {
        if (isset($_POST['replyId']) && isset($_POST['replyUserId']) && !OW::getConfig()->getValue('frmcommentplus', 'enableReplyPostComment')) {
            $errorMessage = OW::getLanguage()->text('base', 'create_reply_post_comment_error');
            $event->setData(array('errorMessage' => $errorMessage));
        }
    }


}