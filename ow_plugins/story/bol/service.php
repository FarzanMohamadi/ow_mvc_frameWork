<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class STORY_BOL_Service
{
    private static $classInstance;
    private $storySeenDao;
    private $storyDao;

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
        $this->storySeenDao = STORY_BOL_StorySeenDao::getInstance();
        $this->storyDao= STORY_BOL_StoryDao::getInstance();
    }

    public function saveStory($userId, $attachmentId, $costumeStyles=null, $thumbnailId = null) {

        return $this->storyDao->saveStory($userId, $attachmentId,$costumeStyles,$thumbnailId);
    }

    /***
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findFollowingStories($isProfileStories = false){

        $followingUsersIds = [];

        if($isProfileStories){
            $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
            if(!$guestAccess){
                return array('valid' => false, 'message' => 'guest_cant_view');
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
            $followingUsersIds [] = $userId;
        } else{
            $followingUsers = NEWSFEED_BOL_FollowDao::getInstance()->findUserFollowingList(OW::getUser()->getId());
            $currentUserId = OW::getUser()->getId() . '';
            $followingUsersIds [] = $currentUserId;
            foreach ($followingUsers as $followingUser) {
                if(!in_array($followingUser->feedId, $followingUsersIds)){
                    $followingUsersIds [] = $followingUser->feedId;
                }
            }
        }

        $followingStories = $this->storyDao->findFollowingStories($followingUsersIds);
        $userSeenStories = $this->storySeenDao->findUserStoriesSeen(OW::getUser()->getId());

        $attachmentIds = [];
        $usersStories = [];

        foreach ($followingStories as $followingStory) {
            $attachmentIds[] = $followingStory->attachmentId;
        }

        $attachmentResults = [];
        if (sizeof($attachmentIds) > 0) {
            $attachmentList = BOL_AttachmentDao::getInstance()->findAttachmentsByIds($attachmentIds);
            foreach ($attachmentList as $attachment) {
                $attachmentResults[$attachment->id] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getAttachmentUrl($attachment->fileName);
            }
        }

        foreach ($followingUsersIds as $userId) {
            $stories = [];
            $isAllStoriesSeen = true;

            foreach ($followingStories as $followingStory) {
                if($userId == $followingStory->userId) {

                    $totalSeen = 0;
                    if($currentUserId == $userId){
                        $totalSeen = $this->storySeenDao->findStorySeenCount($followingStory->id);
                    }

                    $seen = false;
                    foreach ($userSeenStories as $userSeenStory) {
                        if ($userSeenStory->storyId == $followingStory->id) {
                            $seen = true;
                        }
                    }
                    if(!$seen){
                        $isAllStoriesSeen = false;
                    }
                    $attachmentUrl = OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl(). 'img/' . 'default.svg';
                    if(array_key_exists($followingStory->attachmentId, $attachmentResults)){
                        $attachmentUrl = $attachmentResults[$followingStory->attachmentId];
                    }
                    $likesArray = BOL_VoteService::getInstance()
                        ->findEntityLikes('story', $followingStory->id);
    
                    $user_like = FRMLIKE_BOL_Service::getInstance()
                        ->findUserLike($followingStory->id , 'story' , OW::getUser()->getId());
    
    
                    $stories[] = array(
                        'attachmentId' => $followingStory->attachmentId,
                        'id' => $followingStory->id,
                        'userId' => $followingStory->userId,
                        'createdAt' => $followingStory->createdAt,
                        'isSeen' => $seen,
                        'url' => $attachmentUrl,
                        'type' => 'TODO',
                        'duration' => 'TODO',
                        'totalSeen' => $totalSeen,
                        'customStyles' => $followingStory->costumeStyles,
                        'likeCount'=> sizeof($likesArray),
                        'isLiked'=>$user_like ? true : false,
                    );
                }
            }
            if(sizeof($stories) > 0) {
                $avatarSrc = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
                $usersStories[] = array(
                    'username' => BOL_UserService::getInstance()->getUserName($userId),
                    'profileImageUrl' => BOL_AvatarService::getInstance()->getAvatarUrl($userId),
                    'profileUrl' => BOL_UserService::getInstance()->getUserUrl($userId),
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($userId, $avatarSrc),
                    'isSeen' => $isAllStoriesSeen,
                    'stories' => $stories
                );
            }
        }
        return $usersStories;
    }

    public function createNewHighlightCategoryWithHighlights($userId, $categoryLabel, $categoryAvatar=null, $highlightList){
        if(isset($categoryAvatar)){
            $avatar = $categoryAvatar;
        }else{
            $avatar = $highlightList[0];
        }
        $highlightCategory = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->addNewHighlightCategory($userId, $categoryLabel, $avatar);
        foreach ($highlightList as $highlight){
            STORY_BOL_StoryHighlightsDao::getInstance()->addHighlight($userId, $highlight, $highlightCategory->id);
        }
        return $highlightCategory;
    }

    public function editHighlightCategoryWithHighlights($categoryId,$list,$categoryTitle,$avatarId){
        STORY_BOL_StoryHighlightsDao::getInstance()->removeHighlightsByCategoryId($categoryId);
        $categoryObject = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->findById($categoryId);
        if(!isset($categoryObject)){
            return False;
        }
        if( isset($categoryTitle) ){ $categoryObject->categoryTitle = $categoryTitle;}
        if( isset($avatarId) ){ $categoryObject->categoryAvatar = $avatarId; }
        if( isset($categoryTitle) || isset($avatarId) ){
            STORY_BOL_StoryHighlightCategoriesDao::getInstance()->save($categoryObject);
        }
        $userId = OW::getUser()->getId();
        foreach ($list as $storyId){
            STORY_BOL_StoryHighlightsDao::getInstance()->addHighlight($userId, $storyId, $categoryObject->id);
        }
        return True;
    }
    
    
    public function userCanLikeAction($userId)
    {
        
    
    
        //if current user follower of creator
        $isFollower = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->isUserFollower($userId);
    
        if (($userId==OW::getUser()->getId())||($isFollower))
        {
            return true;
        
        }
        else{
            return false;
    
        }
    }


}
