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
class FRMMOBILESUPPORT_BOL_WebServiceStory
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

    public function seenStory() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $id = null;

        if(isset($_POST['id'])){
            $id = $_POST['id'];
        }

        if(!isset($id)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        // TODO: security issue check id in following stories

        STORY_BOL_StorySeenDao::getInstance()->seenStory(OW::getUser()->getId(), $id);
        return array('valid' => true, 'message' => 'successful');
    }

    //all story creat by user
    public function getAllUserStories( $userId=null ){
        if(isset($_POST['userId'])){
            $userId = $_POST['userId'];
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
            $count = (int) $_GET['count'] ?? FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
            $allStories = STORY_BOL_StoryDao::getInstance()->findUserStoriesByCount($userId, $first, $count);
        }else{
            $allStories = STORY_BOL_StoryDao::getInstance()->findUserStories($userId);
        }
        $newAllStories = $this->appendStoryUrl($allStories);
        return array('valid' => true, 'userStoriesList' => $newAllStories);
    }

    public function appendStoryUrl($allStories){
        $thumbnailIds = null;
        if(!empty(array_column( (array) $allStories, 'attachmentId' ))){
            $attachmentIds = array_column($allStories, 'attachmentId' );
            if( !empty(array_column( (array) $allStories, 'thumbnailId' ))) {
                $attachmentIds = array_merge($attachmentIds, array_filter(array_column($allStories, 'thumbnailId')));
            }
        }elseif( !empty(array_column( (array) $allStories, 'categoryAvatar' ))) {
            $attachmentIds = array_column( (array) $allStories, 'categoryAvatar');
        }elseif( !empty(array_column( (array) $allStories, 'storyId' ))) {
            $storyIds = array_column( (array) $allStories, 'storyId');
            $stories = STORY_BOL_StoryDao::getInstance()->findStoriesById($storyIds);
            $attachmentIds = array_column( (array) $stories, 'attachmentId');
            if( !empty(array_column( (array) $stories, 'thumbnailId' ))) {
                $attachmentIds = array_merge($attachmentIds, array_filter(array_column($stories, 'thumbnailId')));
            }
        }else{
            return false;
        }
        $attachmentResults = [];
        if (sizeof($attachmentIds) > 0) {
            $attachmentList = BOL_AttachmentDao::getInstance()->findAttachmentsByIds(array_unique($attachmentIds));
            foreach ($attachmentList as $attachment) {
                $attachmentResults[$attachment->id] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getAttachmentUrl($attachment->fileName);
            }
        }

        $newAllStories =null;
        foreach ($allStories as $storyObject){
            if(isset($storyObject->id) && !isset($storyObject->storyId)){
                $storyObject->storyId = $storyObject->id;
            }
            if(isset($storyObject->thumbnailId)){
                $storyObject->thumbnailUrl = $attachmentResults[$storyObject->thumbnailId];
            }
            if(isset($storyObject->attachmentId) ){
                if(isset($attachmentResults[$storyObject->attachmentId])){
                    $storyObject->url = $attachmentResults[$storyObject->attachmentId];
            }
            }elseif (isset($stories)){
                foreach ($stories as $story){
                    if($story->id == $storyObject->storyId && isset($attachmentResults[$story->attachmentId])){
                        $storyObject->url = $attachmentResults[$story->attachmentId];
                        $storyObject->customStyle = $story->costumeStyles;
                        if(isset($story->thumbnailId)){
                            $storyObject->thumbnailId=$story->thumbnailId;
                        }
                        if(isset($story->thumbnailId)  && isset($attachmentResults[$story->thumbnailId])){
                            $storyObject->thumbnailUrl = $attachmentResults[$story->thumbnailId];
                        }
                    }
                }
            }elseif (isset($storyObject->categoryAvatar)) {
                if (isset($attachmentResults[$storyObject->categoryAvatar])) {
                    $storyObject->avatarUrl = $attachmentResults[$storyObject->categoryAvatar];
                }
            }

            $newAllStories[] = $storyObject;
        }
        return $newAllStories;
    }

    public function seenStories() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $ids = null;

        if(isset($_POST['ids'])){
            $ids = $_POST['ids'];
        }

        if(!isset($ids)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        // TODO: security issue check id in following stories
        $array_of_ids = explode(",",$ids);

        foreach ($array_of_ids as $id){
            $story = STORY_BOL_StoryDao::getInstance()->findById($id);
            if(OW::getUser()->getId() != $story->userId){
                STORY_BOL_StorySeenDao::getInstance()->seenStory(OW::getUser()->getId(), $id);
            }
        }

        return array('valid' => true, 'message' => 'successful');
    }

    public function deleteStory() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $id = null;

        if(isset($_POST['id'])){
            $id = $_POST['id'];
        }

        if(!isset($id)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $story = STORY_BOL_StoryDao::getInstance()->findById($id);
        if(OW::getUser()->getId() != $story->userId){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        STORY_BOL_StoryDao::getInstance()->deleteStory($id);
        return array('valid' => true, 'message' => 'successful');

    }

    public function saveStory(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        $image = null;
        $costumeStyles = null;
        if(isset($_POST['customStyles'])){
            $costumeStyles = $_POST['customStyles'];
        }
        $attachmentIds = [];
        $dangerousFiles = [];
        $thumbnailIds = [];
        if (isset($_FILES['file1'])) {
            $fileIndex = 1;
            while (isset($_FILES['file' . $fileIndex])) {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file' . $fileIndex]['tmp_name']);
                $isThumbFileClean = false;
                if(isset($_FILES['thumbnail' . $fileIndex]['tmp_name'])){
                    $isThumbFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['thumbnail' . $fileIndex]['tmp_name']);
                }
                if ($isFileClean) {
                    $attachmentIds[] = $this->manageStoryAttachment($userId, $_FILES['file' . $fileIndex]);
                    if($isThumbFileClean){
                        $thumbnailIds[] = $this->manageStoryAttachment($userId, $_FILES['thumbnail' . $fileIndex]);
                    }else{
                        $thumbnailIds[] = null;
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file' . $fileIndex]['name'];
                    $dangerousFiles[] = 'file' . $fileIndex;
                }
                $fileIndex++;
            }
        }elseif (isset($_POST['customStyles'])){
            $attachmentIds[] = 0;
        } else{
            return array('status' => false, 'message' => "no_files_attached");
        }

        $attachmentIdsString = "";
        $storyObject = "";
        $thumbnailIndex = 0;
        foreach ($attachmentIds as $attachmentId) {
            $attachmentIdsString .= $attachmentId . ",";
            $thumbnailId = $thumbnailIds[$thumbnailIndex] ?? null;
            $storyObject = STORY_BOL_Service::getInstance()->saveStory(OW::getUser()->getId(), $attachmentId, $costumeStyles, $thumbnailId);
            $thumbnailIndex++;
        }
        if(empty($storyObject)){
            return array('status' => false, 'message' => 'story_has_not_been_added');
        }
        return array('status' => true, 'dangerousFiles' => $dangerousFiles, 'attached files' => $attachmentIdsString, 'storyObject' => $storyObject);
    }

    public function getFollowingStories() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $stories = STORY_BOL_Service::getInstance()->findFollowingStories();
        return array('stories' => $stories);
    }


    public function manageStoryAttachment($userId, $file){
        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);
        $bundle = FRMSecurityProvider::generateUniqueId();
        $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
        $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);

        $fileNameParts = explode(".", $file['name']);
        $fileExtension = end($fileNameParts);
        if(!in_array($fileExtension,$validFileExtensions)){
            return "file_extension_exception";
        }

        $fileSize = $file['size'];
        if($fileSize > $maxUploadSize * 1024 * 1024){
            return "file_size_exception";
        }
        try{
            $attUpload = BOL_AttachmentService::getInstance()->processUploadedFile('story', $file, $bundle, $validFileExtensions, $maxUploadSize);
        } catch (Exception $e){
            return "bundle_exception";
        }
        $attachmentId = $attUpload['dto']->id;
        BOL_AttachmentService::getInstance()->updateStatusForBundle('story',$bundle,1);
        return $attachmentId;
    }

    public function findStorySeens()
    {

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $id = null;

        if(isset($_POST['id'])){
            $id = $_POST['id'];
        }

        if(!isset($id)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $story = STORY_BOL_StoryDao::getInstance()->findById($id);
        if(OW::getUser()->getId() != $story->userId){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $first = (!empty($_GET['first']) && intval($_GET['first']) > 0 ) ? intval($_GET['first']) : 1;
        $userSeens = STORY_BOL_StorySeenDao::getInstance()->findStorySeens($id, $first, 10);
        $userList = array();
        foreach ( $userSeens as $userSeen )
        {
            $avatarSrc = BOL_AvatarService::getInstance()->getAvatarUrl($userSeen->userId);
            $userList[] = array(
                'id' => $userSeen->userId,
                'username' => BOL_UserService::getInstance()->getUserName($userSeen->userId),
                'profileImageUrl' => BOL_AvatarService::getInstance()->getAvatarUrl($userSeen->userId),
                'profileUrl' => BOL_UserService::getInstance()->getUserUrl($userSeen->userId),
                'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($userSeen->userId, $avatarSrc),
            );
        }

        return $userList;
    }
    
    
    public function likeStory() {
        
    
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return array('valid' => false, 'message' => 'plugin_frmlike_not_found');
        }
    
        if(!FRMSecurityProvider::checkPluginActive('story', true)){
            return array('valid' => false, 'message' => 'plugin_story_not_found');
        }
        
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        
        
        $entityType = 'story';
        $entityId = null;
        
        if(isset($_POST['storyId'])){
            $entityId = $_POST['storyId'];
        }
        
        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        

        
        $isStoryActive = STORY_BOL_StoryDao::getInstance()->isActiveStory($entityId);
        $creatorId = null;
        if ($isStoryActive)
        {
            $creatorId = $isStoryActive->userId;

        }
        else
        {
            return array('valid' => false, 'message' => 'story expire');
    
        }
    
        $user_like = FRMLIKE_BOL_Service::getInstance()
            ->findUserLike($entityId , 'story' , OW::getUser()->getId());
        if ($user_like)
        {
            return array('valid' => false, 'message' => 'already liked story');
    
        }
        

        $canLike = STORY_BOL_Service::getInstance()->userCanLikeAction($creatorId);
    
        if (!$canLike)
        {
            return array('valid' => false, 'message' => 'user not in follower list');
        
        }
        
        
        FRMLIKE_BOL_Service::getInstance()->setLike($entityId, $entityType, OW::getUser()->getId());
        
        return array('valid' => true, 'message' => 'liked', 'entityId' => $entityId, 'entityType' => $entityType);
    }
    
    public function unlikeStory() {
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return array('valid' => false, 'message' => 'plugin_frmlike_not_found');
        }
    
        if(!FRMSecurityProvider::checkPluginActive('story', true)){
            return array('valid' => false, 'message' => 'plugin_story_not_found');
        }
    
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
    
    
        $entityType = 'story';
        $entityId = null;
    
        if(isset($_POST['storyId'])){
            $entityId = $_POST['storyId'];
        }
    
        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }
    
    
        $isStoryActive = STORY_BOL_StoryDao::getInstance()->isActiveStory($entityId);
        $creatorId = null;
        if ($isStoryActive)
        {
            $creatorId = $isStoryActive->userId;
        
        }
        else
        {
            return array('valid' => false, 'message' => 'story expire');
        
        }
    
        $user_like = FRMLIKE_BOL_Service::getInstance()
            ->findUserLike($entityId , 'story' , OW::getUser()->getId());
        if (!$user_like)
        {
            return array('valid' => false, 'message' => 'not liked story');
        
        }

        $canUnlike = STORY_BOL_Service::getInstance()->userCanLikeAction($creatorId);
    
        if (!$canUnlike)
        {
            return array('valid' => false, 'message' => 'user not in follower list');
        
        }
        
    
        FRMLIKE_BOL_Service::getInstance()->removeLike($entityId, $entityType, OW::getUser()->getId());
        return array('valid' => true, 'message' => 'unliked', 'entityId' => $entityId, 'entityType' => $entityType);
    }
    
    public function likedUserList()
    {
        if(!FRMSecurityProvider::checkPluginActive('frmlike', true)){
            return array('valid' => false, 'message' => 'plugin_frmlike_not_found');
        }
        
        if(!FRMSecurityProvider::checkPluginActive('story', true)){
            return array('valid' => false, 'message' => 'plugin_story_not_found');
        }
        
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        
        
        $entityType = 'story';
        $entityId = null;
        
        if(isset($_POST['storyId'])){
            $entityId = $_POST['storyId'];
        }
        
        if($entityId == null || $entityType == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        
        $story =  STORY_BOL_StoryDao::getInstance()->findById($entityId);
        if ($story)
        {
            if ($story->userId != OW::getUser()->getId())
            {
                return array('valid' => false, 'message' => 'current user not creator of story');
                
            }
        }
        else
        {
            return array('valid' => false, 'message' => 'story not exist');
            
        }
        
        $voteEntityType = 'frmlike-'.$entityType;
        $likes = BOL_VoteService::getInstance()->findEntityLikes($voteEntityType, $entityId);
        $out = array();
        $out['likes'] = array();
        $out['dislikes'] = array();
        foreach ( $likes as $like )
        {
            /* @var $like BOL_Vote */
            if ($like->getVote() == 1) {
                $out['likes'][] = $like->userId;
            } else if ($like->getVote() == -1) {
                $out['dislikes'][] = $like->userId;
            }
        }
        
        $userList = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()
            ->getUsersInfoByIdList($out['likes']);
        
        
        return array('valid' => true, 'message' => 'likedUserList', 'userList' => $userList);
        
    }
    
    
    
    
    
    
    
}
