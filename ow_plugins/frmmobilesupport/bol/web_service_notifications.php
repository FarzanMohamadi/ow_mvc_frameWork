<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceNotifications
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

    public function getNotifications(){
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $sentIds = array();
        if(isset($_GET['sentIds'])){
            $sentIds = $_GET['sentIds'];
            $sentIds = explode(',', $sentIds);
            $preparedSentIds = array();
            foreach ($sentIds as $sentId) {
                if (!empty(trim($sentId))) {
                    $preparedSentIds[] = $sentId;
                }
            }
            $sentIds = $preparedSentIds;
        }

        $service = NOTIFICATIONS_BOL_Service::getInstance();

        $notificationsData = array();
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        $notifications = $service->findNotificationList(OW::getUser()->getId(), time(), $sentIds, $count);
        foreach ($notifications as $notification){
            $notificationsData[] = $this->preparedNotificationData($notification);
        }

        return array('all_notifications' => $notificationsData);
    }

    public function getNewNotifications(){
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $userId = OW::getUser()->getId();

        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $newNotificationsData = array();
        $newNotifications = $service->findNewNotificationList($userId, null);

        foreach ($newNotifications as $newNotification){
            $newNotificationsData[] = $this->preparedNotificationData($newNotification);;
        }

        return $newNotificationsData;
    }

    public function saveNotificationsSetting() {
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array('valid' => false);
        }

        if (!OW::getUser()->isAuthenticated())
        {
            return array('valid' => false);
        }

        $notificationService =  NOTIFICATIONS_BOL_Service::getInstance();
        $actions = $notificationService->collectActionList();
        $settings = $notificationService->findRuleList(OW::getUser()->getId());

        $form = new Form('notification_setting');

        $processActions = array();

        foreach ( $actions as $action )
        {
            if ($action['section']=='admin' && !OW::getUser()->isAuthorized('base')) {
                continue;
            }
            $field = new CheckboxField($action['action']);
            $field->setValue(!empty($action['selected']));

            if ( isset($settings[$action['action']]) )
            {
                $field->setValue((bool) $settings[$action['action']]->checked);
            }

            $form->addElement($field);

            $processActions[] = $action['action'];
        }

        if ( OW::getRequest()->isPost() )
        {
            $notificationService->saveNotificationsSetting(OW::getUser()->getId(), $processActions, $settings, $_POST);
            return array('valid' => true);
        }

        return array('valid' => false);
    }

    public function getNotificationsSetting() {
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array();
        }

        if (!OW::getUser()->isAuthenticated()){
            return array();
        }

        $notificationService =  NOTIFICATIONS_BOL_Service::getInstance();
        $actions = $notificationService->collectActionList();
        $settings = $notificationService->findRuleList(OW::getUser()->getId());

        $items = array();

        foreach ( $actions as $action ) {
            if ($action['section']=='admin' && !OW::getUser()->isAuthorized('base')) {
                continue;
            }

            if ( empty($items[$action['section']]) ) {
                $items[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'actions' => array()
                );
            }

            $actionSelected = $action['selected'];
            if (isset($settings[$action['action']])) {
                $actionSelected = (bool) $settings[$action['action']]->checked;
            }

            $actionInfo = array(
                'name' => $action['action'],
                'type' => 'boolean',
                'label' => FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($action['description']),
                'required' => false,
                'presentation' => 'boolean',
                'values' => array(),
                'user_value' => $actionSelected,
                'value' => $actionSelected,
                'section' => $action['section'],
            );
            $items[$action['section']]['actions'][] = $actionInfo;
        }

        $userSchedule = $notificationService->getSchedule(OW::getUser()->getId());
        $items['schedule'] = array(
            'label' => OW::getLanguage()->text('notifications', 'config_schedule_title'),
            'actions' => array(
                array(
                    'name' => 'schedule',
                    'type' => 'radio_group',
                    'required' => false,
                    'presentation' => 'radio_group',
                    'values' => array(),
                    'user_value' => $userSchedule,
                    'value' => $userSchedule,
                    'section' => 'schedule',
                    'actions' => array(
                        array(
                            'name' => 'auto',
                            'type' => 'radio',
                            'label' => OW::getLanguage()->text('notifications', 'schedule_automatic'),
                            'required' => false,
                            'presentation' => 'radio',
                            'values' => array(),
                            'user_value' => $userSchedule == 'auto',
                            'value' => $userSchedule == 'auto',
                            'section' => 'schedule',
                        ),
                        array(
                            'name' => 'immediately',
                            'type' => 'radio',
                            'label' => OW::getLanguage()->text('notifications', 'schedule_immediately'),
                            'required' => false,
                            'presentation' => 'radio',
                            'values' => array(),
                            'user_value' => $userSchedule == 'immediately',
                            'value' => $userSchedule == 'immediately',
                            'section' => 'schedule',
                        ),
                        array(
                            'name' => 'never',
                            'type' => 'radio',
                            'label' => OW::getLanguage()->text('notifications', 'schedule_never'),
                            'required' => false,
                            'presentation' => 'radio',
                            'values' => array(),
                            'user_value' => $userSchedule == 'never',
                            'value' => $userSchedule == 'never',
                            'section' => 'schedule',
                        ),
                    )
                ),
            )
        );
        return $items;
    }

    public function getNewNotificationsCount() {
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return 0;
        }

        if(!OW::getUser()->isAuthenticated()){
            return 0;
        }

        $userId = OW::getUser()->getId();

        $service = NOTIFICATIONS_BOL_Service::getInstance();
        return $service->findNewNotificationCount($userId, null);
    }

    public function seenNotification(){
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $userId = OW::getUser()->getId();
        $service = NOTIFICATIONS_BOL_Service::getInstance();

        $notificationId = null;
        if (isset($_GET['notification_id'])) {
            $notificationId = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['notification_id']);
            $notificationId = UTIL_HtmlTag::stripTagsAndJs($notificationId);
        }
        $seenSingle = false;
        if ($notificationId != null && !empty($notificationId)) {
            $service->markNotificationsViewedByIds(array($notificationId));
            $seenSingle = true;
        } else {
            $service->markNotificationsViewedByUserId($userId);
        }
        return array("valid" => true, 'single' => $seenSingle, 'notificationId' => $notificationId, 'new_notifications_count' => $this->getNewNotificationsCount());
    }

    public function hideNotification(){
        if(!FRMSecurityProvider::checkPluginActive('notifications', true)){
            return array();
        }

        $notificationId = null;
        if (isset($_GET['notification_id'])) {
            $notificationId = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['notification_id']);
            $service = NOTIFICATIONS_BOL_Service::getInstance();
            $resp = $service->hideNotification($notificationId);
            return array("valid" => $resp, 'notificationId' => $notificationId);
        }

        return array();
    }

    private function getActionFromLikeId($likeId) {
        $like = NEWSFEED_BOL_LikeDao::getInstance()->findById($likeId);
        if ($like != null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($like->entityType, $like->entityId);
            return $action;
        }

        return null;
    }

    private function getActionFromCommentId($commentId){
        $commentEntity = $this->getCommentEntityByCommentId($commentId);
        if ($commentEntity != null && $commentEntity->pluginKey == 'newsfeed') {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($commentEntity->entityType, $commentEntity->entityId);
            return $action;
        }

        return null;
    }

    private function getNewsFromCommentId($commentId){
        $commentEntity = $this->getCommentEntityByCommentId($commentId);
        if ($commentEntity != null && $commentEntity->pluginKey == 'frmnews') {
            $news = EntryService::getInstance()->findById($commentEntity->entityId);
            return $news;
        }

        return null;
    }

    private function getBlogFromCommentId($commentId){
        $commentEntity = $this->getCommentEntityByCommentId($commentId);
        if ($commentEntity != null && $commentEntity->pluginKey == 'blogs') {
            $blog = PostService::getInstance()->findById($commentEntity->entityId);
            return $blog;
        }

        return null;
    }

    private function getPhotoFromCommentId($commentId){
        $commentEntity = $this->getCommentEntityByCommentId($commentId);
        if ($commentEntity != null && $commentEntity->pluginKey == 'photo') {
            $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($commentEntity->entityId);
            return $photo;
        }

        return null;
    }

    private function getVideoFromCommentId($commentId){
        $commentEntity = $this->getCommentEntityByCommentId($commentId);
        if ($commentEntity != null && $commentEntity->pluginKey == 'video') {
            $video = VIDEO_BOL_ClipService::getInstance()->findClipById($commentEntity->entityId);
            return $video;
        }

        return null;
    }

    private function getCommentEntityByCommentId($commentId){
        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        if ($comment != null) {
            $commentEntityId = $comment->commentEntityId;
            $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($commentEntityId);
            return $commentEntity;
        }

        return null;
    }

    public function preparedNotificationData($notification){
        $notificationData = $notification->getData();
        $objectEntityId = $notification->entityId;
        $groupId = null;
        if (isset($notificationData['groupId'])) {
            $groupId = (int) $notificationData['groupId'];
            $objectEntityId = (int) $groupId;
        }
        $data = NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->getEditedData($notification->pluginKey, $objectEntityId, $notification->entityType, $notificationData);
        $senderUserId = null;
        $senderAvatarUrl = null;
        $senderUserName = '';
        if(isset($data['avatar']['userId'])) {
            $senderUserId = $data['avatar']['userId'];
            $senderAvatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($senderUserId);
            $senderUserName = BOL_UserService::getInstance()->getDisplayName($senderUserId);
        }
        $realText = $this->getTextOfNotification($data);
        $string = $this->getTextOfNotification($data, $senderUserName, false);

        $entityType = $notification->entityType;
        $entityId = $notification->entityId;
        $pageId = '';
        $action = null;

        $page = "";
        if(in_array($notification->entityType, array('friends-accept', 'birthday', 'friendship'))){
            $page = "user";
            if ($notification->entityType == 'friends-accept' && isset($data['avatar']['userId'])){
                $pageId = (int) $data['avatar']['userId'];
            } else if ($notification->entityType == 'birthday') {
                $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $notification->entityId);
            }
        }else if(in_array($notification->entityType, array('event-invitation', 'event', 'event_invitation', 'event-add-file'))){
            $page = "event";
            $url = null;
            if (isset($data['string']['vars']['eventUrl'])){
                $url = $data['string']['vars']['eventUrl'];
            } else if (isset($data['string']['vars']['url'])) {
                $url = $data['string']['vars']['url'];
            }
            if ($url != null) {
                $pageId = substr($url, strpos($url, 'event/') + 6);
                $pageId = (int) $pageId;

            }
        }else if(in_array($notification->entityType, array('news-add_news', 'news-add_comment'))){
            $page = "news";
            if (isset($data['string']['vars']['url'])){
                $url = $data['string']['vars']['url'];
                $pageId = substr($url, strpos($url, 'entry/') + 6);
                $pageId = (int) $pageId;
            }
        }else if(in_array($notification->entityType, array('blogs-add_comment', 'blog-post', 'blogs-add_blog'))){
            $page = "blog";
            if (isset($data['string']['vars']['url'])){
                $url = $data['string']['vars']['url'];
                $pageId = substr($url, strpos($url, 'post/') + 5);
                $pageId = (int) $pageId;
            }
        }else if(in_array($notification->entityType, array('groups-join', 'groups', 'groups_wal', 'groups-add-file', 'user_invitation', 'groups-update-status', 'group_approve'))){
            $page = "group";
            if ($groupId != null) {
                $pageId = $groupId;
            } else if (isset($data['string']['vars']['groupUrl'])){
                $url = $data['string']['vars']['groupUrl'];
                $pageId = substr($url, strpos($url, 'groups/') + 7);
                $pageId = (int) $pageId;
            }
        }else if(in_array($notification->entityType, array('forum_topic_reply', 'group-topic-add'))){
            $page = "forum";
            if ($notification->entityType == 'group-topic-add') {
                $pageId = (int) $notification->entityId;
            }else if (isset($data['string']['vars']['topicUrl'])){
                $url = $data['string']['vars']['topicUrl'];
                $pageId = substr($url, strpos($url, 'topic/') + 6);
                $pageId = (int) $pageId;
            }
        }else if(in_array($notification->entityType, array('photo_comments', 'photo_like', 'photo-add_rate'))){
            $page = "photo";
            if ($notification->entityType == 'photo_comments') {
                $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($notification->entityType, $notification->entityId);
            }else if (in_array($notification->entityType, array('photo_like', 'photo-add_rate'))) {
                $pageId = (int) $entityId;
            }
        }else if(in_array($notification->entityType, array('frmpasswordchangeinterval'))){
            $page = "change_password";
        }else if(in_array($notification->entityType, array('questions-answer', 'questions-post'))){
            $page = "feed";
        }else if(in_array($notification->entityType, array('video_add_comment'))){
            $page = "video";
            //TODO: should be handle
        } else if(in_array($notification->entityType, array('user-edit-approve'))){
            $page = "user";
            $pageId = (int) $notification->entityId;
            $entityId = (int) $notification->entityId;
        }

        if (in_array($notification->entityType, array('frmlike-groups-status',
            'frmlike-user-status',
            'frmlike-blog-post',
            'frmlike-news-entry',
            'base_profile_wall',
            'photo-add_comment',
            'status_comment',
            'photo_add_comment',
            'video-add_comment',
            'news-add_comment'))) {
            $action = $this->getActionFromCommentId($notification->entityId);
            if($action == null) {
                $find = false;
                $news = $this->getNewsFromCommentId($notification->entityId);
                if ($news != null){
                    $find = true;
                    $page = "news";
                    $entityId = (int) $news->id;
                    $pageId = (int) $news->id;
                }

                if(!$find) {
                    $photo = $this->getPhotoFromCommentId($notification->entityId);
                    if ($photo != null) {
                        $find = true;
                        $page = "photo";
                        $entityId = (int)$photo->id;
                        $pageId = (int)$photo->id;
                    }
                }

                if(!$find) {
                    $video = $this->getVideoFromCommentId($notification->entityId);
                    if ($video != null) {
                        $find = true;
                        $page = "video";
                        $entityId = (int) $video->id;
                        $pageId = (int) $video->id;
                    }
                }

                if(!$find) {
                    $blog = $this->getBlogFromCommentId($notification->entityId);
                    if ($blog != null) {
                        $find = true;
                        $page = "blog";
                        $entityId = (int) $blog->id;
                        $pageId = (int) $blog->id;
                    }
                }
            }
        }else if (in_array($notification->entityType, array('status_like'))){
            $action = $this->getActionFromLikeId($notification->entityId);
        }else if ($notification->entityType == 'user_status') {
            $page = "feed";
            $entityType = 'user-status';
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $notification->entityId);
            if ($action != null){
                $pageId = (int) $notification->entityId;
            }

        }else if ($notification->entityType == 'user-status' && $notification->pluginKey == 'frmmention') {
            $page = "feed";
            $entityType = 'user-status';
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $notification->entityId);
            if ($action != null){
                $pageId = (int) $notification->entityId;
            }

        }else if ($notification->entityType == 'groups-status') {
            $hasNewsfeedInUrl = false;
            if (isset($data['url'])) {
                $hasNewsfeedInUrl = strpos($data['url'], 'newsfeed');
            }
            if ($notification->pluginKey == 'frmmention'){
                if ($hasNewsfeedInUrl === false) {
                    $page = "group";
                    $pageId = (int) $entityId;
                } else {
                    $page = "feed";
                    $entityType = 'groups-status';
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $notification->entityId);
                    if ($action != null){
                        $pageId = (int) $notification->entityId;
                    }
                }
            }
            else {
                $page = "feed";
                $entityType = 'groups-status';
                $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $notification->entityId);
                if ($action != null){
                    $pageId = (int) $notification->entityId;
                }
            }
        }

        if($action != null) {
            $page = "feed";
            $entityId = $action->entityId;
            $pageId = (int) $action->entityId;
            $entityType = $action->entityType;
        }

        $disable = false;
        if($entityType == 'user-status' && $action == null){
            $disable = true;
        }
        if (isset($data['disabled']) && $data['disabled']) {
            $disable = true;
        }

        if($page == "" && $pageId == ""){
            if($entityType == "photo_add_comment"){
                $url = $data['string']['vars']['photoUrl'];
                $photoId = substr($url, strpos($url, 'photo/view/') + 11);
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                if($photo != null){
                    $page = "photo";
                    $entityId = (int) $photo->id;
                    $pageId = (int) $photo->id;
                }
            }
            if($entityType == 'blogs'){
                $page = "blog";
            }
        }

        $string = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText($string);

        return array(
            'id' => (int) $notification->id,
            'pageId' => $pageId,
            'entityType' => $entityType,
            'entityId' => (int) $entityId,
            'plugin' => $notification->pluginKey,
            'timestamp' => $notification->timeStamp,
            'viewed' => $notification->viewed,
            'senderUserId' => $senderUserId,
            'senderUserName' => $senderUserName,
            'senderAvatarUrl' => $senderAvatarUrl,
            'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $senderUserId, $senderAvatarUrl),
            'string' => $string,
            'page' => $page,
            'disable' => $disable,
            'real_text' => $realText
        );
    }

    /***
     * @param $data
     * @param bool $real
     * @param $cleanString
     * @return string
     */
    public function getTextOfNotification($data, $cleanString = '', $real = true){
        if (!isset($data['string']) || empty($data['string']) || !is_array($data['string']) ){
            return "";
        }

        $string = "";
        if (isset($data['string']['key'])) {
            $key = explode('+', $data['string']['key']);
            if (!$real) {
                if (isset($data['string']['vars']['userName'])) {
                    $data['string']['vars']['userName'] = '';
                }
                if (isset($data['string']['vars']['actor'])) {
                    $data['string']['vars']['actor'] = '';
                }
                if (isset($data['string']['vars']['user'])) {
                    $data['string']['vars']['user'] = '';
                }
                if (isset($data['string']['vars']['receiver'])) {
                    $data['string']['vars']['receiver'] = '';
                }
            }
            $vars = empty($data['string']['vars']) ? array() : $data['string']['vars'];
            $string = OW::getLanguage()->text($key[0], $key[1], $vars);
            if (!empty($string)) {
                $string = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($string, true, true);
            }
        }
        if(!$real){
            $string = trim($string);
            if (substr($string, 0, strlen($cleanString)) == $cleanString){
                $string = substr($string, strlen($cleanString));
            }
            $string = trim($string);
        }
        return $string;
    }
}