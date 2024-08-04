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
class FRMMOBILESUPPORT_BOL_WebServiceComment
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

    public function getCommentsInformationFromRequest(){
        $entityType = null;
        $entityId = null;
        if (isset($_GET['entityType']))
        {
            $entityType = $_GET['entityType'];
        }
        if (isset($_GET['entityId']))
        {
            $entityId = $_GET['entityId'];
        }

        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if(FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            if(!FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->userCanSeeAction($entityType, $entityId)){
                return array();
            }
        }

        if($entityType == 'event'){
            $canUserAccessToGroup = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance()->canUserAccessWithEntity($entityType, $entityId, OW::getUser()->getId());
            if(!$canUserAccessToGroup){
                return array();
            }
        }

        return $this->getCommentsInformation($entityType, $entityId);
    }

    public function likeComment() {
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $id = null;
        if(isset($_GET['id'])){
            $id = $_GET['id'];
        }

        if ($id === null) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $comment = BOL_CommentService::getInstance()->findComment($id);
        if (!isset($comment)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $commentEntityTId = $comment->getCommentEntityId();
        $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($commentEntityTId);

        if (!isset($commentEntity)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = $commentEntity->entityType;
        $entityId = $commentEntity->entityId;

        $access = $this->likable($entityType, $entityId, OW::getUser()->getId());
        if (!$access) {
            return array('valid' => false, 'message' => 'authorization_error', 'entityType' => $entityType, 'id' => $id);
        }
        FRMLIKE_BOL_Service::getInstance()->setLike($id, $entityType, OW::getUser()->getId());
        return array('valid' => true, 'message' => 'liked', 'entityId' => $entityId, 'entityType' => $entityType, 'id' => $id);
    }

    public function unlikeComment() {
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $id = null;
        if(isset($_GET['id'])){
            $id = $_GET['id'];
        }

        if ($id === null) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $comment = BOL_CommentService::getInstance()->findComment($id);
        if (!isset($comment)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $commentEntityTId = $comment->getCommentEntityId();
        $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($commentEntityTId);

        if (!isset($commentEntity)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityType = $commentEntity->entityType;
        $entityId = $commentEntity->entityId;

        $access = $this->likable($entityType, $entityId, OW::getUser()->getId());
        if (!$access) {
            return array('valid' => false, 'message' => 'authorization_error', 'entityType' => $entityType, 'id' => $id);
        }
        FRMLIKE_BOL_Service::getInstance()->removeLike($id, $entityType, OW::getUser()->getId());
        return array('valid' => true, 'message' => 'unliked', 'entityId' => $entityId, 'entityType' => $entityType, 'id' => $id);
    }

    public function likable($entityType, $entityId, $userId, $checkAccess = true) {
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return false;
        }
        $validEntityTypes = FRMLIKE_BOL_Service::getInstance()->getValidEntityTypes();
        if(!in_array($entityType, $validEntityTypes))
        {
            return false;
        }
        if (!$checkAccess) {
            return true;
        }
        if($entityType == 'groups-status' || $entityType == 'groups-join' || $entityType == 'group' || $entityType == 'groups-leave'){
            $canUserAccessToGroup = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->canUserAccessWithEntity($entityType, $entityId);
            if(!$canUserAccessToGroup){
                return false;
            }
            return true;
        }

        if($entityType == 'video_comments'){
            $canUserAccessToVideo = VIDEO_BOL_ClipService::getInstance()->canUserSeeVideoOfUserId(OW::getUser()->getId(), $entityId);
            if(!$canUserAccessToVideo){
                return false;
            }
            return true;
        }

        if($entityType == 'photo_comments'){
            $canUserAccessToPhoto = PHOTO_BOL_PhotoService::getInstance()->canUserSeePhoto($entityId);
            if(!$canUserAccessToPhoto){
                return false;
            }
            return true;
        }

        if($entityType == 'event'){
            $canUserAccessToEvent = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance()->canUserAccessWithEntity($entityType, $entityId, $userId);
            if(!$canUserAccessToEvent){
                return false;
            }
            return true;
        }

        if($entityType == 'blog-post'){
            $canUserAccessToBlog = FRMMOBILESUPPORT_BOL_WebServiceBlogs::getInstance()->canUserCommentBlog($entityId);
            if(!$canUserAccessToBlog){
                return false;
            }
            return true;
        }

        if(in_array($entityType, $this->getDashboardFeedEntityType())) {
            $access = $this->canAccessToComment($entityType, $entityId, $userId);
            if (!$access) {
                return false;
            }
            return true;
        }

        if($entityType == 'news-entry') {
            return true;
        }
        return false;
    }

    public function getCommentsInformation($entityType, $entityId, $fromPage = null){
        $data = array();

        $page = null;
        if($fromPage != null){
            $page = $fromPage;
        }
        if(isset($_GET['page'])){
            $page = $_GET['page'];
        }

        $first = null;
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        if($page == null && $first != null){
            $page = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageNumber($first);
        }

        if($page == null){
            $page= 1;
        }

        $repliesPage = 1;
        if (isset($_GET['commentId'])) {
            $data = array();
            $commentId = $_GET['commentId'];
            $commentEntity = array(BOL_CommentDao::getInstance()->findById($commentId));
            $repliesPage = $page;
        } else {
            $commentEntity = BOL_CommentService::getInstance()->findCommentList($entityType, $entityId, $page);
        }


        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        /** @var BOL_Comment $comment */
        foreach ($commentEntity as $key => $comment) {
            $data[] = $this->prepareComment($comment);
            $commentReplies = $this->getCommentReplies($comment->id, $repliesPage, $count);

            $replies = array();
            foreach ($commentReplies['replies'] as $reply) {
                $reply = $this->prepareComment($reply);
                $replies[] = $reply;
            }
            $commentReplies['replies'] = $replies;

            $data[$key]['repliesCount'] = $commentReplies['repliesCount'];
            $data[$key]['replies'] = $commentReplies['replies'];
        }

        return $data;
    }

    public function getCommentsCount($entityType, $entityId){
        $commentsCount = BOL_CommentService::getInstance()->findCommentCount($entityType, $entityId);
        return $commentsCount;
    }

    /***
     * @param BOL_Comment $comment
     * @return array
     */
    public function prepareComment($comment){
        if($comment == null) {
            return array();
        }
        $files = array();
        if(isset($comment->attachment) && $comment->attachment != null){
            $attachments = json_decode($comment->attachment);
            $tmpUrl = null;
            if(isset($attachments->url)){
                $tmpUrl = $attachments->url;
            }
            if($tmpUrl != null){
                $files[] = FRMSecurityProvider::getInstance()->correctHomeUrlVariable($tmpUrl);
            }
        }

        $removable = true;
        $commentInfo = $this->getCommentInfoForDeleteByObject($comment);
        if($commentInfo == null){
            $removable = false;
        }
        $user = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($comment->userId);
        $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($comment->message, true, false, true);

        $regex_view = '((( |^|\n|\t|>|>|\(|\))@)(\w+))';
        preg_match_all('/'.$regex_view.'/', $text, $matches);
        $replacedString = array();
        if(isset($matches[4])){
            foreach($matches[4] as $match){
                $mentionedUser = BOL_UserService::getInstance()->findByUsername($match);
                if($mentionedUser){
                    if (!in_array($mentionedUser, $replacedString)) {
                        $text = str_replace('@'.$match, '@'.$match.':'.$mentionedUser->getId(), $text);
                        $replacedString[] = $mentionedUser;
                    }
                }
            }
        }

        $commentEntityTId = $comment->getCommentEntityId();
        $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($commentEntityTId);

        $likeInfo = array(
            'enable' => false,
            'liked' => false,
            'disliked' => false,
            'sum' => 0,
            'disliked_users' => array(),
            'liked_users' => array()
        );
        if (OW::getUser()->isAuthenticated() && $this->likable($commentEntity->entityType, $commentEntity->entityId, OW::getUser()->getId(), false)) {
            $likeInfo['enable'] = true;
            list($commentLikeInfo, $userVoteInfo) = FRMLIKE_BOL_Service::getInstance()->getLikeInfoForList(array($comment->id), 'frmlike-' . $commentEntity->entityType);
            if (isset($commentLikeInfo) && isset($commentLikeInfo[$comment->id])) {
                $commentLikeInfo = $commentLikeInfo[$comment->id];
            } else {
                $commentLikeInfo = null;
            }
            if ($commentLikeInfo != null) {
                if (!empty($commentLikeInfo['sum'])) {
                    $likeInfo['sum'] = (int)$commentLikeInfo['sum'];
                }
                if (!empty($commentLikeInfo['upUserId'])) {
                    $upUserIds = $commentLikeInfo['upUserId'];
                    foreach ($upUserIds as $upUserId){
                        if ($upUserId == OW::getUser()->getId()) {
                            $likeInfo['liked'] = true;
                        }
                    }
                    $likeInfo['liked_users'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($upUserIds);
                }
                if (!empty($commentLikeInfo['downUserId'])) {
                    $downUserIds = $commentLikeInfo['downUserId'];
                    foreach ($downUserIds as $downUserId){
                        if ($downUserId == OW::getUser()->getId()) {
                            $likeInfo['disliked'] = true;
                        }
                    }
                    $likeInfo['disliked_users'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($downUserIds);
                }
            }
        }

        $result = array(
            "userId" => (int) $comment->userId,
            "user" => $user,
            "text" => $text,
            "id" => (int) $comment->id,
            "time" => $comment->createStamp,
            "removable" => $removable,
            "entityType" => "comment",
            "likeInfo" => $likeInfo,
            "entityId" => (int) $comment->id,
            "flagAble" => true,
            "files" => $files
        );
        if (!empty($comment->replyId)) {
            $replyUser = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($comment->replyUserId);
            $result["replyUserId"] = !empty($comment->replyUserId) ? (int) $comment->replyUserId : null;
            $result["replyUser"] = $replyUser;
            $result["replyId"] = !empty($comment->replyId) ? (int) $comment->replyId : null;
        }
        return $result;
    }

    public function addComment(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $text = null;
        $entityType = null;
        $entityId = null;
        $pluginKey = null;

        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        if(isset($_POST['pluginKey'])){
            $pluginKey = $_POST['pluginKey'];
        }

        if(isset($_POST['entityId'])){
            $entityId = $_POST['entityId'];
        }

        if(isset($_POST['entityType'])){
            $entityType = $_POST['entityType'];
        }

        $userId = OW::getUser()->getId();
        $access = false;

        if($entityType == null || $entityId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!in_array($pluginKey, array('event', 'newsfeed', 'frmnews', 'video', 'photo','blogs', 'forum'))){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!in_array($entityType, array('event', 'groups-status', 'groups-join', 'groups-leave', 'group', 'user_join', 'news-entry', 'video_comments', 'photo_comments', 'blog-post', 'forum-post')) && !in_array($entityType, $this->getDashboardFeedEntityType())){
            return array('valid' => false, 'message' => 'authorization_error', 'entityType' => $entityType, 'entityId' => $entityId);
        }

        if($entityType == 'groups-status' || $entityType == 'groups-join' || $entityType == 'group' || $entityType == 'groups-leave'){
            $canUserAccessToGroup = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->canUserAccessWithEntity($entityType, $entityId);
            if(!$canUserAccessToGroup){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $access = true;
        }

        if($entityType == 'video_comments'){
            $canUserAccessToVideo = VIDEO_BOL_ClipService::getInstance()->canUserSeeVideoOfUserId(OW::getUser()->getId(), $entityId);
            if(!$canUserAccessToVideo){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $access = true;
        }

        if($entityType == 'photo_comments'){
            $canUserAccessToPhoto = PHOTO_BOL_PhotoService::getInstance()->canUserSeePhoto($entityId);
            if(!$canUserAccessToPhoto){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $access = true;
        }

        if($entityType == 'event'){
            $canUserAccessToEvent = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance()->canUserAccessWithEntity($entityType, $entityId, $userId);
            if(!$canUserAccessToEvent){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $access = true;
        }

        if($entityType == 'blog-post'){
            $canUserAccessToBlog = FRMMOBILESUPPORT_BOL_WebServiceBlogs::getInstance()->canUserCommentBlog($entityId);
            if(!$canUserAccessToBlog){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $access = true;
        }

        if(!$access && in_array($entityType, $this->getDashboardFeedEntityType())) {
            $access = $this->canAccessToComment($entityType, $entityId, $userId);
            if (!$access) {
                return array('valid' => false, 'message' => 'authorization_error', 'entityType' => $entityType, 'entityId' => $entityId);
            }
        }

        $attachment = null;
        $virusDetectedFiles = array();
        $fileValid = true;
        $fileAdded = false;

        if ( isset($_FILES) && !empty($_FILES['file']['name']) ){
            if ( (int) $_FILES['file']['error'] !== 0 ||
                !is_uploaded_file($_FILES['file']['tmp_name']) ||
                !UTIL_File::validateImage($_FILES['file']['name']) ){
                $fileValid = false;
            }
            else {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                if ($isFileClean) {
                    $tempArr = array();

                    $tempArr['url'] = '';
                    $tempArr['uid'] = FRMSecurityProvider::generateUniqueId();;
                    $tempArr['pluginKey'] = $pluginKey;
                    $item = $_FILES['file'];

                    try
                    {
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($tempArr['pluginKey'], $item, $tempArr['uid'], array('jpg', 'jpeg', 'png', 'gif'));
                        $tempArr['url'] = $dtoArr['url'];
                    }
                    catch ( Exception $e )
                    {
                        $fileValid = false;
                    }

                    if ($fileValid || !empty($tempArr['url'])) {
                        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE, array('string' => $tempArr['url'])));
                        if (isset($stringRenderer->getData()['string'])) {
                            $tempArr['url'] = $stringRenderer->getData()['string'];
                        }
                        OW::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));

                        $tempArr['href'] = $tempArr['url'];
                        $tempArr['type'] = 'photo';
                        $attachment = json_encode($tempArr);
                        $fileAdded = true;
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file']['name'];
                }
            }
        }

        $text = empty($text) ? '' : trim($text);
        if (empty($text) && !$fileAdded) {
            return array('valid' => false, 'message' => 'authorization_error');
        }


        $checkReplyPostCommentParams = OW::getEventManager()->trigger(new OW_Event('frmcommentplus.check_reply_post_comment_request_params'));
        if(isset($checkReplyPostCommentParams->getData()['errorMessage'])){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $replyId = null;
        if (isset($_POST['replyId'])) {
            $replyId = $_POST['replyId'];
        }

        $replyUserId = null;
        if (isset($_POST['replyUserId'])) {
            $replyUserId = $_POST['replyUserId'];
        }

        $comment = BOL_CommentService::getInstance()->addComment($entityType, $entityId, $pluginKey, $userId, $text, $attachment, $replyId, $replyUserId);
        if ($comment == null) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $comment->message = urldecode($comment->message);
        $commentItem = $this->prepareComment($comment);
        return array('valid' => true, 'message' => 'added', 'item' => $commentItem, 'virus_files' => $virusDetectedFiles, 'fileValid' => $fileValid);
    }

    public function getDashboardFeedEntityType(){
        return array('multiple_photo_upload', 'user-status', 'photo_comments', 'group');
    }

    public function canAccessToComment($entityType, $entityId, $userId){
        if($entityType == null ||
            $entityId == null ||
            $entityType == 'event' ||
            $entityType == 'groups-status'){
            return false;
        }

        if(in_array($entityType, $this->getDashboardFeedEntityType())){
            $activity = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getCreatorActivityOfAction($entityType, $entityId);
            if($activity == null){
                return false;
            }

            $ownerId = $activity->userId;
            $privacy = $activity->privacy;

            if($ownerId == $userId){
                return true;
            }

            if(BOL_UserService::getInstance()->isBlocked($userId, $ownerId) ||
                BOL_UserService::getInstance()->isBlocked($ownerId, $userId)){
                return false;
            }

            if(FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->userAccessUsingPrivacy($privacy, $userId, $ownerId)){
               return true;
            }

        }

        return false;
    }

    public function removeComment(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $cid = null;
        if(isset($_POST['id'])){
            $cid = $_POST['id'];
        }

        if($cid == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $commentInfo = $this->getCommentInfoForDelete($cid);
        if($commentInfo == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        BOL_CommentService::getInstance()->deleteComment($cid);
        return array('valid' => true, 'message' => 'deleted', 'id' => (int) $cid);
    }

    /***
     * @param BOL_Comment $comment
     * @return array|null
     */
    public function getCommentInfoForDeleteByObject($comment)
    {
        if ($comment == null || !isset($comment->id)) {
            return null;
        }

        $userId = OW::getUser()->getId();

        /* @var $commentEntity BOL_CommentEntity */
        $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->getCommentEntityId());

        if ($commentEntity === null )
        {
            return null;
        }

        $isModerator = OW::getUser()->isAuthorized($commentEntity->pluginKey) || OW::getUser()->isAdmin();
        $commentOwner = $userId == $comment->getUserId();

        if ( !$isModerator && !$commentOwner )
        {
            return null;
        }

        return array('comment' => $comment, 'commentEntity' => $commentEntity);
    }

    public function getCommentInfoForDelete($cid)
    {
        if($cid == null){
            return null;
        }
        $comment = BOL_CommentService::getInstance()->findComment($cid);
        if($comment == null){
            return null;
        }

        return $this->getCommentInfoForDeleteByObject($comment);
    }

    /**
     * @param int $commentId
     * @param int $page
     * @param int $count
     * @return array
     */
    private function getCommentReplies($commentId, $page, $count) {
        $commentReplies = BOL_CommentService::getInstance()->findReplyList($commentId, $page, $count);

        $commentRepliesCount = BOL_CommentService::getInstance()->findReplyCount($commentId);
        $result = [
            'repliesCount' => $commentRepliesCount,
            'replies' => $commentReplies
        ];
        return $result;
    }
}