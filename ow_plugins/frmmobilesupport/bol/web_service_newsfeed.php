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
class FRMMOBILESUPPORT_BOL_WebServiceNewsfeed
{
    private static $classInstance;
    const IMAGE_EXTENSIONS = array("jpg","jpeg","png","gif","bmp");
    const VIDEO_EXTENSIONS = array("mp4", "3gp", "avi","mov");
    const AUDIO_EXTENSIONS = array("mp3","aac","ogg","aac");
    const CHUNK_SIZE = 100000;

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

    public function userProfilePosts($userId, $first = 0, $count = 11){
        if($userId == null){
            return array();
        }

        if(!$this->canUserSeeFeed(OW::getUser()->getId(), $userId)){
            return array();
        }

        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        if(isset($_GET['count'])){
            $count = $_GET['count'];
        }

        $params = array(
            "feedType" => "user",
            "feedId" => $userId,
            "offset" => $first,
            "displayCount" => $count,
            "displayType" => "action",
            "checkMore" => true,
            "feedAutoId" => "feed1",
            "startTime" => time(),
            "formats" => null,
            "endTIme" => 0
        );
        return FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getActionData($params);
    }

    public function getPost(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = null;
        $entityId = null;
        if(isset($_GET['entityType'])){
            $entityType = $_GET['entityType'];
        }

        if(isset($_GET['entityId'])){
            $entityId = $_GET['entityId'];
        }

        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if(!$this->userCanSeeAction($entityType, $entityId)){
            return array('valid' => false, 'message' => 'authorization_error', 'entityId' => $entityId, 'entityType' => $entityType);
        }

        $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        if($action == null){
            return array('valid' => false, 'message' => 'input_error', 'entityId' => $entityId, 'entityType' => $entityType);
        }

        $data = $this->preparedActionData($action, array('comments'));
        if($data == null){
            return array('valid' => false, 'message' => 'input_error', 'entityId' => $entityId, 'entityType' => $entityType);
        }

        return $data;
    }

    public function getBusinessPosts(){

        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $start = 0;
        $count = 20;
        if(isset($_POST['start'])){
            $start = $_POST['start'];
        }

        if(isset($_POST['limit'])){
            $limit = $_POST['limit'];
        }

        $actions = NEWSFEED_BOL_ActionDao::getInstance()->findProductsFeed(array($start, $limit));
        if($actions == null){
            return array('valid' => false, 'message' => 'no_posts_found');
        }
        foreach ($actions as $action){
            $data[] = $this->preparedActionData($action, array('comments'));
        }
        if($data == null){
            return array('valid' => false, 'message' => 'no_data_for_posts');
        }

        return $data;

    }

    public function getDashboard(){
        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize() + 1;
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $params = array(
            "feedType" => "my",
            "feedId" => OW::getUser()->getId(),
            "offset" => $first,
            "displayCount" => $count,
            "displayType" => "action",
            "checkMore" => true,
            "feedAutoId" => "feed1",
            "startTime" => time(),
            "formats" => null,
            "endTIme" => 0
        );
        return FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getUserActionData($params);
    }

    public function like(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = null;
        $entityId = null;
        if(isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        if(isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }

        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if(!$this->userCanSeeAction($entityType, $entityId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();

        if (!isset($_POST['vote']) || ($_POST['vote'] != 1 && $_POST['vote'] != -1)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        NEWSFEED_BOL_Service::getInstance()->addLike($userId, $entityType, $entityId, $_POST['vote']);
        $likesArray = BOL_VoteService::getInstance()->findEntityLikes($entityType, $entityId);

        $likeInfo = array(
            'size' => sizeof($likesArray)
        );

        $feedId = '';
        $feedType = '';
        $feed = $this->findFeed($entityType, $entityId);
        if ($feed) {
            $feedId = (int) $feed->feedId;
            $feedType = $feed->feedType;
        }

        return array('valid' => true, 'message' => 'liked', 'info' => $likeInfo, "feedId" => $feedId, "feedType" => $feedType);
    }


    public function removeAction(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = null;
        $entityId = null;
        if(isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        if(isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }

        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);

        if(!$this->userCanSeeAction($entityType, $entityId, $action)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!$this->canRemoveFeedByAction($entityType, $entityId, null, $action)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $feedId = '';
        $feedType = '';
        $feed = $this->findFeed($entityType, $entityId, $action);
        if ($feed) {
            $feedId = (int) $feed->feedId;
            $feedType = $feed->feedType;
        }

        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array('entityType' => $entityType, 'entityId' => $entityId)));
        $data = array("entityId" => (int) $entityId, "entityType" => $entityType, "feedId" => $feedId, "feedType" => $feedType);
        return array('valid' => true, 'message' => 'removed', 'data' => $data);
    }

    public function removeActions(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityTypes = null;
        $entityIds = null;
        if(isset($_POST['entityTypes'])){
            $entityTypes = (array) json_decode( $_POST['entityTypes'] );
        }

        if(isset($_POST['entityIds'])){
            $entityIds = (array) json_decode( $_POST['entityIds'] );
        }

        if($entityIds == null || $entityTypes == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        foreach ($entityIds as $key => $entityId){
            $entityType = $entityTypes[$key];
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);

            if(!$this->userCanSeeAction($entityType, $entityId, $action)){
                return array('valid' => false, 'message' => 'authorization_error');
            }

            if(!$this->canRemoveFeedByAction($entityType, $entityId, null, $action)){
                return array('valid' => false, 'message' => 'authorization_error');
            }

            $feedId = '';
            $feedType = '';
            $feed = $this->findFeed($entityType, $entityId, $action);
            if ($feed) {
                $feedId = (int) $feed->feedId;
                $feedType = $feed->feedType;
            }

            OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array('entityType' => $entityType, 'entityId' => $entityId)));
            $data = array("entityId" => (int) $entityId, "entityType" => $entityType, "feedId" => $feedId, "feedType" => $feedType);
            $out[$key] = $data;
        }

        return array('valid' => true, 'message' => 'removed', 'results' => $out);
    }

    public function getCreatorActivityOfAction($entityType, $entityId, $action = null){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return null;
        }

        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        }
        if($action == null){
            return null;
        }
        $activitiesId = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($action->getId()));
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByIdList($activitiesId);

        foreach($activities as $activity){
            if($activity->activityType == 'create'){
                return $activity;
            }
        }
        return null;
    }

    public function findAllParticipatedUsersInAction($entityType, $entityId){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return null;
        }

        $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        if($action == null){
            return null;
        }
        $userIds = array();
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($action->getId()));
        foreach($activities as $activityId){
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if(!in_array($activity->userId, $userIds)){
                $userIds[] = $activity->userId;
            }
        }
        return $userIds;
    }

    public function getGroupId($entityType, $entityId, $creatorActivity = null){
        $activity = $creatorActivity;
        if ($activity == null) {
            $activity = $this->getCreatorActivityOfAction($entityType, $entityId);
        }
        if($activity == null){
            return false;
        }

        $feedFromActivity = null;
        if (isset($creatorActivity->feed_object)) {
            $feedFromActivity = $creatorActivity->feed_object;
            if($feedFromActivity->feedType=="groups"){
                return $feedFromActivity->feedId;
            }
        }
        if ($feedFromActivity == null) {
            $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id));
            $event = null;
            foreach ($feedIdFromActivities as $feedFromActivity){
                if($feedFromActivity->feedType=="groups"){
                    return $feedFromActivity->feedId;
                }
            }
        }
        return null;
    }

    public function findFeed($entityType, $entityId, $action = null, $creatorActivity = null){
        $activity = $creatorActivity;
        if ($activity == null) {
            $activity = $this->getCreatorActivityOfAction($entityType, $entityId, $action);
        }
        if($activity == null){
            return false;
        }
        $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id));
        $event = null;
        foreach ($feedIdFromActivities as $feedFromActivity){
            return $feedFromActivity;
        }
        return null;
    }

    public function canUserSendPostOnFeed($userId, $feedId){
        if(OW::getUser()->isAdmin()){
            return true;
        }
        return $this->checkFeedPrivacy($userId, $feedId, 'who_post_on_newsfeed');
    }

    public function getDefaultPrivacyOfUsersPosts($user){
        if($user == null){
            return '';
        }
        if(!FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            return '';
        }
        $text = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy('other_post_on_feed_newsfeed', $user->id);
        return $text;
    }

    public function canUserSeeFeed($userId, $feedId){
        if(OW::getUser()->isAdmin()){
            return true;
        }

        $blocked = BOL_UserService::getInstance()->isBlocked($userId, $feedId);
        if ($blocked) {
            return false;
        }
        return $this->checkFeedPrivacy($userId, $feedId, 'base_view_profile');
    }

    public function checkFeedPrivacy($userId, $feedId, $key){
        if($userId == null && OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->isAuthenticated();
        }
        if($feedId == null) {
            return false;
        }

        if($userId == $feedId){
            return true;
        }

        if(!FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            return true;
        }

        $profileOwnerPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy($key, $feedId);
        $profileOwnerPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->validatePrivacy($profileOwnerPrivacy);
        if(!OW::getUser()->isAuthenticated() && $profileOwnerPrivacy != 'everybody'){
            return false;
        }
        if ($profileOwnerPrivacy == 'friends_only') {
            $ownerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $feedId));
            if(!in_array($userId,$ownerFriendsId)){
                return false;
            }
        } else if ($profileOwnerPrivacy == 'only_for_me') {
            return false;
        }

        return true;
    }

    public function getUserProfileDefaultPrivacy($userId){
        if($userId == null){
            return 'only_for_me';
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)){
            $profileOwnerPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy('other_post_on_feed_newsfeed', $userId);
            $profileOwnerPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->validatePrivacy($profileOwnerPrivacy);
            return $profileOwnerPrivacy;
        }
        return 'only_for_me';
    }

    public function sendPost(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $generalService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
        $text = null;
        $feedType = null;
        $feedId = null;
        $privacy = null;
        $replyTo = null;
        $tags = null;
        $productHashtags = null;
        $products = null;

        if (isset($_POST['question_hidden']) && $_POST['question_hidden'] == 'false') {
            $_POST['question_id'] = FRMSecurityProvider::generateUniqueId();
        }

        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        if(isset($_POST['feedId']) && !empty($_POST['feedId'])){
            $feedId = $_POST['feedId'];
        }

        if(isset($_POST['feedType'])){
            $feedType = $_POST['feedType'];
        }

        if(isset($_POST['replyToEntityType']) && isset($_POST['replyToEntityId'])){
            $replyTo = array('$replyToEntityType'=>$_POST['replyToEntityType'],
                '$replyToEntityId'=>$_POST['replyToEntityId']);
        }


        $userId = OW::getUser()->getId();
        if($feedType === 'user' && ($feedId == null || empty($feedId) || $feedId == 'null')){
            $feedId = $userId;
        }

        if($feedType == null || $feedId == null){
            return array('valid' => false, 'message' => 'feedType_FeedId_Null');
        }

        if ($text == null) {
            $text = "";
        }

        if(!in_array($feedType, array('user', 'groups'))){
            return array('valid' => false, 'message' => 'feed_type_error');
        }

        if($feedType == 'user'){
            if(!$this->canUserSeeFeed($userId, $feedId) || !$this->canUserSendPostOnFeed($userId, $feedId)){
                return array('valid' => false, 'message' => 'user_unable_to_send_post');
            }
            $profileOwnerPrivacy = $this->getUserProfileDefaultPrivacy($feedId);
            if($feedId == $userId){
                if(isset($_POST['privacy'])){
                    if (FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                        $privacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->validatePrivacy($_POST['privacy']);
                        $_POST['privacy'] = $privacy;
                    }
                }else{
                    $privacy = $profileOwnerPrivacy;
                    $_POST['privacy'] = $profileOwnerPrivacy;
                }
            } else if($feedId != $userId){
                $privacy = $profileOwnerPrivacy;
                $_POST['privacy'] = $profileOwnerPrivacy;
            }
        }else{
            $privacy = 'everybody';
            $_POST['privacy'] = 'everybody';
        }

        $visibility = NEWSFEED_BOL_Service::VISIBILITY_FULL;

        if($feedType == 'groups') {
            if(!FRMSecurityProvider::checkPluginActive('groups', true)){
                return array('valid' => false, 'message' => 'plugin_not_found');
            }
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($feedId);
            if ( $group == null || !GROUPS_BOL_Service::getInstance()->isCurrentUserCanAddPost($group) )
            {
                return array('valid' => false, 'message' => 'current_user_unable_to_send_post');
            }
            $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
            $visibility = $private
                ? 14 // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
                : 15; // Visible for all (15)
        }

        $text = empty($text) ? '' : $generalService->stripString($text, false);

        /**
         * replace unicode emoji characters
         */
        $replaceUnicodeEmoji= new OW_Event('frm.replace.unicode.emoji', array('text' => $text));
        OW::getEventManager()->trigger($replaceUnicodeEmoji);
        if(isset($replaceUnicodeEmoji->getData()['correctedText'])) {
            $text = $replaceUnicodeEmoji->getData()['correctedText'];
        }

        /**
         * remove remaining utf8 unicode emoji characters
         */
        $removeUnicodeEmoji= new OW_Event('frm.remove.unicode.emoji', array('text' => $text));
        OW::getEventManager()->trigger($removeUnicodeEmoji);
        if(isset($removeUnicodeEmoji->getData()['correctedText'])) {
            $text = $removeUnicodeEmoji->getData()['correctedText'];
        }

        $text = UTIL_HtmlTag::autoLink($text);
        $attachId = null;
        $dtoObject = null;
        $content = array();
        $fileIndex = 0;
        $virusDetectedFiles = array();
        $attachmentList = null;

        if (isset($_POST['attachId'])) {
            $attachmentList = BOL_AttachmentDao::getInstance()->findAttahcmentByBundle('frmnewsfeedplus', $_POST['attachId']);
            if (!empty($attachmentList) && $attachmentList != null) {
                $attachId = $_POST['attachId'];
                foreach ($attachmentList as $attachmentItem) {
                    if ($attachmentItem->status == 1 || $attachmentItem->userId != OW::getUser()->getId()) {
                        return array('valid' => false, 'message' => 'input_file_error');
                    }
                }
            }
        }

        if (isset($_FILES) && $attachId == null) {
            if (isset($_FILES['file'])) {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                if ($isFileClean) {
                    $dtoObject = $this->manageNewsfeedAttachment($userId, $_FILES['file']);
                    if (isset($dtoObject) || $dtoObject != null) {
                        $attachId = $dtoObject['bundle'];
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file']['name'];
                }
            }
            while (isset($_FILES['file' . $fileIndex])) {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file' . $fileIndex]['tmp_name']);
                if ($isFileClean) {
                    $dtoObject = $this->manageNewsfeedAttachment($userId, $_FILES['file' . $fileIndex], $attachId, $fileIndex);
                    if (isset($dtoObject) || $dtoObject != null) {
                        $attachId = $dtoObject['bundle'];
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file' . $fileIndex]['name'];
                }
                $fileIndex++;
            }
        }

        if (isset($_POST['fileIds'])) {
            $fileIds = explode(',', $_POST['fileIds']);
            if (!empty($attachmentList) && $attachmentList != null) {
                $fileIndex = 1;
                foreach ($attachmentList as $attachmentItem) {
                    if ($attachmentItem->status == 0 && !in_array($attachmentItem->id, $fileIds)) {
                        BOL_AttachmentService::getInstance()->deleteAttachment(OW::getUser()->getId(), $attachmentItem->id);
                    } else {
                        if (!isset($_POST['attachment_feed_data'])) {
                            $_POST['attachment_feed_data'] = '';
                        }
                        $_POST['attachment_feed_data'] = $_POST['attachment_feed_data'] . $fileIndex . ':' . $attachmentItem->id . '-';
                        $fileIndex += 1;
                    }
                }
            }
        }

        if(isset($_POST['tags'])){
            $tags = $_POST['tags'];
        }

        if(isset($_POST['productHashtags'])){
            $productHashtags = json_decode( $_POST['productHashtags'] );
        }

        if(isset($_POST['products'])){
            $productsList = json_decode( $_POST['products'] );
            foreach ($productsList as $item){
                $item = (array) $item;
                if(!isset($item['positionX'])  || !isset($item['positionY'])  || !isset($item['productId']) ){
                    return array('valid' => false, 'message' => 'invalid_product_info');
                }
                $productNew[] = $item;
            }
            $products = json_encode( $productNew );
        }



        if($text == "" || $text == null){
            $newsfeedAttachmentEvents = OW::getEventManager()->trigger(new OW_Event('on.status.update.check.data'));
            if(!isset($newsfeedAttachmentEvents->getData()['hasData']) ||  $newsfeedAttachmentEvents->getData()['hasData'] == false) {
                return array('valid' => false, 'message' => 'empty_text');
            }
        }

        OW::getLogger()->writeLog(OW_Log::INFO, 'mobile_native_user_add_post', array('message' => 'before_post_added', 'token' => $_POST['access_token'], 'feedId' => $feedId, 'feedType' => $feedType, 'userId' => $userId, 'text' => $text));

        $out = NEWSFEED_BOL_Service::getInstance()
            ->addStatus($userId, $feedType, $feedId, $visibility, $text, array(
                "content" => $content,
                "attachmentId" => $attachId,
                "replyTo" => $replyTo,
                "tags" => $tags,
                "productHashtags" => json_encode($productHashtags),
                "products" => $products,
            ));

        if(!isset($out['entityType']) || !isset($out['entityId'])){
            return array('valid' => false, 'message' => 'error_save_data');
        }

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($out['entityType'], $out['entityId']);
        if ($action == null ){
            return array('valid' => false, 'message' => 'output_error');
        }
        if($privacy != null) {
            $this->updateActionPrivacy($action->getId(), $privacy, $action);
        }

        $result = $this->preparedActionsData(array($action));
        return array('valid' => true, 'message' => 'added', 'item' => $result, 'virus_files' => $virusDetectedFiles);
    }

    public function manageNewsfeedAttachment($userId, $file, $bundle = null, $index = 1){
        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);
        if ($bundle == null){
            $bundle = FRMSecurityProvider::generateUniqueId();
        }
        $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
        $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);

        try{
            $attUpload = BOL_AttachmentService::getInstance()->processUploadedFile('newsfeed', $file, $bundle, $validFileExtensions, $maxUploadSize);
        } catch (Exception $e){
            return array('bundle' => null, 'dto' => null);
        }
        $attachmentId = $attUpload['dto']->id;
        $attachment = BOL_AttachmentDao::getInstance()->findById((int)$attachmentId);
        $attachmentPath = BOL_AttachmentService::getInstance()->getAttachmentsDir(). $attachment->fileName;
        $fileExt = UTIL_File::getExtension($attachment->fileName);
        $newAttachmentFileName =$attachment->origFileName;
        $item = array();
        $item['name'] = $newAttachmentFileName;
        $item['type'] = 'image/'.$fileExt;
        $item['error'] = 0;
        $item['size'] = UTIL_File::getFileSize($attachmentPath,false);
        $pluginKey = 'frmnewsfeedplus';
        $tempFileId = BOL_FileTemporaryService::getInstance()->addTemporaryFile($attachmentPath,$newAttachmentFileName,$userId);
        $item['tmp_name']=BOL_FileTemporaryService::getInstance()->getTemporaryFilePath($tempFileId);
        $dtoArr =BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $bundle);

        if (!isset($_POST['attachment_feed_data'])) {
            $_POST['attachment_feed_data'] = '';
        }
        $_POST['attachment_feed_data'] = $_POST['attachment_feed_data'] . $index . ':' . $dtoArr['dto']->id . '-';
        $preview = false;
        if(isset($_POST['preview']) && $_POST['preview'] == 'true'){
            $preview = true;
        } else if(isset($_POST['preview'.$index]) && $_POST['preview'.$index] == 'true'){
            $preview = true;
        }
        if ($preview) {
            if (!isset($_POST['attachment_preview_data'])) {
                $_POST['attachment_preview_data'] = '';
            }
            $_POST['attachment_preview_data'] = $_POST['attachment_preview_data'] . $dtoArr['dto']->id . '-';
        }
        return array('bundle' => $bundle, 'dto' => $dtoArr);
    }

    public function updateActionPrivacy($actionId, $privacy, $action = null){
        if($actionId == null){
            return;
        }
        $activitiesId = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByIdList($activitiesId);
        foreach ($activities as $activity) {
            $activity->privacy = $privacy;
            NEWSFEED_BOL_Service::getInstance()->saveActivity($activity, $action);
        }
    }

    public function editPost(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = null;
        $entityId = null;
        $text = '';
        if(isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        if(isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }

        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        if($text == '' || $entityId == null || $entityType == null){
            if(!(isset($_POST['tags']) || isset($_POST['products']))){
                return array('valid' => false, 'message' => 'input_error');
            }
        }

        $result = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->editPost($text, $entityId, $entityType);
        if(isset($result['actionId']) && $result['actionId'] != -1){
            $feedId = '';
            $feedType = '';
            $feed = $this->findFeed($entityType, $entityId);
            if ($feed) {
                $feedId = (int) $feed->feedId;
                $feedType = $feed->feedType;
            }

            if (isset($result['text'])) {
                $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($result['text'], false);
                $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText($text);
            }

            return array('valid' => true, 'message' => 'post_edited', 'feedId' => $feedId, 'feedType' => $feedType, 'text' => $text);
        }
        return array('valid' => false, 'message' => 'authorization_error');
    }

    public function removeLike(){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = null;
        $entityId = null;
        if(isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        if(isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }

        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);

        if(!$this->userCanSeeAction($entityType, $entityId, $action)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        BOL_VoteService::getInstance()->removeVote($userId, $entityType, $entityId);

        $event = new OW_Event('feed.after_like_removed', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        $feedId = '';
        $feedType = '';
        $feed = $this->findFeed($entityType, $entityId, $action);
        if ($feed) {
            $feedId = (int) $feed->feedId;
            $feedType = $feed->feedType;
        }

        return array('valid' => true, 'message' => 'removed', "feedId" => $feedId, "feedType" => $feedType);
    }

    public function userCanSeeAction($entityType, $entityId, $action = null){
        if(!OW::getUser()->isAuthenticated()){
            return false;
        }

        if($entityId == null || $entityType == null){
            return false;
        }

        if ($action == null) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
        }
        if($action == null){
            return false;
        }

        $isModerator = false;
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized($action->pluginKey)) {
            $isModerator = true;
        }
        try{
            if(!$isModerator && !OW::getUser()->isAdmin()){
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('action' => $action,'actionId' => $action->getId())));
            }
        }catch (Exception $e){
            return false;
        }

        return true;
    }

    public function getUserActionData($params){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array();
        }
        $endTime = null;
        if(isset($params['endTime'])){
            $endTime = $params['endTime'];
        }
        $driver = new NEWSFEED_CLASS_FeedDriver();
        $actionList = NEWSFEED_BOL_ActionDao::getInstance()->findByUser($params['feedId'], array($params['offset'], $params['displayCount'], $params['checkMore']), $params['startTime'], $params['formats'], $driver, $endTime);
        return $this->preparedActionsData($actionList);
    }

    public function getActionData($params){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array();
        }

        $driver = new NEWSFEED_CLASS_FeedDriver();
        $endTime = null;
        if(isset($params['endTime'])){
            $endTime = $params['endTime'];
        }
        $additionalInfo = array();
        if(isset($params['additionalInfo'])){
            $additionalInfo = $params['additionalInfo'];
        }
        $actionList = NEWSFEED_BOL_ActionDao::getInstance()->findByFeed($params['feedType'], $params['feedId'], array($params['offset'], $params['displayCount'], $params['checkMore']), $params['startTime'], $params['formats'], $driver, $endTime, $additionalInfo);

        if (isset($params['additionalInfo']['doPrepareActions']) && sizeof($params['additionalInfo']['doPrepareActions']) > 0) {
            foreach ($params['additionalInfo']['doPrepareActions'] as $addAct) {
                $findAction = false;
                foreach ($actionList as $action) {
                    if ($action->id == $addAct->id) {
                        $findAction = true;
                    }
                }
                if (!$findAction) {
                    $actionList[] = $addAct;
                }
            }
        }
        return $this->preparedActionsData($actionList, $additionalInfo);
    }

    public function getSiteActionData($first, $count){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array();
        }
        $driver = new NEWSFEED_CLASS_SiteDriver();
        $actionList = NEWSFEED_BOL_ActionDao::getInstance()->findSiteFeed(array($first, $count, false), time(), null, $driver, null);
        return $this->preparedActionsData($actionList);
    }

    public function findOrderedListByIdList($idList)
    {
        if (empty($idList)) {
            return array();
        }

        $unsortedDtoList = NEWSFEED_BOL_ActionDao::getInstance()->findByIdList($idList);
        $unsortedList = array();
        foreach ($unsortedDtoList as $dto) {
            $unsortedList[$dto->id] = $dto;
        }

        $sortedList = array();
        foreach ($idList as $id) {
            if (!empty($unsortedList[$id])) {
                $sortedList[] = $unsortedList[$id];
            }
        }

        return $sortedList;
    }


    public function preparedActionsData($actionList = array(), $additionalInfo = array()){
        $data = array();

        $userIds = array();
        $replyActionIds = array();
        $actionIds = array();
        $activityIds = array();
        $params = array();
        $params['actionsInfo'] = array();
        $params['additionalInfo'] = $additionalInfo;
        $entityTypeList = array();
        $entityIdList = array();
        $groupIds = array();
        $questionIds = array();
        $attachmentIdList = array();
        $usernameList = array();
        $groupsCacheInfo = array();
        $groupsChannelCacheInfo = array();
        $cachedActionActivities = array();
        $groupsFileIds = array();
        $eventFileIds = array();
        $forumTopicIds = array();

        foreach ($actionList as $action){
            $actionIds[] = $action->id;
        }

        $activitiesId = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds($actionIds);
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByIds($activitiesId);

        $creatorActivities = array();
        foreach($activities as $activity){
            if($activity->activityType == 'create'){
                $creatorActivities[$activity->actionId] = $activity;
            }
            if (!isset($cachedActionActivities[$activity->actionId])) {
                $cachedActionActivities[$activity->actionId] = array();
            }
            $cachedActionActivities[$activity->actionId][] = $activity;
            $activityIds[] = $activity->id;
        }

        $params['cache']['activities'] = $cachedActionActivities;

        $cachedFeedFromCreatorActivity = array();
        $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds($activityIds);
        foreach ($feedIdFromActivities as $feedFromActivity){
            foreach ($creatorActivities as $key => $value){
                if ($creatorActivities[$key]->id == $feedFromActivity->activityId) {
                    $creatorActivities[$key]->feed_object = $feedFromActivity;
                }
            }
            $cachedFeedFromCreatorActivity[$feedFromActivity->activityId] = $feedFromActivity;
        }

        $params['cache']['feed_by_creator_activity'] = $cachedFeedFromCreatorActivity;

        foreach ($actionList as $action){
            $actionPreparedInfo = $this->preparedActionDataInfo($action, $creatorActivities);
            if (!isset($params['actionsInfo'][$action->id])) {
                $params['actionsInfo'][$action->id] = array();
            }
            $params['actionsInfo'][$action->id]['creatorActivity'] = $actionPreparedInfo['creatorActivity'];
            if (!in_array($actionPreparedInfo['userId'], $userIds)) {
                $userIds[] = $actionPreparedInfo['userId'];
            }
            if ($actionPreparedInfo['replyActionId'] != null && !in_array($actionPreparedInfo['replyActionId'], $replyActionIds)) {
                $replyActionIds[] = $actionPreparedInfo['replyActionId'];
            }

            $actionDataJson = array();
            if(isset($action->data)){
                $actionDataJson = $action->data;
            }

            if($actionDataJson != null){
                $actionDataJson = (array) json_decode($actionDataJson);
            }

            if (isset($actionDataJson['question_id'])) {
                $questionIds[] = $actionDataJson['question_id'];
            }
            if (isset($actionDataJson['attachmentIdList'])) {
                $attachmentIdList = array_merge($actionDataJson['attachmentIdList'], $attachmentIdList);
            }
            $entityTypeList[] = $action->entityType;
            $entityIdList[] = $action->entityId;

            if ($action->entityType == 'groups-add-file') {
                $groupsFileIds[] = $action->entityId;
            }
            if ($action->entityType == 'event-add-file') {
                $eventFileIds[] = $action->entityId;
            }
            if ($action->entityType == 'forum-topic') {
                $forumTopicIds[] = $action->entityId;
            }

            if (isset($actionDataJson['contextFeedType']) && isset($actionDataJson['contextFeedId'])) {
                $feedType = $actionDataJson['contextFeedType'];
                $feedId = $actionDataJson['contextFeedId'];
                if ($feedType == 'groups') {
                    if (!in_array($feedId, $groupIds)) {
                        $groupIds[] = $feedId;
                    }
                }
            }
            if (isset($actionDataJson['status']) && FRMSecurityProvider::checkPluginActive('frmmention', true)) {
                $mentionService = FRMMENTION_BOL_Service::getInstance();
                $localUsernameList = $mentionService->findUsernamesFromView($actionDataJson['status']);
                $usernameList = array_merge($localUsernameList, $usernameList);
            }
        }

        if (FRMSecurityProvider::checkPluginActive('groups', true) && !empty($groupIds)) {
            $groups = GROUPS_BOL_GroupDao::getInstance()->findByIdList($groupIds);
            foreach ($groups as $group) {
                $groupsCacheInfo[$group->id] = $group;
            }
        }
        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) && !empty($groupIds)) {
            $groupsChannelIds = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->findChannelIds($groupIds);
            foreach ($groupIds as $groupId) {
                $channel = false;
                if (in_array($groupId, $groupsChannelIds)) {
                    $channel = true;
                }
                $groupsChannelCacheInfo[$groupId] = $channel;
            }
            $groupsManagersCacheInfo = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds($groupIds);
            $params['cache']['groups_managers'] = $groupsManagersCacheInfo;
        }

        $entityTypeList = array_unique($entityTypeList);
        $entityIdList = array_unique($entityIdList);

        $cachedPinedActions = array();
        if (FRMSecurityProvider::checkPluginActive('frmnewsfeedpin', true)) {
            $pinList = FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->findByEntityIdsAndEntityTypes($entityIdList, $entityTypeList);
            foreach ($pinList as $pin) {
                $cachedPinedActions[$pin->entityType . '-' . $pin->entityId] = true;
            }
            $params['cache']['pinned_actions'] = $cachedPinedActions;
        }

        if (FRMSecurityProvider::checkPluginActive('frmquestions', true)) {
            $cachedQuestionsInfo = FRMQUESTIONS_BOL_Service::getInstance()->findOptionsAnswersListByQuestionIds($questionIds);
            $params['cache']['questions'] = $cachedQuestionsInfo;
        }

        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) && !empty($groupsFileIds)) {
            $groupFiles = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findByIdList($groupsFileIds);
            $groupFilesData = array();

            foreach ($groupFiles as $groupFile) {
                $attachmentIdList[] = $groupFile->attachmentId;
                $groupFilesData[$groupFile->id] = $groupFile;
            }

            $params['cache']['group_files'] = $groupFilesData;
        }


        if (FRMSecurityProvider::checkPluginActive('frmeventplus', true) && !empty($eventFileIds)) {
            $eventFiles = FRMEVENTPLUS_BOL_EventFilesDao::getInstance()->findByIdList($eventFileIds);
            $eventFilesData = array();
            foreach ($eventFiles as $eventFile) {
                $attachmentIdList[] = $eventFile->attachmentId;
                $eventFilesData[$eventFile->id] = $eventFile;
            }

            $params['cache']['event_files'] = $eventFilesData;
        }

        if (FRMSecurityProvider::checkPluginActive('forum', true) && !empty($forumTopicIds)) {
            $params['cache']['topics_posts'] = FORUM_BOL_PostDao::getInstance()->findTopicsPostByIds($forumTopicIds);

            $topics = FORUM_BOL_TopicDao::getInstance()->findByIdList($forumTopicIds);
            $topicsData = array();
            $topicsGroupId = array();
            foreach ($topics as $topic) {
                $topicsData[$topic->id] = $topic;
                $topicsGroupId[] = $topic->groupId;
            }
            $params['cache']['topics'] = $topicsData;

            if (sizeof($topicsGroupId) > 0) {
                $topicGroups = FORUM_BOL_GroupDao::getInstance()->findByIdList($topicsGroupId);
                $topicGroupsData = array();
                $topicSectionIds = array();
                foreach ($topicGroups as $topicGroup) {
                    $topicGroupsData[$topicGroup->id] = $topicGroup;
                    $topicSectionIds[] = $topicGroup->sectionId;
                }
                $params['cache']['topic_groups'] = $topicGroupsData;

                if (sizeof($topicSectionIds) > 0) {
                    $topicSections = FORUM_BOL_SectionDao::getInstance()->findByIdList($topicSectionIds);
                    $topicSectionsData = array();
                    foreach ($topicSections as $topicSection) {
                        $topicSectionsData[$topicSection->id] = $topicSection;
                    }
                    $params['cache']['topic_sections'] = $topicSectionsData;
                }
            }
        }

        $params['cache']['groups'] = $groupsCacheInfo;
        $params['cache']['groups_channel'] = $groupsChannelCacheInfo;

        $cachedActions = NEWSFEED_BOL_Service::getInstance()->findActionByIds($actionIds);
        $params['cache']['actions'] = $cachedActions;

        $actionsByEntity = array();
        foreach ($cachedActions as $cachedAction) {
            $actionsByEntity[$cachedAction->entityType . '-' . $cachedAction->entityId] = $cachedAction;
        }
        $params['cache']['actions_by_entity'] = $actionsByEntity;

        $userIdsByUsernameList = BOL_UserDao::getInstance()->findIdsByUserNames($usernameList);
        $userIds = array_merge($userIdsByUsernameList, $userIds);
        if (OW::getUser()->isAuthenticated()) {
            $userIds[] = OW::getUser()->getId();
        }
        $userIds = array_unique($userIds);
        $userIds = array_values($userIds);
        $params['usersIdList'] = $userIds;

        $params['usersInfo'] = array(
            'avatars' => array(),
            'urls' => array(),
            'names' => array(),
            'roleLabels' => array()
        );
        $params['usersInfo']['username'] = array();
        $cachedUserByUsername = array();

        if ( !empty($userIds) )
        {
            $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

            foreach ( $usersInfo as $uid => $userInfo )
            {
                $params['usersInfo']['avatars'][$uid] = $userInfo['src'];
                $params['usersInfo']['urls'][$uid] = $userInfo['url'];
                $cachedUserByUsername[$userInfo['urlInfo']['vars']['username']] = $userInfo['title'];
                $params['usersInfo']['names'][$uid] = $userInfo['title'];
                $params['usersInfo']['roleLabels'][$uid] = array(
                    'label' => $userInfo['label'],
                    'labelColor' => $userInfo['labelColor']
                );
            }
        }
        $params['cache']['username'] = $cachedUserByUsername;
        $usersCacheInfoById = array();
        $usersCacheInfoByUsername = array();
        $userList = BOL_UserDao::getInstance()->findByIdList($userIds);
        foreach ($userList as $user) {
            $usersCacheInfoById[$user->id] = $user;
            $usersCacheInfoByUsername[$user->username] = $user;
        }
        $params['cache']['users']['id'] = $usersCacheInfoById;
        $params['cache']['users']['username'] = $usersCacheInfoByUsername;

        if (FRMSecurityProvider::checkPluginActive('groups', true)) {
            $cachedUsersGroups = array();
            $usersRegisteredGroups = GROUPS_BOL_GroupUserDao::getInstance()->findGroupsByUserIds($userIds);
            foreach ($usersRegisteredGroups as $usersRegisteredGroup) {
                if (!isset($cachedUsersGroups[$usersRegisteredGroup->userId]) || !in_array($usersRegisteredGroup->groupId, $cachedUsersGroups[$usersRegisteredGroup->userId])) {
                    $cachedUsersGroups[$usersRegisteredGroup->userId][$usersRegisteredGroup->groupId] = $usersRegisteredGroup->groupId;
                }
            }
            $params['cache']['users_groups'] = $cachedUsersGroups;
        }

        if (FRMSecurityProvider::checkPluginActive('friends', true) && !empty($userIds)) {
            $params['cache']['friendships'] = FRIENDS_BOL_Service::getInstance()->findFriendships($userIds, OW::getUser()->getId());
        }

        $attachmentsList = array();
        $attachmentIdList =  array_unique($attachmentIdList);
        if (!empty($attachmentIdList)) {
            $attachmentsList = BOL_AttachmentDao::getInstance()->findByIdList($attachmentIdList);
        }
        $attachmentDir = BOL_AttachmentService::getInstance()->getAttachmentsDir();
        $cachedAttachmentsList = array();
        $keyFiles = array();
        $secureFilePluginActive = OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('frmsecurefileurl', true);
        foreach ($attachmentsList as $attachment) {
            $cachedAttachmentsList[$attachment->id] = $attachment;
            $filePathDir = $attachmentDir . $attachment->fileName;
            $filePath = OW::getStorage()->prepareFileUrlByPath($filePathDir);
            if ($secureFilePluginActive) {
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($filePath);
                $keyFiles[] = $keyInfo['key'];

                $thumbnailPath = UTIL_File::getCustomPath($filePathDir, 'userfiles-base-attachments-' . $attachment->fileName, 100, 100, 'min');
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($thumbnailPath);
                $keyFiles[] = $keyInfo['key'];

                $previewPath = UTIL_File::getCustomPath($filePathDir, 'userfiles-base-attachments-' . $attachment->fileName, 600, 600, 'min');
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($previewPath);
                $keyFiles[] = $keyInfo['key'];
            }
        }
        $params['cache']['attachments'] = $cachedAttachmentsList;

        $cachedSecureFileKeyList = array();
        if ($secureFilePluginActive && sizeof($keyFiles) > 0) {
            $keyList = FRMSECUREFILEURL_BOL_Service::getInstance()->existUrlByKeyList($keyFiles);
            foreach ($keyList as $urlObject) {
                $cachedSecureFileKeyList[$urlObject->key] = $urlObject;
            }
            foreach ($keyFiles as $key) {
                if (!array_key_exists($key, $cachedSecureFileKeyList)) {
                    $cachedSecureFileKeyList[$key] = null;
                }
            }
            $params['cache']['secure_files'] = $cachedSecureFileKeyList;
        }


        $params['preparedUsersData'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($userIds, true);
        $params['replyActions'] = NEWSFEED_BOL_Service::getInstance()->findActionByIds($replyActionIds);

        $cachedLikedEntities = array();
        $cachedDislikedEntities = array();
        $likesInfo = NEWSFEED_BOL_LikeDao::getInstance()->findByEntities($entityTypeList, $entityIdList);
        /** @var BOL_Vote $likeInfo */
        foreach ($likesInfo as $likeInfo) {
            if($likeInfo->vote == 1) {
            if (!isset($cachedLikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId])) {
                $cachedLikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId] = array();
            }
            $cachedLikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId][] = $likeInfo->userId;
            } else if ($likeInfo->vote == -1) {
                if (!isset($cachedDislikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId])) {
                    $cachedDislikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId] = array();
                }
                $cachedDislikedEntities[$likeInfo->entityType . '-' . $likeInfo->entityId][] = $likeInfo->userId;
            }
        }
        foreach ($entityTypeList as $entityType) {
            foreach ($entityIdList as $entityId) {
                if (!isset($cachedLikedEntities[$entityType . '-' . $entityId])) {
                    $cachedLikedEntities[$entityType . '-' . $entityId] = array();
                }
                if (!isset($cachedDislikedEntities[$entityType . '-' . $entityId])) {
                    $cachedDislikedEntities[$entityType . '-' . $entityId] = array();
                }
            }
        }
        $params['cache']['like_entities'] = $cachedLikedEntities;
        $params['cache']['dislike_entities'] = $cachedDislikedEntities;


        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) && !empty($groupIds)) {
            $thumbnailCacheInfo = array();
            $thumbnails = FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->getThumbnailsByAttachmentIds($attachmentIdList);
            foreach ($thumbnails as $thumbnail) {
                $thumbnailCacheInfo[$thumbnail->attachmentId] = $thumbnail;
            }
            foreach ($attachmentIdList as $attachmentId) {
                if (!isset($thumbnailCacheInfo[$attachmentId])) {
                    $thumbnailCacheInfo[$attachmentId] = null;
                }
            }
            $params['cache']['thumbnail_attachment'] = $thumbnailCacheInfo;
        }

        $commentsCounts = BOL_CommentDao::getInstance()->findCommentsCounts($entityTypeList, $entityIdList);
        $cachedCommentsCounts = array();
        foreach ($commentsCounts as $commentsCount) {
            $cachedCommentsCounts[$commentsCount['entityType'].'-'.$commentsCount['entityId']]  = (int) $commentsCount['count'];
        }
        foreach ($entityTypeList as $entityType) {
            foreach ($entityIdList as $entityId) {
                if (!isset($cachedCommentsCounts[$entityType . '-' . $entityId])) {
                    $cachedCommentsCounts[$entityType . '-' . $entityId] = 0;
                }
            }
        }
        $params['cache']['comments_count'] = $cachedCommentsCounts;


        $blockedUsers = BOL_UserService::getInstance()->findBlockedListByUserIdList(OW::getUser()->getId(), $userIds);
        $blockedByUsers = BOL_UserService::getInstance()->findBlockedByListByUserIdList(OW::getUser()->getId(), $userIds);

        $params['cache']['blockedUsers'] = $blockedUsers;
        $params['cache']['blockedByUsers'] = $blockedByUsers;

        foreach ($actionList as $action){
            $actionData = $this->preparedActionData($action, $params);
            if($actionData != null){
                $data[] = $actionData;
            }
        }

        return $data;
    }

    private function preparedActionData($action, $params = array()){
        $data = array();
        $generalService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
        $actionDataJson = null;
        $images = array();
        $sounds = array();
        $videos = array();
        $album = array();
        $entityTitle = "";
        $questionData = array();
        $onLocationTitle = "";
        $text = "";
        $forwardString = null;
        $forwardEntityType = null;
        $forwardEntityId = null;
        $entityImage = null;
        $time = "";
        $activityString = "";
        $userId = null;
        $objectId = null;
        $lastActivity = null;
        $entityDescription = null;
        $privacy = null;
        $forumGroupId = null;
        $privacyEditable = false;
        $groupId=null;
        $groupEntity = null;
        $replyToEntityType = null;
        $replyToEntityId = null;
        $products = null;

        if(isset($action->data)){
            $actionDataJson = $action->data;
        }

        if($actionDataJson != null){
            $actionDataJson = json_decode($actionDataJson);
        }

        if($actionDataJson != null){
            if (isset($params['actionsInfo'][$action->id]['creatorActivity'])) {
                $creatorActivity = $params['actionsInfo'][$action->id]['creatorActivity'];
            } else {
                $creatorActivity = $this->getCreatorActivityOfAction($action->entityType, $action->entityId, $action);
            }
            $feedObject = null;
            if (isset($creatorActivity->feed_object)) {
                $feedObject = $creatorActivity->feed_object;
            }
            if ($feedObject == null) {
                if (isset($params['cache']['feed_by_creator_activity'])) {
                    if (isset($params['cache']['feed_by_creator_activity'][$creatorActivity->id])) {
                        $feedObject = $params['cache']['feed_by_creator_activity'][$creatorActivity->id];
                    }
                } else {
                    $feedObject = $this->findFeed($action->entityType, $action->entityId, $action, $creatorActivity);
                }
            }
            if(isset($actionDataJson->ownerId)){
                $userId = $actionDataJson->ownerId;
            }

            if(isset($actionDataJson->products)){
                $products = json_decode($actionDataJson->products);
            }

            if(isset($actionDataJson->data->userId)){
                $userId = $actionDataJson->data->userId;
            }

            if(isset($actionDataJson->string)) {
                if(!isset($actionDataJson->string->key)){
                    $activityString = $generalService->stripString($actionDataJson->string, false, true);
                }else {
                    $keys = explode('+', $actionDataJson->string->key);
                    $varsArray = array();
                    $vars = empty($actionDataJson->string->vars) ? array() : $actionDataJson->string->vars;
                    foreach ($vars as $key => $var) {
                        $varsArray[$key] = $var;
                    }
                    $string = OW::getLanguage()->text($keys[0], $keys[1], $varsArray);
                    if (!empty($string)) {
                        $activityString = $generalService->stripString($string, false, true);
                    }
                }
            }

            if($action->format == "image_content"){
                // This is prefetch image
                if(false && isset($actionDataJson->content->vars->image)) {
                    $images[] = array(
                        "url" => FRMSecurityProvider::getInstance()->correctHomeUrlVariable($actionDataJson->content->vars->image),
                    );
                }

                if(isset($actionDataJson->status)){
                    $text = $generalService->stripString($actionDataJson->status, false);
                }
            }else if($action->format == "text" || $action->format == "content"){
                if(isset($actionDataJson->status)) {
                    $text = $generalService->stripString($actionDataJson->status, false);
                }
                if(isset($actionDataJson->data->userId)){
                    $userId = $actionDataJson->data->userId;
                }
            }else if($action->format == "image" &&
                $action->entityType == "photo_comments" &&
                isset($actionDataJson->content->format) &&
                $actionDataJson->content->format == 'image' &&
                isset($actionDataJson->content->vars->url->routeName) &&
                $actionDataJson->content->vars->url->routeName == 'view_photo' &&
                isset($actionDataJson->content->vars->url->vars->id)){

                $photoId = $actionDataJson->content->vars->url->vars->id;
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                if ($photo != null) {
                    $albumId = $photo->albumId;
                    $albumObj = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId);
                    if ($albumObj != null) {
                        $albumLabel = $albumObj->name;
                        $album = array("label" => $albumLabel, "id" => $albumId);
                        $userId = $albumObj->userId;
                    }
                    $url = PHOTO_BOL_PhotoService::getInstance()->getPhotoFullsizeUrl($photoId, $photo->hash);
                    $image = array(
                        "url" => FRMSecurityProvider::getInstance()->correctHomeUrlVariable($url),
                    );

                    $images[] = $image;
                }
            }
            if(isset($actionDataJson->replyTo->{'$replyToEntityId'}) && isset($actionDataJson->replyTo->{'$replyToEntityType'})){
                $replyToEntityId = (int)$actionDataJson->replyTo->{'$replyToEntityId'};
                $replyToEntityType = $actionDataJson->replyTo->{'$replyToEntityType'};
            }
            if(isset($actionDataJson->photoIdList)){
                if(isset($actionDataJson->content->vars->status)) {
                    $text = $generalService->stripString($actionDataJson->content->vars->status, false);
                }
                $photoIdList = $actionDataJson->photoIdList;
                $albumId = null;
                $images = array();
                foreach ($photoIdList as $photoId){
                    $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                    if ($photo != null) {

                        if($albumId == null) {
                            $albumId = $photo->albumId;
                        }
                        $url = PHOTO_BOL_PhotoService::getInstance()->getPhotoFullsizeUrl($photoId, $photo->hash);
                        $image = array(
                            "url" => FRMSecurityProvider::getInstance()->correctHomeUrlVariable($url),
                        );
                        $images[] = $image;
                    }
                }

                if($albumId == null && isset($actionDataJson->content->vars->info->route->vars->album)){
                    $albumId = $actionDataJson->content->vars->info->route->vars->album;
                }

                $albumLabel = "";
                if(isset($actionDataJson->content->vars->info->route->label)){
                    $albumLabel = $actionDataJson->content->vars->info->route->label;
                }

                if($albumId != null && $albumLabel == ""){
                    $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId);
                    if($album != null){
                        $albumLabel = $album->name;
                    }
                }
                $album = array("label" => $albumLabel, "id" => $albumId);
            }

            $files = array();
            $videoThumbnailUrl = null;
            $videoUrl = null;
            $videoIframe = false;

            if(in_array($action->entityType, array("groups-join", "groups-leave", "groups-status", "groups-add-file"))) {
                $groupId = $this->getGroupId($action->entityType, $action->entityId, $creatorActivity);
                if($groupId != null){
                    $groupEntity = null;
                    if (isset($params['additionalInfo']['group_object'])) {
                        $groupEntity = $params['additionalInfo']['group_object'];
                    }
                    if (isset($params['cache']['groups'][$groupId])) {
                        $groupEntity = $params['cache']['groups'][$groupId];
                    }
                    if ($groupEntity == null) {
                        $groupEntity = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                    }
                }
                if($groupEntity != null){
                    $entityTitle = $groupEntity->title;
                    $onLocationTitle = $groupEntity->title;
                    $time = $groupEntity->timeStamp;
                    $objectId = $groupEntity->id;
                    if(in_array($action->entityType, array("groups-join", "groups-leave")) && (empty($text) || $text == "")){
                        $text = $activityString;
                    }
                }
            }

            if(FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)){
                $canEditPost = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->canEditPost($action->entityId, $action->entityType, $action, $creatorActivity);
                $data['editable'] = $canEditPost;
            }

            if( isset($actionDataJson->question_id) &&
                FRMSecurityProvider::checkPluginActive('frmquestions', true)) {
                $questionId = $actionDataJson->question_id;
                $question = null;
                if (isset($params['cache']['questions'][$questionId])) {
                    $question = $params['cache']['questions'][$questionId]['question'];
                }
                if ($question == null) {
                    $question= FRMQUESTIONS_BOL_Service::getInstance()->findQuestion($questionId);
                }
                if ($question != null) {
                    $multipleAnswer = $question->isMultiple != 0;
                    $allowAddOption = $question->addOption;
                    $questionData = array(
                        'privacy' => $question->privacy,
                        'multiple' => $multipleAnswer,
                        'allowAddOptions' => $allowAddOption,
                        'id' => (int) $questionId,
                    );
                    $answeredOneOptions=false;
                    $isManager = false;
                    $checkManager = true;
                    if (isset($params['additionalInfo']['isManager'])) {
                        $checkManager = false;
                        $isManager = $params['additionalInfo']['isManager'];
                    }
                    if ($question != null && $question->context == 'groups' && isset($params['cache']['groups_managers']) && isset($params['cache']['groups'][$question->contextId])) {
                        $checkManager = false;
                        if (isset($params['cache']['groups_managers'][$question->contextId])) {
                            $managerIds = $params['cache']['groups_managers'][$question->contextId];
                            if (in_array(OW::getUser()->getId(), $managerIds)) {
                                $isManager = true;
                            }
                        }
                    }
                    $qParams = array(
                        'group' => $groupEntity,
                        'checkManager' => $checkManager,
                        'params' => $params,
                    );
                    $editable = false;
                    if (isset($data['editable']) && $data['editable']) {
                        $editable = true;
                    }
                    $questionData['editable'] = $editable || $isManager || FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserEdit($questionId, $qParams);
                    $options = FRMMOBILESUPPORT_BOL_WebServiceQuestions::getInstance()->prepareOptionsData($questionId, $questionData['editable'], $params);
                    foreach ($options as $prepareOptionData){
                        if ($prepareOptionData['answered'] == true) {
                            $answeredOneOptions = true;
                        }
                    }

                    $canAnswerOptions = true;
                    if ($answeredOneOptions && !$multipleAnswer) {
                        $canAnswerOptions = false;
                    }
                    $questionData['add_answer'] = $canAnswerOptions;
                    $questionData['options'] = $options;

                    if ($editable) {
                        $questionData['add_option'] = true;
                    } else {
                        $userCanAddOption = FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserAddOption($questionId, $groupEntity, $checkManager, $question);
                        $questionData['add_option'] = $userCanAddOption;
                    }

                    if (OW::getUser()->isAuthenticated()) {
                        $subscribe = false;
                        if (isset($params['cache']['questions']) && array_key_exists($questionId, $params['cache']['questions'])) {
                            if (isset($params['cache']['questions'][$questionId]['subscribe'])) {
                                $subscribe = true;
                            }
                        } else {
                            $subscribe = FRMQUESTIONS_BOL_SubscribeDao::getInstance()->findSubscribeByQuestionAndUser(OW::getUser()->getId(), $questionId);
                        }
                        $data['subscribe_editable'] = true;
                        $data['subscribe_status'] = isset($subscribe);
                    }
                }
            }

            if($action->entityType == "avatar-change"){
                if( $creatorActivity != null){
                    $userId = $creatorActivity->userId;
                }
            }

            if($action->entityType == "user_join"){
                if( $creatorActivity != null){
                    $userId = (int) $creatorActivity->userId;
                }
            }

            if($action->entityType == "groups-add-file" || $action->entityType == "event-add-file") {
                $entityId = $action->entityId;
                $fileId = null;
                if($action->entityType == "groups-add-file"){
                    if(FRMSecurityProvider::checkPluginActive('groups', true)){
                        $file = null;
                        if (isset($params['cache']['group_files'][$entityId])) {
                            $file = $params['cache']['group_files'][$entityId];
                        }
                        if ($file == null) {
                            $file = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findById($entityId);
                        }
                        if($file != null){
                            $fileId = $file->attachmentId;
                        }
                    }
                }else if($action->entityType == "event-add-file"){
                    if(FRMSecurityProvider::checkPluginActive('event', true)) {
                        $file = FRMEVENTPLUS_BOL_EventFilesDao::getInstance()->findById($entityId);
                        if ($file != null) {
                            $fileId = $file->attachmentId;
                        }
                        if(isset($file->eventId)) {
                            $objectId = $file->eventId;
                        }
                    }
                }
                if($fileId != null) {
                    $attachment = null;
                    if (isset($params['cache']['attachments'][$fileId])) {
                        $attachment = $params['cache']['attachments'][$fileId];
                    }
                    if ($attachment == null) {
                        $attachment = BOL_AttachmentDao::getInstance()->findById($fileId);
                    }
                    if (isset($attachment) && $attachment->getId() > 0) {
                        $files[$attachment->getId()] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->prepareFileInformation($attachment, $params);
                        $userId = $attachment->userId;
                    }
                }else if($creatorActivity != null){
                    $userId = $creatorActivity->userId;
                }

            }else if(in_array($action->entityType, array("groups-join", "groups-leave")) && isset($actionDataJson->data->joinUsersId)) {
                $userId = $actionDataJson->data->joinUsersId;
            }else if($action->entityType == "friend_add") {
                $friendedUsers = $this->findAllParticipatedUsersInAction($action->entityType, $action->entityId);
                if($friendedUsers != null && sizeof($friendedUsers) > 1) {
                    $friendAddInfo = array();
                    $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($friendedUsers);
                    $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($friendedUsers);
                    $userIdRequested = $friendedUsers[0];
                    $userIdAccepted = $friendedUsers[1];
                    $userId = $userIdRequested;
                    $paramsText = array(
                        "user_url" => "",
                        "name" => $usernames[$userIdAccepted],
                    );
                    $activityString = OW::getLanguage()->text('friends', 'newsfeed_action_string', $paramsText);
                    $friendAddInfo[] = array(
                        'id' => $userIdRequested,
                        "name" => $usernames[$userIdRequested],
                        "avatarUrl" => $avatars[$userIdRequested]
                    );
                    $friendAddInfo[] = array(
                        'id' => $userIdAccepted,
                        "name" => $usernames[$userIdAccepted],
                        "avatarUrl" => $avatars[$userIdAccepted]
                    );
                    $data['friendAddInformation'] = $friendAddInfo;
                }else{
                    if( $creatorActivity != null){
                        $userId = $creatorActivity->userId;
                    }
                }
                $activityString = $generalService->stripString($activityString, true, true);
            }

            $previewIdList = array();
            if(isset($actionDataJson->previewIdList)){
                $previewIdList = $actionDataJson->previewIdList;
            }
            if(isset($actionDataJson->attachmentIdList)) {
                foreach ($actionDataJson->attachmentIdList as $fileId) {
                    $attachment = null;
                    if (isset($params['cache']['attachments'][$fileId])) {
                        $attachment = $params['cache']['attachments'][$fileId];
                    }
                    if ($attachment == null) {
                        $attachment = BOL_AttachmentDao::getInstance()->findById($fileId);
                    }
                    $validPreviewType = FRMSecurityProvider::getAttachmentExtensionType($attachment);
                    if (isset($attachment) && $attachment->getId() > 0 && ($validPreviewType == null || !in_array($fileId, $previewIdList))) {
                        $files[$attachment->getId()] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->prepareFileInformation($attachment, $params);
                    }
                }
            }

            foreach ($previewIdList as $previewId){
                $attachment = null;
                if (isset($params['cache']['attachments'][$previewId])) {
                    $attachment = $params['cache']['attachments'][$previewId];
                }
                if ($attachment == null) {
                    $attachment = BOL_AttachmentDao::getInstance()->findById($previewId);
                }
                if (isset($attachment) && $attachment->getId() > 0) {
                    $attInfo = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->prepareFileInformation($attachment, $params);
                    if(isset($attInfo['fileUrl'])){
                        $extension = '';
                        if (isset(pathinfo($attachment->getOrigFileName())['extension'])) {
                            $extension = strtolower(pathinfo($attachment->getOrigFileName())['extension']);
                        }

                        $defaultThumbnailUrl = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'video-no-video.png';
                        if(FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)){
                            $thumbnailObj = null;
                            if (isset($params['cache']['thumbnail_attachment']) && array_key_exists($attInfo['id'], $params['cache']['thumbnail_attachment'])) {
                                $thumbnailObj = $params['cache']['thumbnail_attachment'][$attInfo['id']];
                            } else {
                                $thumbnailObj = FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->getThumbnailById($attInfo['id']);
                            }
                            if ($thumbnailObj == null) {
                                if (OW::getUser()->isAuthenticated() && $attachment->userId == OW::getUser()->getId()) {
                                    $defaultThumbnailUrl = null;
                                }
                            } else {
                                $defaultThumbnailUrl = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getThumbnailFilePath($thumbnailObj->getName());
                            }
                        }

                        $attName = '';
                        if (isset($attInfo['fileName'])){
                            $attName = $attInfo['fileName'];
                        }

                        if(in_array($extension, FRMSecurityProvider::IMAGE_EXTENSIONS)) {
                            $images[] = array(
                                "url" => $attInfo['fileUrl'],
                                "id" => $attInfo['id'],
                                "name" => $attName,
                            );
                        } else if(in_array($extension, FRMSecurityProvider::AUDIO_EXTENSIONS)) {
                            $sounds[] = array(
                                "url" => $attInfo['fileUrl'],
                                "id" => $attInfo['id'],
                                "name" => $attName,
                            );
                        } else if(in_array($extension, FRMSecurityProvider::VIDEO_EXTENSIONS)) {
                            $videos[] = array(
                                "url" => $attInfo['fileUrl'],
                                "id" => $attInfo['id'],
                                "thumbnail" => $defaultThumbnailUrl,
                                "name" => $attName,
                            );
                        }
                    }
                }
            }

            if($action->entityType == "group" && FRMSecurityProvider::checkPluginActive('groups', true)){
                $groupEntity = GROUPS_BOL_Service::getInstance()->findGroupById($action->entityId);
                if ($groupEntity != null) {
                    $entityTitle = $groupEntity->title;
                    $objectId = $groupEntity->id;
                    $onLocationTitle = $groupEntity->title;
                    $entityDescription = $groupEntity->description;
                    $entityImage = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($groupEntity);
                    $time = $groupEntity->timeStamp;
                }
            }else if($action->entityType == "event" && FRMSecurityProvider::checkPluginActive('event', true)){
                $eventEntity = EVENT_BOL_EventService::getInstance()->findEvent($action->entityId);
                if ($eventEntity != null) {
                    if ($eventEntity->getImage()) {
                        $entityImage = EVENT_BOL_EventService::getInstance()->generateImageUrl($eventEntity->getImage(), true);
                    } else {
                        $entityImage = EVENT_BOL_EventService::getInstance()->generateDefaultImageUrl();
                    }
                    $objectId = $eventEntity->id;
                    $entityTitle = $eventEntity->title;
                    $onLocationTitle = $eventEntity->title;
                    $entityDescription = $eventEntity->description;
                    $time = $eventEntity->createTimeStamp;
                }
            }else if($action->entityType == "news-entry" && FRMSecurityProvider::checkPluginActive('frmnews', true)){
                $newsEntity = EntryService::getInstance()->findById($action->entityId);
                if ($newsEntity != null) {
                    $entityTitle = $generalService->stripString($newsEntity->title);
                    $entityDescription = $newsEntity->entry;
                    $objectId = $newsEntity->id;
                    if ($newsEntity->getImage()) {
                        $entityImage = EntryService::getInstance()->generateImageUrl($newsEntity->getImage(), true);
                    } else {
                        $entityImage = EntryService::getInstance()->generateDefaultImageUrl();
                    }
                    $time = $newsEntity->timestamp;
                }
            }else if($action->entityType == "forum-topic" && FRMSecurityProvider::checkPluginActive('forum', true)){
                $topic = null;
                if (isset($params['cache']['topics'][$action->entityId])) {
                    $topic = $params['cache']['topics'][$action->entityId];
                }
                if ($topic == null) {
                    $topic = FORUM_BOL_ForumService::getInstance()->findTopicById($action->entityId);
                }
                if ($topic) {
                    $firstPostText = null;
                    if (isset($params['cache']['topics_posts'][$topic->id])) {
                        $topicPosts = $params['cache']['topics_posts'][$topic->id];
                        $minimumId = min(array_keys($topicPosts));
                        if (isset($topicPosts[$minimumId])) {
                            $firstPostText = $topicPosts[$minimumId]->text;
                        }
                    } else {
                        $postForum = FORUM_BOL_PostDao::getInstance()->findTopicPostList($topic->id, 0, 1);
                        if (isset($postForum) && $postForum != null && sizeof($postForum) > 0){
                            $firstPostText = $postForum[0]->text;
                        }
                    }
                    $entityTitle = $topic->title;
                    $objectId = $topic->id;
                    if ($firstPostText != null){
                        $entityDescription = $generalService->setMentionsOnText($firstPostText);
                    }
                    $forumService = FORUM_BOL_ForumService::getInstance();
                    $groupInfo = null;
                    if (isset($params['cache']['topic_groups'][$topic->groupId])) {
                        $groupInfo = $params['cache']['topic_groups'][$topic->groupId];
                    }
                    if ($groupInfo == null) {
                        $groupInfo = $forumService->getGroupInfo($topic->groupId);
                    }
                    if ( $groupInfo )
                    {
                        $forumSection = null;
                        if (isset($params['cache']['topic_sections'][$groupInfo->sectionId])) {
                            $forumSection = $params['cache']['topic_sections'][$groupInfo->sectionId];
                        }
                        if ($forumSection == null) {
                            $forumSection = $forumService->findSectionById($groupInfo->sectionId);
                        }
                        if ( $forumSection && $forumSection->entity == 'groups' ){
                            $forumGroupId = (int) $groupInfo->entityId;
                        }
                    }
                }
            }else if($action->entityType == "forum-post" && FRMSecurityProvider::checkPluginActive('forum', true)){
                $forumPosts = FORUM_BOL_PostDao::getInstance()->findListByPostIds(array($action->entityId));
                if (isset($forumPosts) && sizeof($forumPosts) > 0) {
                    $forumPost = $forumPosts[0];
                    $topic = null;
                    if (isset($params['cache']['topics'][$forumPost['topicId']])) {
                        $topic = $params['cache']['topics'][$forumPost['topicId']];
                    }
                    if ($topic == null) {
                        $topic = FORUM_BOL_ForumService::getInstance()->findTopicById($forumPost['topicId']);
                    }
                    if ($topic) {
                        $entityTitle = $topic->title;
                        $objectId = $topic->id;
                        $entityDescription = $generalService->stripString($forumPost['text'], true);
                        $forumService = FORUM_BOL_ForumService::getInstance();
                        $groupInfo = null;
                        if (isset($params['cache']['topic_groups'][$topic->groupId])) {
                            $groupInfo = $params['cache']['topic_groups'][$topic->groupId];
                        }
                        if ($groupInfo == null) {
                            $groupInfo = $forumService->getGroupInfo($topic->groupId);
                        }
                        if ($groupInfo) {
                            $forumSection = null;
                            if (isset($params['cache']['topic_sections'][$groupInfo->sectionId])) {
                                $forumSection = $params['cache']['topic_sections'][$groupInfo->sectionId];
                            }
                            if ($forumSection == null) {
                                $forumSection = $forumService->findSectionById($groupInfo->sectionId);
                            }
                            if ($forumSection && $forumSection->entity == 'groups') {
                                $forumGroupId = (int)$groupInfo->entityId;
                            }
                        }
                    }
                }
            }else if($action->entityType == "video_comments" && FRMSecurityProvider::checkPluginActive('frmvideoplus', true) && FRMSecurityProvider::checkPluginActive('video', true)){
                $clip = VIDEO_BOL_ClipService::getInstance()->findClipById($action->entityId);
                if($clip != null){
                    $entityTitle = $clip->title;
                    $entityDescription = $clip->description;
                    $objectId = $clip->id;

                    if (substr($clip->code, 0, 7) == "<iframe") {
                        $videoUrl = $clip->code;
                        $videoIframe = true;
                    } else {
                        if (strpos($clip->code, 'https://www.aparat.com/video/video/embed/videohash/') !== false) {
                            $parts = explode('/', strstr($clip->code, 'https://www.aparat.com/video/video/embed/videohash/'));
                            $aparat_video_ID = $parts[7];
                            $videoUrl = '<iframe src="https://www.aparat.com/video/video/embed/videohash/'.$aparat_video_ID.'/vt/frame" allowfullscreen="true"></iframe>';
                            $videoIframe = true;
                        } else {
                            $videoUrl = FRMVIDEOPLUS_BOL_Service::getInstance()->getVideoFilePath($clip->code);
                        }
                    }
                    if (!empty($clip->thumbUrl)) {
                        $videoThumbnailUrl = FRMVIDEOPLUS_BOL_Service::getInstance()->getVideoFilePath($clip->thumbUrl);
                    }
                }else{
                    return null;
                }
            } else if($action->entityType == "blog-post" && isset($actionDataJson->content->vars->title)){
                $entityTitle = $generalService->stripString($actionDataJson->content->vars->title);
                $blogEntity = PostService::getInstance()->findById($action->entityId);
                $entityDescription = $blogEntity->post;
                $objectId = $blogEntity->id;
                $entityImage = OW::getPluginManager()->getPlugin('base')->getStaticUrl(). 'css/images/' . 'no-picture.png';
                $time = $blogEntity->timestamp;
            }

            if($creatorActivity != null && isset($creatorActivity->timeStamp)){
                $time = $creatorActivity->timeStamp;
            }else if(empty($time) && isset($actionDataJson->time)){
                $time = $actionDataJson->time;
            }

            $lastActivityObject = $this->getLastActivity($action->entityType, $action->entityId, $action, $params);
            if($lastActivityObject != null && isset($lastActivityObject['data']['string'])){
                $lastActivityString = $lastActivityObject['data']['string'];
                $lastActivityString = $this->getLocalizedText($lastActivityString);
                $assigns = array();
                if(isset($lastActivityObject['data']['assigns'])){
                    $assigns = $lastActivityObject['data']['assigns'];
                }
                $lastActivityString = $this->processAssigns($lastActivityString, $assigns);
                $lastActivityString = $generalService->stripString($lastActivityString, true, true);
                $cachedUserInfo = null;
                if (isset($params['preparedUsersData'][$lastActivityObject['userId']])) {
                    $cachedUserInfo = $params['preparedUsersData'][$lastActivityObject['userId']];
                } else {
                    $cachedUserInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($lastActivityObject['userId']);
                }
                $lastActivity = array(
                    "timestamp" => $lastActivityObject['timeStamp'],
                    "user" => $cachedUserInfo,
                    "text" => $lastActivityString
                );
            }

            $likesInformation = $this->getLikesInformation($action->entityType, $action->entityId, $params);

            $features['likable'] = true;
            $features['commentable'] = true;
            if(isset($actionDataJson->features)){
                $features['likable'] = false;
                $features['commentable'] = false;
                if(in_array('likes', $actionDataJson->features)){
                    $features['likable'] = true;
                }
                if(in_array('comments', $actionDataJson->features)){
                    $features['commentable'] = true;
                }
            }

            $entityTypeBlackList = array('friend_add', 'groups-status', 'group', 'group-join', 'event', 'groups-add-file', 'forum-topic');
            $feedTypeWhiteList = array('user', 'my', 'site');
            if($creatorActivity != null &&
                $feedObject != null &&
                !in_array($action->entityType, $entityTypeBlackList) &&
                in_array($feedObject->feedType , $feedTypeWhiteList)){
                $privacy = $creatorActivity->privacy;
                if($feedObject->feedId == OW::getUser()->getId()){
                    $privacyEditable = true;
                }
            }

            if(isset($actionDataJson->sourceUser)){
                $doc = new DOMDocument();
                @$doc->loadHTML(mb_convert_encoding($actionDataJson->sourceUser, 'HTML-ENTITIES', 'UTF-8'));
                $doc->removeChild($doc->doctype);
                $link = $doc->getElementsByTagName('a');
                if (isset($link) && isset($link->item(0)->nodeValue) ) {
                    $forwardString = $generalService->stripString($link->item(0)->nodeValue);
                }
                if(isset($actionDataJson->contextFeedType) && $actionDataJson->contextFeedType == 'groups'){
                    $forwardEntityType = 'groups-status';
                    $forwardEntityId = $actionDataJson->contextFeedId;
                    $groupEntity = GROUPS_BOL_Service::getInstance()->findGroupById($forwardEntityId);
                    if ($groupEntity != null) {
                        $entityTitle = $groupEntity->title;
                        $onLocationTitle = $groupEntity->title;
                    }
                }
            }

            if($action->entityType == 'user-status' && $feedObject != null && $feedObject->feedType == 'user' && $feedObject->feedId != $userId){
                $onLocationTitle = BOL_UserService::getInstance()->getDisplayName($feedObject->feedId);
                $objectId = $feedObject->feedId;
            }

            if($action->entityType == 'birthday'){
                if (isset($actionDataJson->userData) && isset($actionDataJson->userData->userId)) {
                    $userId = $actionDataJson->userData->userId;
                    if (isset($actionDataJson->birthdate)){
                        $birthdayTime = $actionDataJson->birthdate;
                        $dateTime = new DateTime($birthdayTime);
                        $dateTime = $dateTime->getTimestamp();
                        $text = UTIL_DateTime::formatSimpleDate($dateTime, true);
                    }
                }
            }

            if($objectId == null){
                $objectId = $action->entityId;
            }

            $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText($text, $params);

            $forwardable = false;
            $eventForwardable = OW::getEventManager()->trigger(new OW_Event('newsfeed.can_forward_post',array('group_object' => $groupEntity,'activity' => $creatorActivity,'action' => $action,'entityId' => (int) $action->entityId, 'entityType' => $action->entityType, 'params' => $params)));
            if(isset($eventForwardable->getData()['forwardable'])){
                $forwardable = $eventForwardable->getData()['forwardable'];
            }

            if (isset($actionDataJson->reply_to)) {
                $replyAction = null;
                if (isset($params['replyActions'][$actionDataJson->reply_to])) {
                    $replyAction = $params['replyActions'][$actionDataJson->reply_to];
                }
                if ($replyAction == null) {
                    $replyAction = NEWSFEED_BOL_ActionDao::getInstance()->findActionById($actionDataJson->reply_to);
                }
                if (!empty($replyAction)) {
                    $replyActionData = $replyAction->data;
                    if($replyActionData != null){
                        $replyActionData = json_decode($replyActionData);
                        if (isset($replyActionData->status) && isset($replyActionData->data->userId)) {
                            $data['reply_action_id'] = (int) $replyAction->id;
                            $data['reply_action_entity_id'] = (int) $replyAction->entityId;
                            $data['reply_action_entity_type'] = $replyAction->entityType;
                            $data['reply_action_username'] = BOL_UserService::getInstance()->getDisplayName($replyActionData->data->userId);
                            $data['reply_action_text'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText(FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($replyActionData->status, true, true, true));
                        }
                    }
                }else{
                    $data['reply_action_id'] = -1;
                    $data['reply_action_text'] = OW::getLanguage()->text('mailbox', 'deleted_message');
                }
            }

            if (isset($actionDataJson->replyTo)) {
                $replyAction = null;
                if (isset($params['replyActions'][(int)$actionDataJson->replyTo->{'$replyToEntityId'}])) {
                    $replyActionId = $params['replyActions'][(int)$actionDataJson->replyTo->{'$replyToEntityId'}];
                    $replyAction = NEWSFEED_BOL_ActionDao::getInstance()->findActionById((int)$replyActionId->id);
                }
                if (!empty($replyAction)) {
                    $replyActionData = $replyAction->data;
                    if($replyActionData != null){
                        $replyActionData = json_decode($replyActionData);
                        if (isset($replyActionData->status) && isset($replyActionData->data->userId)) {
                            $data['reply_action_id'] = (int) $replyAction->id;
                            $data['reply_action_entity_id'] = (int) $replyAction->entityId;
                            $data['reply_action_entity_type'] = $replyAction->entityType;
                            $data['reply_action_username'] = BOL_UserService::getInstance()->getDisplayName($replyActionData->data->userId);
                            $data['reply_action_text'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText(FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($replyActionData->status, true, true, true));
                        }
                    }
                }
            }

            if (isset($actionDataJson->tags)) {
                $data['tags'] = $actionDataJson->tags;
            }

            if (isset($actionDataJson->productHashtags)) {
                $data['productHashtags'] = $actionDataJson->productHashtags;
            }

            if($groupId != null && $groupEntity != null){
                $isChannel = false;
                if (isset($params['additionalInfo']['isChannel'])) {
                    $isChannel = $params['additionalInfo']['isChannel'];
                } else {
                    $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load',
                        array('groupId' => $groupId, 'additionalInfo' => $params)));
                    if(isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel'] == true) {
                        $isChannel = true;
                    }
                }

                $hideCommentFeatures = NEWSFEED_BOL_Service::getInstance()->checkDisableComment();
                $hideLikeFeatures = NEWSFEED_BOL_Service::getInstance()->checkDisableLike();
                if ($isChannel || $hideCommentFeatures) {
                    $features['commentable'] = false;
                }
                if ($isChannel || $hideLikeFeatures) {
                    $features['likable'] = false;
                }
            }

            if($text == '' && in_array($action->entityType, array("user_edit"))) {
                $text = $activityString;
            }

            $data['objectId'] = (int) $objectId;
            $data['entityId'] = (int) $action->entityId;
            $data['entityType'] = $action->entityType;
            $data['text'] = $text;
            $data['forwardable'] = $forwardable;
            $data['actionId'] = (int) $action->id;
            $data['likable'] = $features['likable'];
            $data['products'] = $products;
            $data['dislikable'] = (bool)OW::getConfig()->getValue('frmlike', 'dislikePostActivate');
            $data['removable'] = $this->canRemoveFeed($action, OW::getUser()->getId(), $creatorActivity, $groupEntity);
            $data['commentable'] = $features['commentable'];
            $cachedUserInfo = null;
            if (isset($params['preparedUsersData'][$userId])) {
                $cachedUserInfo = $params['preparedUsersData'][$userId];
            } else {
                $cachedUserInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($userId);
            }
            $data['user'] = $cachedUserInfo;
            $data['likes'] = $likesInformation['likes'];
            $data['dislikes'] = $likesInformation['dislikes'];
            $data['user_like'] = $this->getUserLikesValue($likesInformation['likes']);
            $data['user_dislike'] = $this->getUserLikesValue($likesInformation['dislikes']);
            $commentCount = 0;
            if (isset($params['cache']['comments_count'][$action->entityType . '-' . $action->entityId])) {
                $commentCount = $params['cache']['comments_count'][$action->entityType . '-' . $action->entityId];
            } else {
                $commentCount = FRMMOBILESUPPORT_BOL_WebServiceComment::getInstance()->getCommentsCount($action->entityType, $action->entityId);
            }
            $data['comments_count'] = $commentCount;
            if (in_array('comments', $params)){
                $data['comments'] = FRMMOBILESUPPORT_BOL_WebServiceComment::getInstance()->getCommentsInformation($action->entityType, $action->entityId, 1);
                if(is_array($data['comments'])) {
                    $commIds = array();
                    foreach ($data['comments'] as $com) {
                        $commIds[] = (int) $com['id'];
                    }
                    if (FRMSecurityProvider::checkPluginActive('notifications', true)) {
                        $commentNotifications = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findNotificationsByEntityIds('status_comment', $commIds, OW::getUser()->getId());
                        $unMarkedNotifications = array();
                        if($commentNotifications != null && is_array($commentNotifications)) {
                            foreach ($commentNotifications as $cNotif) {
                                if ($cNotif->viewed != 1) {
                                    $unMarkedNotifications[] = $cNotif->id;
                                }
                            }
                            if (sizeof($unMarkedNotifications) > 0){
                                NOTIFICATIONS_BOL_NotificationDao::getInstance()->markViewedByIds($unMarkedNotifications);
                            }
                        }
                    }

                }
            }

            $data['video_iframe'] = $videoIframe;
            $data['privacy'] = $privacy;
            $data['privacyEditable'] = $privacyEditable;
            $data['time'] = $time;
            $data['flagAble'] =  true;

            // Temporary we should use here
            $data['onLocationTitle'] = $onLocationTitle;
            $data['activityString'] = $activityString;
            $data['entityTitle'] = $entityTitle;
            $data['album'] = $album;
            $data['files'] = $files;
            $data['question'] = $questionData;
            $data['images'] = $images;
            $data['sounds'] = $sounds;
            $data['videos'] = $videos;
            $data['video_url'] = $videoUrl;
            $data['entityDescription'] = $generalService->stripString($entityDescription);
            $data['forwardEntityId'] = $forwardEntityId;
            $data['forwardEntityType'] = $forwardEntityType;
            $data['forwardString'] = $forwardString;
            $data['entityImage'] = $entityImage;
            $data['video_thumbnail_url'] = $videoThumbnailUrl;
            $data['forumGroupId'] = $forumGroupId;
            $data['lastActivity'] = $lastActivity;

            // Set data if they are not empty
            if ($onLocationTitle != "") {
                $data['onLocationTitle'] = $onLocationTitle;
            }
            if ($activityString != "") {
                $data['activityString'] = $activityString;
            }
            if ($entityTitle != "") {
                $data['entityTitle'] = $entityTitle;
            }
            if (sizeof($album) > 0) {
                $data['album'] = $album;
            }
            if (sizeof($files) > 0) {
                $data['files'] = $files;
            }
            if (sizeof($questionData) > 0) {
                $data['question'] = $questionData;
            }
            if (sizeof($images) > 0) {
                $data['images'] = $images;
            }
            if (sizeof($sounds) > 0) {
                $data['sounds'] = $sounds;
            }
            if (sizeof($videos) > 0) {
                $data['videos'] = $videos;
            }
            if ($videoUrl != null) {
                $data['video_url'] = $videoUrl;
            }
            if ($entityDescription != null) {
                $data['entityDescription'] = $generalService->stripString($entityDescription);
            }
            if ($forwardEntityId != null) {
                $data['forwardEntityId'] = $forwardEntityId;
            }
            if ($forwardEntityType != null) {
                $data['forwardEntityType'] = $forwardEntityType;
            }
            if ($forwardString != null) {
                $data['forwardString'] = $forwardString;
            }
            if ($entityImage != null) {
                $data['entityImage'] = $entityImage;
            }
            if ($videoThumbnailUrl != null) {
                $data['video_thumbnail_url'] = $videoThumbnailUrl;
            }
            if ($forumGroupId != null) {
                $data['forumGroupId'] = $forumGroupId;
            }
            if ($lastActivity != null) {
                $data['lastActivity'] = $lastActivity;
            }
            if (isset($actionDataJson->reply_to) & ($replyToEntityId != null) & ($replyToEntityType != null)) {
                $data['replyTo'] = array(
                    'entityId'=>$replyToEntityId,
                    'entityType'=>$replyToEntityType,
                );
            }

            if (($replyToEntityId != null) & ($replyToEntityType != null)
                & isset($data['reply_action_username'])
                & isset($data['reply_action_text'])) {
                $data['replyTo'] = array(
                    'entityId'=>$replyToEntityId,
                    'entityType'=>$replyToEntityType,
                    'username'=>$data['reply_action_username'],
                    'text'=>$data['reply_action_text']);
            }


            $isChannel = false;
            if (isset($params['additionalInfo']['isChannel'])) {
                $isChannel = $params['additionalInfo']['isChannel'];
            }
            $replyToAction = !$isChannel && FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->isReplyFeatureEnable($action->entityType, isset($actionDataJson->data->status),$groupId, false);

            if ($replyToAction) {
                $data['replyToActionId'] = (int) $action->id;
            }

            if (OW::getUser()->isAuthenticated() && isset($data['user']['id']) && OW::getUser()->getId() != $data['user']['id']) {
                $checkBlockingFromDB = true;
                $isBlocked = false;
                if (isset($params['cache']['blockedUsers'][$data['user']['id']])) {
                    $isBlocked = $params['cache']['blockedUsers'][$data['user']['id']];
                    $checkBlockingFromDB = false;
                }
                if (isset($params['cache']['blockedByUsers'][$data['user']['id']])) {
                    $isBlocked = $isBlocked || $params['cache']['blockedByUsers'][$data['user']['id']];
                    $checkBlockingFromDB = false;
                }
                if($checkBlockingFromDB){
                    $isBlocked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $data['user']['id']);
                    $isBlocked = $isBlocked || BOL_UserService::getInstance()->isBlocked($data['user']['id'], OW::getUser()->getId());
                }
                if ($isBlocked) {
                    // one blocked another
                    $data['commentable'] = false;
                    $data['likable'] = false;
                    if (isset($data['replyToActionId'])) {
                        unset($data['replyToActionId']);
                    }
                }
            }
        }

        if ((!(sizeof($images) > 0)) &&
            $action->entityType !== "groups-status" &&
            !(!empty($data['videos']) ||
            !empty($data['video_url']) ||
            !empty($data['video_thumbnail_url']) )
        ) {
            $images[] = array(
                "url" =>  OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl(). 'img/' . 'thumbnail.jpg',
                "id" => 0,
                "name" => "No_image",
            );
            $data['images'] = $images;
        }

        return $data;
    }

    private function preparedActionDataInfo($action, $creatorActivities = array()){
        $userId= null;
        $creatorActivity = null;
        $actionDataJson = null;
        $replyActionId = null;

        if(isset($action->data)){
            $actionDataJson = $action->data;
        }

        if($actionDataJson != null){
            $actionDataJson = json_decode($actionDataJson);
        }

        if($actionDataJson != null){
            $creatorActivity = null;
            if (isset($creatorActivities[$action->id])) {
                $creatorActivity = $creatorActivities[$action->id];
            }
            if ($creatorActivity == null) {
                $creatorActivity = $this->getCreatorActivityOfAction($action->entityType, $action->entityId, $action);
            }
            if (isset($actionDataJson->replyTo)) {
                $replyActionId = (int)$actionDataJson->replyTo->{'$replyToEntityId'};
            }
            if(isset($actionDataJson->ownerId)){
                $userId = $actionDataJson->ownerId;
            }

            if(isset($actionDataJson->data->userId)){
                $userId = $actionDataJson->data->userId;
            }

            if($action->format == "text" || $action->format == "content"){
                if(isset($actionDataJson->data->userId)){
                    $userId = $actionDataJson->data->userId;
                }
            }

            if(in_array($action->entityType, array("groups-join", "groups-leave")) && isset($actionDataJson->data->joinUsersId)) {
                $userId = $actionDataJson->data->joinUsersId;
            }

            if($action->entityType == 'birthday'){
                if (isset($actionDataJson->userData) && isset($actionDataJson->userData->userId)) {
                    $userId = $actionDataJson->userData->userId;
                }
            }
        }
        if ($userId == null && $creatorActivity != null && isset($creatorActivity->userId)) {
            $userId = $creatorActivity->userId;
        }
        return array(
            'userId' => $userId,
            'creatorActivity' => $creatorActivity,
            'replyActionId' => $replyActionId,
        );
    }

    public function setPostVideoThumbnail($videoId, $fileData) {
        if($videoId  == null || $fileData == null || !FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)){
            return array('valid' => false, 'thumbnail' => '');
        }

        $attachment = BOL_AttachmentDao::getInstance()->findById($videoId);
        if ($attachment == null || $attachment->userId != OW::getUser()->getId()) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $fileHashName=UTIL_String::getRandomString(10).'.png';
        $tmpVideoImageFile = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getThumbnailFileDir($fileHashName);
        $filteredData = explode(',', $fileData);
        if (!isset($filteredData[1])) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $valid = FRMSecurityProvider::createFileFromRawData($tmpVideoImageFile, $filteredData[1]);
        if (!$valid) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $thumbnailObj = FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->addThumbnail($videoId,$fileHashName, OW::getUser()->getId());
        if ($thumbnailObj == null) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $thumbnail = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getThumbnailFilePath($thumbnailObj->getName());
        return array('valid' => true, 'thumbnail' => $thumbnail);
    }

    public function changePrivacy(){
        $privacy = null;
        if (isset($_POST['privacy'])){
            $privacy = $_POST['privacy'];
        }
        $entityId = null;
        if (isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }
        $entityType = null;
        if (isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        if($privacy == null || $entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $objectId = $entityId;
        $actionType = 'user_status';
        $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);

        if(in_array($entityType, array('album'))){
            $actionType = $entityType;
        }else if($action == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if($action != null){
            $objectId = $action->getId();
        }

        $feedId = null;
        $feed = $this->findFeed($entityType, $entityId, $action);
        if($feed != null && isset($feed->feedId)) {
            $feedId = $feed->feedId;
        }

        $res = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->editPrivacyProcess($privacy, $objectId, $actionType, $feedId, $action);

        if(isset($res['result'])) {
            if($res['result']){
                return array('valid' => true, 'message' => 'changed.');
            }else{
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param null $action
     * @param $params
     * @return null
     */
    public function getLastActivity($entityType, $entityId, $action = null, $params = array()){
        $driver = new NEWSFEED_CLASS_UserDriver();
        $driver->setup(array('feedType' => 'my', 'feedId' => OW::getUser()->getId()));
        $action = $driver->getAction($entityType, $entityId, $action, $params);
        $lastActivity = null;
        if ($action != null) {
            foreach ($action->getActivityList() as $a) {
                /* @var $a NEWSFEED_BOL_Activity */
                $activity[$a->id] = array(
                    'activityType' => $a->activityType,
                    'activityId' => $a->activityId,
                    'id' => $a->id,
                    'data' => json_decode($a->data, true),
                    'timeStamp' => $a->timeStamp,
                    'privacy' => $a->privacy,
                    'userId' => $a->userId,
                    'visibility' => $a->visibility
                );

                if ($lastActivity === null && !in_array($activity[$a->id]['activityType'], NEWSFEED_BOL_Service::getInstance()->SYSTEM_ACTIVITIES)) {
                    $lastActivity = $activity[$a->id];
                }
            }
        }
        return $lastActivity;
    }

    protected function processAssigns( $content, $assigns )
    {
        $search = array();
        $values = array();

        foreach ( $assigns as $key => $item )
        {
            $search[] = '[ph:' . $key . ']';
            $values[] = $item;
        }

        $result = str_replace($search, $values, $content);
        $result = preg_replace('/\[ph\:\w+\]/', '', $result);

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $result)));
        if(isset($stringRenderer->getData()['string'])){
            $result = $stringRenderer->getData()['string'];
        }
        return $result;
    }

    protected function getLocalizedText( $textData )
    {
        if ( !is_array($textData) )
        {
            return $textData;
        }

        $keyData = explode("+", $textData["key"]);
        $vars = empty($textData["vars"]) ? array() : $textData["vars"];

        return OW::getLanguage()->text($keyData[0], $keyData[1], $vars);
    }

    public function canRemoveFeedByAction($entityType, $entityId, $userId = null, $action = null){
        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        }
        if($action == null){
            return false;
        }

        return $this->canRemoveFeed($action, $userId);
    }

    public function canRemoveFeed($action, $userId = null, $creatorActivity = null, $groupEntity = null){
        if($userId == null){
            $userId = OW::getUser()->getId();
        }
        if($userId == null){
            return false;
        }
        if($creatorActivity == null){
            $creatorActivity = $this->getCreatorActivityOfAction($action->entityType, $action->entityId);
        }
        if($creatorActivity != null){
            if($creatorActivity->userId == $userId) {
                return true;
            }

            $actionFeed = null;
            if (isset($creatorActivity->feed_object)) {
                $actionFeed = $creatorActivity->feed_object;
                if($actionFeed->feedId == $userId){
                    return true;
                }
            }
            if ($actionFeed == null) {
                $actionFeed = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids(array($creatorActivity->id));
                if(isset($actionFeed[$creatorActivity->id]) && isset($actionFeed[$creatorActivity->id][0])){
                    if($actionFeed[$creatorActivity->id][0]->feedId == $userId){
                        return true;
                    }
                }
            }
        }

        if(in_array($action->entityType, array("groups-join", "groups-leave", "groups-status", "groups-add-file"))) {
            $groupId = $this->getGroupId($action->entityType, $action->entityId, $creatorActivity);
            if($groupId != null){
                if ($groupEntity == null) {
                    $groupEntity = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                }
                if (isset($groupEntity)) {
                    $isGroupOwner = $groupEntity->userId == OW::getUser()->getId();
                    $isGroupModerator = OW::getUser()->isAuthorized('groups');
                    $canRemoveGroupPost = $isGroupOwner || $isGroupModerator;
                    if ($canRemoveGroupPost) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function getUserLikesValue($likesInformation){
        if(!OW::getUser()->isAuthenticated() || $likesInformation == null || empty($likesInformation)){
            return false;
        }

        foreach ($likesInformation as $likeInformation){
            if($likeInformation['id'] == OW::getUser()->getId()){
                return true;
            }
        }

        return false;
    }

    private function getLikesInformation($entityType, $entityId, $params = array()){
        $data = array();
        $userIds = array();
        if (isset($params['cache']['like_entities'][$entityType.'-'.$entityId])) {
            $userIds['likes'] = $params['cache']['like_entities'][$entityType.'-'.$entityId];
            $userIds['dislikes'] = $params['cache']['dislike_entities'][$entityType.'-'.$entityId];
        } else {
            $userIds = NEWSFEED_BOL_Service::getInstance()->findEntityLikeUserIds($entityType, $entityId);
        }
        $data['likes'] = array();
        $data['dislikes'] = array();

        foreach ($userIds['likes'] as $userId){
            $data['likes'][] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($userId, false, false, $params);
        }

        foreach ($userIds['dislikes'] as $userId){
            $data['dislikes'][] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($userId, false, false, $params);
        }

        return $data;
    }

    public function forwardAction(){

        $feedType=null;
        $privacy=null;
        $visibility=null;
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return array(false,'newsfeed_plugin_is_not_active');
        }
        if(!isset($_POST['actionId']))
        {
            return array('valid' => false, 'message' => 'actionId_is_null');
        }
        $actionId = $_POST['actionId'];
        $action=NEWSFEED_BOL_Service::getInstance()->findActionById($actionId);
        if(!isset($action))
        {
            return array('valid' => false, 'message' => 'action_is_null');
        }
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($action->getId()));
        foreach($activities as $activityId){
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if($activity->activityType=='create'){
               $privacy=$activity->privacy;
               $visibility=$activity->visibility;
            }
        }
        switch ($action->entityType)
        {
            case 'user-status':
                $feedType='user';
                break;
            case 'groups-status':
                $feedType='groups';
                break;
            default:
                return array('valid' => false, 'message' => 'invalid_source_type');
        }

        $sourceId = null;

        $actionDataJson = null;
        if(isset($action->data)){
            $actionDataJson = $action->data;
        }

        if($actionDataJson != null){
            $actionDataJson = json_decode($actionDataJson);
        }

        if ($actionDataJson != null) {
            if (isset($actionDataJson->contextFeedId)) {
                $sourceId = $actionDataJson->contextFeedId;
            } else if(isset($actionDataJson->data) && isset($actionDataJson->data->userId)) {
                $sourceId = $actionDataJson->data->userId;
            }
        }

        if(!isset($_POST['entityType']) || !in_array($_POST['entityType'],array('user','groups')))
        {
            return array('valid' => false, 'message' => 'unknown_entityType');
        }
        $entityType=$_POST['entityType'];


        $enableQRSearch = (boolean)OW::getConfig()->getValue('frmnewsfeedplus','enable_QRSearch');
        if( ($_POST['entityType']=="groups" && !isset($_POST['entityId'])) || (!$enableQRSearch && !isset($_POST['entityId'])) || (!isset($_POST['entityId']) && !isset($_POST['questions']) && !isset($_POST['accountType'])) )
        {
            return array('valid' => false, 'message' => 'entityId_is_null');
        }
        if (!isset($_POST["forwardType"]) && $feedType === 'groups') {
            $_POST["forwardType"] = $feedType;
        }

        if ($_POST['entityType'] == "user" && !isset($_POST['entityId'])) {
            return $this->forwardToUsersByQuestions($actionId, $feedType, $entityType, $sourceId, $privacy, $visibility);
        }
        $entityId = $_POST['entityId'];
        $selectedIds = array($entityId);
        $errorMessage = null;
        list($isDataValid,$errorMessage)=$this->checkUserIsValidToForward($actionId, $sourceId, $selectedIds, $feedType,$entityType);
        if($isDataValid===false)
        {
            return array('valid' => false, 'message' => $errorMessage);
        }
        $result = FRMSecurityProvider::forwardPost($actionId,$sourceId,$selectedIds,$privacy,$visibility,$feedType,$entityType,true);
        if (!isset($result['valid'])){
            return array('valid' => false, 'message' => $errorMessage);
        }
        if (!$result['valid']) {
            return $result;
        }
        if (isset($result['item']) && $result['item'] != null && isset($result['item']['entityType']) && isset($result['item']['entityId'])) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($result['item']['entityType'], $result['item']['entityId']);
            if ($action == null) {
                return array('valid' => true, 'message' => $result['message']);
            }
            $actionInfo = $this->preparedActionData($action);
            return array('valid' => true, 'message' => $result['message'], 'post' => $actionInfo);
        }
        return $result;
    }

    public function forwardMessages()
    {

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if ($_POST['sourceIds'] == null
            || $_POST['sourceType'] == null
            || $_POST['targetTypes'] == null
            || $_POST['targetIds'] == null) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $sourceIds = (array)json_decode($_POST['sourceIds']);
        $sourceType = $_POST['sourceType'];
        $targetTypes = (array)json_decode($_POST['targetTypes']);
        $targetIds = (array)json_decode($_POST['targetIds']);

        if (count($targetTypes) != count($targetIds) ){
            return array('valid' => false, 'message' => 'input_error');
        }

        $result = null;
        $counter =0;
        foreach ($targetIds as $targetId){

            if ($sourceType == 'group' && $targetTypes[$counter] == 'group') {

                if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                    return array(false, 'newsfeed_plugin_is_not_active');
                }

                if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
                    return array(false, 'groups_plugin_is_not_active');
                }
                $actions = NEWSFEED_BOL_Service::getInstance()->findActionListByEntityIdsAndEntityType($sourceIds, 'groups-status');
                $actionIds = array_column( $actions,'id');
                $activitiesList = NEWSFEED_BOL_ActivityDao::getInstance()->findSiteFeedActivity($actionIds);
                $actionList = NEWSFEED_BOL_Service::getInstance()->findActionByIds($actionIds);

                foreach ($actions as $action) {
                    if (!isset($action)) {
                        $result[] = array('valid' => false, 'message' => 'action_not_found');
                    }
                    $sourceId = null;
                    $actionDataJson = null;
                    $privacy = 'everybody';
                    $visibility = 15;
                    $actionId = $action->id;
                    foreach ($activitiesList as $activity) {
                        if ($activity->activityType == 'create' && $activity->actionId == $actionId) {
                            $privacy = $activity->privacy;
                            $visibility = $activity->visibility;
                        }
                    }
                    if (isset($actionList[$actionId]->data)) {
                        $actionDataJson = json_decode($actionList[$actionId]->data);
                        if (isset($actionDataJson->contextFeedId)) {
                            $sourceId = $actionDataJson->contextFeedId;
                        } else if (isset($actionDataJson->data) && isset($actionDataJson->data->userId)) {
                            $sourceId = $actionDataJson->data->userId;
                        }
                    }

                    $_POST["forwardType"] = 'groups-status';
                    list($isDataValid, $errorMessage) = $this->checkUserIsValidToForward($actionId, $sourceId, $targetIds, 'groups', 'groups');
                    if ($isDataValid === false) {
                        $result[$actionId] = array('valid' => false, 'message' => $errorMessage);
                    } else {
                        $postResult = FRMSecurityProvider::forwardPost($actionId, $sourceId, (array)$targetId, $privacy, $visibility, 'groups', 'groups', true);
                    }
                    if (!isset($postResult['valid'])) {
                        $result[$actionId] = array('valid' => false, 'message' => $errorMessage, 'entityId' => $actionId);
                    } else {
                        $result[$actionId] = $postResult;
                    }
                }

            }

            if ($sourceType == 'group' && $targetTypes[$counter] == 'chat') {

                if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                    return array(false, 'newsfeed_plugin_is_not_active');
                }

                if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
                    return array(false, 'groups_plugin_is_not_active');
                }
                $conversationService = MAILBOX_BOL_ConversationService::getInstance();
                if (!FRMSecurityProvider::checkPluginActive('mailbox', true)
                    || !OW::getUser()->isAuthenticated()) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }
                $actions = NEWSFEED_BOL_Service::getInstance()->findActionListByEntityIdsAndEntityType($sourceIds, 'groups-status');
                $actionIds = array_column( $actions,'id');
                $actionList = NEWSFEED_BOL_Service::getInstance()->findActionByIds($actionIds);
                $userId = OW::getUser()->getId();
                $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $targetId);
                if ($conversationId == null || empty($conversationId)) {
                    $conversation = $conversationService->createChatConversation($userId, $targetId);
                    $conversationId = $conversation->getId();
                }
                $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($conversationId);
                if ($conversation == null) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }

                foreach ($actionList as $action) {
                    $actionDataJson = null;
                    $actionId = $action->entityId;
                    if (!isset($action)) {
                        $result[$targetId][] = array('valid' => false, 'actionId' => $actionId, 'message' => 'action_not_found');
                    }
                    if (isset($action->data)) {
                        $actionDataJson = json_decode($action->data);
                    }

                    $opponentId = $targetId;
                    $text = $actionDataJson->data->status;
                    $isForwarded = true;

                    if ($userId == $opponentId || !isset($text) || $text == '' || $opponentId == null) {
                        $result[$targetId][$actionId] = array('valid' => false, 'message' => 'authorization_error');
                    }

                    $text = str_replace('↵', "\r\n", $text);
                    $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($text, false);
                    $event = new OW_Event('mailbox.before_send_message', array(
                        'senderId' => $userId,
                        'recipientId' => $opponentId,
                        'conversationId' => $conversationId,
                        'message' => $text
                    ), array('result' => true, 'error' => '', 'message' => $text));
                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if (!$data['result']) {
                        return array('valid' => false, 'message' => 'authorization_error');
                    }

                    $text = $data['message'];
                    try {
                        $replyId = null;
                        if (isset($_POST['replyId'])) {
                            $replyId = $_POST['replyId'];
                        }
                        $message = $conversationService->createMessage($conversation, $userId, $text, $replyId, false, null, $isForwarded);
                        if(isset($actionDataJson->attachmentId)){
                            MAILBOX_BOL_ConversationService::getInstance()->forwardAttachmentIdToChat($actionDataJson->attachmentId, $message->id);
                        }
                        if (isset($actionDataJson->attachmentIdList)) {
                            foreach ($actionDataJson->attachmentIdList as $attachmentId){
                                MAILBOX_BOL_ConversationService::getInstance()->forwardAttachmentIdToChat($attachmentId, $message->id);
                            }
                        }
                    } catch (InvalidArgumentException $e) {
                        $result[$targetId][$actionId] = array('valid' => false, 'message' => 'authorization_error');
                    }
                    $result[$targetId][$actionId] = array('valid' => true, 'message_status' => 'sent');
                }
            }

            if($sourceType == 'chat' && $targetTypes[$counter] == 'group'){
                if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                    return array(false, 'newsfeed_plugin_is_not_active');
                }

                if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
                    return array(false, 'groups_plugin_is_not_active');
                }
                $conversationService = MAILBOX_BOL_ConversationService::getInstance();
                if (!FRMSecurityProvider::checkPluginActive('mailbox', true)
                    || !OW::getUser()->isAuthenticated()) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }
                $sourceConversation = MAILBOX_BOL_MessageDao::getInstance()->findMessageById($sourceIds[0]);
                $conversation = MAILBOX_BOL_MessageDao::getInstance()->findMessagesByIdList($sourceConversation[0]->conversationId,$sourceIds);
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($targetId);
                $bundle = FRMSecurityProvider::generateUniqueId('nfa-' . "feed1");
                $postData = array("forwarded_from_chat"=> True);
                $userId = OW::getUser()->getId();
                foreach ($conversation as $message){
                    $attachmentIdList = '';
                    $previewIdList=array();
                    $attachments = MAILBOX_BOL_ConversationService::getInstance()->findAttachmentsByMessageIdList((array((int)$message->id)));
                    if (isset($attachments)) {
                        foreach ($attachments[$message->id] as $attachment){
                            $newAttachmentIds = array();
                            BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);
                            $fileExt = UTIL_File::getExtension($attachment->fileName);
                            $attachmentPath = MAILBOX_BOL_ConversationService::getInstance()->getAttachmentFilePath($attachment->id, $attachment->hash, $fileExt, $attachment->fileName);
                            if (OW::getStorage()->fileExists($attachmentPath)) {
                                $newAttachmentFileName = urldecode($attachment->fileName);
                                $item = array();
                                $item['name'] = $newAttachmentFileName;
                                $item['type'] = 'image/' . $fileExt;
                                $item['error'] = 0;
                                $item['size'] = UTIL_File::getFileSize($attachmentPath, false);
                                $pluginKey = 'frmnewsfeedplus';
                                $tempFileId = BOL_FileTemporaryService::getInstance()->addTemporaryFile($attachmentPath, $newAttachmentFileName, $userId);
                                $item['tmp_name'] = BOL_FileTemporaryService::getInstance()->getTemporaryFilePath($tempFileId);
                                $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $bundle);
                                $newAttachmentIds[] = $dtoArr['dto']->id;
                                if (isset($actionData->previewIdList) && in_array($attachmentId, $actionData->previewIdList)) {
                                    $previewIdList[] = $dtoArr['dto']->id;
                                }
                            }
                        }
                        $postData['attachmentIdList'] = $newAttachmentIds;
                    }
                    $result[$targetId][$message->id] = NEWSFEED_BOL_Service::getInstance()
                        ->addStatus( $userId, 'groups', $targetId, $group->privacy, $message->text, $postData );
                }
            }

            if($sourceType == 'chat' && $targetTypes[$counter] == 'chat'){
                $conversationService = MAILBOX_BOL_ConversationService::getInstance();
                if (!FRMSecurityProvider::checkPluginActive('mailbox', true)
                    || !OW::getUser()->isAuthenticated()) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }
                $userId = OW::getUser()->getId();
                    $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $targetId);
                    if ($conversationId == null || empty($conversationId)) {
                        $conversation = $conversationService->createChatConversation($userId, $targetId);
                        $conversationId = $conversation->getId();
                    }else{
                        $conversation = $conversationService->getConversation($conversationId);
                    }
                    $sourceConversation = MAILBOX_BOL_MessageDao::getInstance()->findMessageById($sourceIds[0]);
                    $massages = MAILBOX_BOL_MessageDao::getInstance()->findMessagesByIdList($sourceConversation[0]->conversationId,$sourceIds);
                foreach ($massages as $message){

                    $opponentId = $targetId;
                    $text = $message->text;
                    $isForwarded = true;

                    if ($userId == $opponentId || !isset($text) || $text == '' || $opponentId == null) {
                        $result[$targetId][$message->id] = array('valid' => false, 'message' => 'authorization_error');
                    }

                    $event = new OW_Event('mailbox.before_send_message', array(
                        'senderId' => $userId,
                        'recipientId' => $opponentId,
                        'conversationId' => $conversationId,
                        'message' => $text
                    ), array('result' => true, 'error' => '', 'message' => $text));
                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if (!$data['result']) {
                        return array('valid' => false, 'message' => 'authorization_error');
                    }

                    $text = $data['message'];

                    try {
                        $replyId = null;
                        if (isset($_POST['replyId'])) {
                            $replyId = $_POST['replyId'];
                        }
                        $newMessage = $conversationService->createMessage($conversation, $userId, $text, $replyId, false, null, $isForwarded);
                        $attachments = MAILBOX_BOL_ConversationService::getInstance()->findAttachmentsByMessageIdList((array((int)$message->id)));
                        if (isset($attachments)) {
                            MAILBOX_BOL_ConversationService::getInstance()->forwardMessageAttachments($newMessage->id,$attachments[$message->id] );
                        }

                    } catch (InvalidArgumentException $e) {
                        $result[$targetId][$message->id] = array('valid' => false, 'message' => $message);
                    }
                    /*$item = $conversationService->getRawMessageInfo($message);
                      if (isset($_POST['_id']) && !empty($_POST['_id']) && $_POST['_id'] != null && $_POST['_id'] != "null") {
                          $item['_id'] = $_POST['_id'];
                      } else {
                          $item['_id'] = $message->id;
                      }*/
                    $result[$targetId][$message->id] = array('valid' => true, 'message_status' => 'sent');
                }
            }

            $counter++;
        }


        return $result;

    }

    public function removeMessages(){

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ($_POST['messageType'] == null
            || $_POST['messageIds'] == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $messageType = $_POST['messageType'];
        $messageIds = (array)json_decode($_POST['messageIds']);


        if($messageType == 'group'){

            $entityType = 'groups-status';
            foreach ($messageIds as $key => $entityId){
                $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
                if(!$this->userCanSeeAction($entityType, $entityId, $action)){
                    return array('valid' => false, 'message' => 'authorization_error');
                }
                if(!$this->canRemoveFeedByAction($entityType, $entityId, null, $action)){
                    return array('valid' => false, 'message' => 'authorization_error');
                }
                $feedId = '';
                $feedType = '';
                $feed = $this->findFeed($entityType, $entityId, $action);
                if ($feed) {
                    $feedId = (int) $feed->feedId;
                    $feedType = $feed->feedType;
                }

                OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array('entityType' => $entityType, 'entityId' => $entityId)));
                $data = array("entityId" => (int) $entityId, "entityType" => $entityType, "feedId" => $feedId, "feedType" => $feedType);
                $out[$key] = $data;
            }

            return array('valid' => true, 'message' => 'removed', 'results' => $out);
        }

        if($messageType == 'chat'){
            if (!FRMSecurityProvider::checkPluginActive('mailbox', true)
                || !OW::getUser()->isAuthenticated()) {
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $userId = OW::getUser()->getId();
            $sourceConversation = MAILBOX_BOL_MessageDao::getInstance()->findMessageById($messageIds[0]);
            $massages = MAILBOX_BOL_MessageDao::getInstance()->findMessagesByIdList($sourceConversation[0]->conversationId,$messageIds);
            $senderId = array_unique( array_column($massages,'senderId') )[0];
            $lastMessage = MAILBOX_BOL_MessageDao::getInstance()->findLastMessage( $sourceConversation[0]->conversationId );
            if($userId == (int)$senderId){
                MAILBOX_BOL_MessageDao::getInstance()->deleteMessagesByIdList($messageIds);
                if(in_array( (int)$lastMessage->id ,  $messageIds)){
                    $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findConversationObjectById( (int)$sourceConversation[0]->conversationId );
                    $beforeLastMessage = MAILBOX_BOL_MessageDao::getInstance()->findHistory($sourceConversation[0]->conversationId,(int)$lastMessage->id,1);
                    $conversation->lastMessageId = (int) $beforeLastMessage[0]->id;
                    MAILBOX_BOL_ConversationDao::getInstance()->save($conversation);
                }
                return array('valid' => true, 'message' => 'all_messages_removed');
            }else{
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }
    }

    private function checkUserIsValidToForward($actionId, $sourceId, $selectedIds, $feedType,$forwardType)
    {

        if($forwardType=='groups') {
            if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
                return array(false,'groups_plugin_is_not_active');
            }
        }
        if($feedType=='groups') {
            /*
             * check if user has access to source group
             */
            $sourceGroup = GROUPS_BOL_Service::getInstance()->findGroupById($sourceId);
            if (!isset($sourceGroup)) {
                return array(false,'source_group_not_found');
            }
            $isCurrentUserCanViewSourceGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($sourceGroup);
            if (!$isCurrentUserCanViewSourceGroup) {
                return array(false,'access_denied_to_source_group');
            }
            /*
             * check if destination users allow current user to write on their walls.
             */
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                if ($forwardType == 'user') {
                    foreach ($selectedIds as $selectedUserId) {
                        $whoCanPostPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy('who_post_on_newsfeed', $selectedUserId);
                        if ($whoCanPostPrivacy == 'only_for_me') {
                            return array(false,'access_denied_to_user_feed');
                        }
                    }
                }
            }

            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('actionId' => $actionId, 'feedId' => $sourceId)));
        }

        /* check if user has access to selected group(s) */
        if($forwardType=='groups') {
            foreach ($selectedIds as $selectedGroupId) {
                $selectedGroup = GROUPS_BOL_Service::getInstance()->findGroupById($selectedGroupId);
                if (!isset($selectedGroup)) {
                    return array(false,'destination_group_not_found');
                }
                $isCurrentUserCanViewSelectedGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($selectedGroup);
                if (!$isCurrentUserCanViewSelectedGroup) {
                    return array(false,'access_denied_to_destination_group');
                }
                else{
                    $event = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',array('groupId' => $selectedGroupId)));
                    if(isset($event->getData()['channelParticipant']) && $event->getData()['channelParticipant']==true) {
                        return array(false,'access_denied_to_write_destination_group_channel');
                    }
                }
            }
        }
        if($feedType=='user') {
            $activity=FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getCreatorActivityOfActionById($actionId);
            /*
             * check if current user is owner of the activity
             */
            if ($activity->userId == OW::getUser()->getId()) {
                return true;
            }
            /*
             * check if current user has access to this activity
             */
            $activityOwnerId = $activity->userId;
            $activityPrivacy = $activity->privacy;

            /*
             * activity is private
             */
            if ($activity->userId != OW::getUser()->getId())
            {
                switch ( $activityPrivacy)
                {
                    case 'only_for_me' :
                        return array(false,'access_denied_to_user_feed');
                        break;
                    case 'everybody' :
                        /*
                         * all users have access to a general status
                         */
                        return true;
                        break;
                    case 'friends_only' :
                        /*
                         * check if current user is a friend of owner of the activity
                         */
                        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
                            throw new Redirect404Exception();
                        }
                        $service = FRIENDS_BOL_Service::getInstance();
                        $isFriends = $service->findFriendship(OW::getUser()->getId(), $activityOwnerId);
                        if (isset($isFriends) && $isFriends->status == 'active') {
                            return true;
                        }else {
                            return array(false,'friends_plugin_not_installed');
                        }
                        break;
                    default:
                        return array(false,'no_activity_privacy_found');
                }
            }
        }
    }

    private function forwardToUsersByQuestions($actionId, $feedType, $entityType, $sourceId, $privacy, $visibility) {
        $count = self::CHUNK_SIZE;
        list($first,$accountType,$questions) = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->setupParametersQuestionsToInvite();
        $users = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getForwardSearchedUsersByQuestions($questions,$first, $count, $actionId);
        $result = array('valid' => false);
        while (!empty($users)) {
            $selectedIds = array_column($users, 'id');
            $errorMessage = null;
            list($isDataValid,$errorMessage)=$this->checkUserIsValidToForward($actionId, $sourceId, $selectedIds, $feedType,$entityType);
            if($isDataValid===false)
            {
                return array('valid' => false, 'message' => $errorMessage);
            }
            $result = FRMSecurityProvider::forwardPost($actionId,$sourceId,$selectedIds,$privacy,$visibility,$feedType,$entityType,true);
            if (!isset($result['valid'])){
                return array('valid' => false, 'message' => $errorMessage);
            }
            if (!$result['valid']) {
                return $result;
            }

            $first += $count;
            $users = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getForwardSearchedUsersByQuestions($questions,$first, $count, $actionId);
        }
        if (isset($result['item']) && $result['item'] != null && isset($result['item']['entityType']) && isset($result['item']['entityId'])) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($result['item']['entityType'], $result['item']['entityId']);
            if ($action == null) {
                return array('valid' => true, 'message' => $result['message']);
            }
            $actionInfo = $this->preparedActionData($action);
            return array('valid' => true, 'message' => $result['message'], 'post' => $actionInfo);
        }
        return $result;
    }

    public function userFollowingList()
    {

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = null;
        if(isset($_GET['userId'])){
            $userId = $_GET['userId'];
        }else if(isset($_GET['username'])){
            $user = BOL_UserService::getInstance()->findByUsername($_GET['username']);
            if($user != null){
                $userId = $user->getId();
            }
        }else if(OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
        }

        if($userId == null){
            return array();
        }

        $first = (!empty($_GET['first']) && intval($_GET['first']) > 0 ) ? intval($_GET['first']) : 1;
        $followingUsers = NEWSFEED_BOL_FollowDao::getInstance()->findUserFollowingListWithPaginate($userId, $first, 10);

        $userList = array();
        foreach ( $followingUsers as $user )
        {
            $avatarSrc = BOL_AvatarService::getInstance()->getAvatarUrl($user->feedId);
            $username = BOL_UserService::getInstance()->getUserName($user->feedId);
            if($username){
                $userList[] = array(
                    'id' => $user->feedId,
                    'username' => $username,
                    'profileImageUrl' => BOL_AvatarService::getInstance()->getAvatarUrl($user->feedId),
                    'profileUrl' => BOL_UserService::getInstance()->getUserUrl($user->feedId),
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($user->feedId, $avatarSrc),
                );
            }
        }
        return $userList;
    }

    public function searchFollowers(){
        $userId = null;
        if(isset($_GET['userId']) && is_numeric($_GET['userId'])){
            $userId = $_GET['userId'];
        }

        if($userId == null && OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
        }

        if($userId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $search = '';
        if(isset($_POST['search'])){
            $search = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['search'], true, true);
        }

        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize()*1000;
        $first = 0;
        if(isset($_POST['first'])){
            $first = (int) FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['first'], true, true);
        }

        $followerObjects = $this->findFollowersSearchUserIdList($userId,$search);
        $allFollowersIds = array_column( (array) $followerObjects, 'userId');
        $followerIds = array_slice($allFollowersIds,$first,$count) ;
        if(!empty($followerIds)) {
            $usersObjects = BOL_UserService::getInstance()->findUserListByIdList($followerIds);
            $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($followerIds);
            foreach ($usersObjects as $usersObject) {
                if(strpos($usernames[$usersObject->id], $search) !== false ){
                    $users[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($usersObject, null, null, false, true);
                }
            }
        }
        if($users == null){
            return array('valid' => false, 'message' => 'no_result_found');
        }
        return $users;
    }

    public function findFollowersSearchUserIdList($userId,$search){
        return NEWSFEED_BOL_FollowDao::getInstance()->findList('user',$userId);
    }

    public function userFollowerList()
    {

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = null;
        if(isset($_GET['userId'])){
            $userId = $_GET['userId'];
        }else if(isset($_GET['username'])){
            $user = BOL_UserService::getInstance()->findByUsername($_GET['username']);
            if($user != null){
                $userId = $user->getId();
            }
        }else if(OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
        }

        if($userId == null){
            return array();
        }

        $first = (!empty($_GET['first']) && intval($_GET['first']) > 0 ) ? intval($_GET['first']) : 1;
        $followerUsers = NEWSFEED_BOL_FollowDao::getInstance()->findUserFollowerListWithPaginate($userId, $first, 10);

        $userList = array();
        foreach ( $followerUsers as $user )
        {
            $avatarSrc = BOL_AvatarService::getInstance()->getAvatarUrl($user->userId);
            $username = BOL_UserService::getInstance()->getUserName($user->userId);
            if($username) {
                $userList[] = array(
                    'id' => $user->userId,
                    'username' => $username,
                    'profileImageUrl' => BOL_AvatarService::getInstance()->getAvatarUrl($user->userId),
                    'profileUrl' => BOL_UserService::getInstance()->getUserUrl($user->userId),
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($user->userId, $avatarSrc),
                );
            }
        }
        return $userList;
    }
}