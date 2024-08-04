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
class FRMMOBILESUPPORT_BOL_WebServiceFriends
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

    public function getUserFriends($userId){
        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        if(!FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->canUserSeeFeed(OW::getUser()->getId(), $userId)){
            return array();
        }

        if(!FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($userId, 'friends_view', 'friends')){
            return array();
        }

        $friendsData = array();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $friendsFetch = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
            'userId' => $userId,
            'count' => $count,
            'first' => $first
        ));
        $userIds = array();
        if (isset($friendsFetch) && is_array($friendsFetch)) {
            $userIds = $friendsFetch;
        }

        if(sizeof($userIds) == 0){
            return array();
        }

        $users = BOL_UserService::getInstance()->findUserListByIdList($userIds);
        $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($userIds);
        $usersPrivacy = array();
        if (FRMSecurityProvider::checkPluginActive('privacy', true)) {
            $usersPrivacy = PRIVACY_BOL_ActionService::getInstance()->getActionValueListByUserIdList(array('who_post_on_newsfeed'), $userIds);
        }

        foreach ($friendsFetch as $friend){
            $userFriendObject = null;
            foreach ($users as $user){
                if($user->id == $friend){
                    $userFriendObject = $user;
                }
            }

            if($userFriendObject != null) {
                $username = null;
                if(isset($usernames[$userFriendObject->id])){
                    $username = $usernames[$userFriendObject->id];
                }

                $avatarUrl = null;
                if(isset($avatars[$userFriendObject->id])){
                    $avatarUrl = $avatars[$userFriendObject->id];
                }
                $canSendPost = true;
                if (isset($usersPrivacy[$friend]['who_post_on_newsfeed'])) {
                    $privacySendPost = $usersPrivacy[$friend]['who_post_on_newsfeed'];
                    if ($privacySendPost == 'only_for_me' && !OW::getUser()->isAdmin()) {
                        $canSendPost = false;
                    }
                }
                $params['security']['send_post'] = $canSendPost;
                $friendsData[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userFriendObject, $avatarUrl, $username, false, true, $params);
            }
        }
        return $friendsData;
    }

    public function getUserFriendsCount($userId){
        $friendsData = 0;
        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return (int) $friendsData;
        }

        if(FRMSecurityProvider::checkPluginActive('friends', true)){
            $friendsData = FRIENDS_BOL_Service::getInstance()->countFriends($userId);
        }
        return (int) $friendsData;
    }

    public function friendRequest()
    {
        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
            return array("valid" => false);
        }

        $userRequesterId = null;

        if (isset($_GET['userId'])) {
            $userRequesterId = $_GET['userId'];
        }

        if ($userRequesterId == null || !OW::getUser()->isAuthenticated() || !is_numeric($userRequesterId)) {
            return array("valid" => false);
        }

        $userId = OW::getUser()->getId();

        if($userId == $userRequesterId){
            return array("valid" => false);
        }

        if ( BOL_UserService::getInstance()->isBlocked($userRequesterId, $userId) ||
            BOL_UserService::getInstance()->isBlocked($userId, $userRequesterId)){
            return array("valid" => false);
        }

        $request = FRIENDS_BOL_Service::getInstance()->findByRequesterIdAndUserId($userRequesterId, $userId);
        if($request != null && $request->friendId == $userRequesterId &&  $userId== $request->userId){
            $friendshipData = $this->getFriendshipInformation($userId, $userRequesterId);
            return array('valid' => true, 'message' => 'already_send_request', 'friendship' => $friendshipData);
        }

        FRIENDS_BOL_Service::getInstance()->request($userId, $userRequesterId);
        $friendshipData = $this->getFriendshipInformation($userId, $userRequesterId);
        return array("valid" => true, 'message' => 'send_request', 'friendship' => $friendshipData);
    }

    public function cancelRequest()
    {
        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
            return array("valid" => false);
        }

        $userId = null;

        if (isset($_GET['userId'])) {
            $userId = $_GET['userId'];
        }

        if ($userId == null || !OW::getUser()->isAuthenticated()) {
            return array("valid" => false);
        }

        FRIENDS_BOL_Service::getInstance()->cancel(OW::getUser()->getId(), $userId);
        $friendshipData = $this->getFriendshipInformation(OW::getUser()->getId(), $userId);
        return array("valid" => true, 'id' => (int) $userId, 'friendship' => $friendshipData);
    }

    public function acceptFriendRequest(){
        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return array("valid" => false);
        }

        $userRequesterId = null;

        if(isset($_GET['requesterId'])){
            $userRequesterId = $_GET['requesterId'];
        }

        if($userRequesterId == null || !OW::getUser()->isAuthenticated()){
            return array("valid" => false);
        }

        $userId = OW::getUser()->getId();


        if ( BOL_UserService::getInstance()->isBlocked($userRequesterId, $userId) ||
            BOL_UserService::getInstance()->isBlocked($userId, $userRequesterId)){
            return array("valid" => false);
        }

        $request = FRIENDS_BOL_Service::getInstance()->findByRequesterIdAndUserId($userRequesterId, $userId);
        if($request != null && $request->friendId == $userId && $userRequesterId == $request->userId){
            FRIENDS_BOL_Service::getInstance()->accept($userId, $userRequesterId);
            $event = new OW_Event('friends.request-accepted', array(
                'senderId' => $userRequesterId,
                'recipientId' => $userId,
                'time' => time()
            ));
            OW::getEventManager()->trigger($event);
            $friendshipData = $this->getFriendshipInformation($userId, $userRequesterId);
            return array('valid' => true,'id' => (int) $userRequesterId, 'friendship' => $friendshipData);
        }

        return array('valid' => false);
    }


    public function isFriend($user1Id, $user2Id){
        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return true;
        }

        $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($user1Id, $user2Id);
        if (isset($isFriends) && $isFriends->status == 'active') {
            return true;
        }

        return false;
    }

    public function getFriendshipInformation($user1Id, $user2Id){

        /**
         * friendship
         * 0 (not load),
         * 1 (is friend),
         * 2 (is not friend),
         * 3 (send request from itself),
         * 4 (send request from the other side)
         */

        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return 1;
        }

        $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($user1Id, $user2Id);
        if (isset($isFriends) && $isFriends->status == 'active') {
            return 1;
        }

        $request = FRIENDS_BOL_Service::getInstance()->findByRequesterIdAndUserId($user2Id, $user1Id);
        if($request != null && $request->friendId == $user1Id && $user2Id == $request->userId){
            return 4;

        } else if($request != null && $request->friendId == $user2Id && $user1Id == $request->userId){
            return 3;

        }

        return 2;
    }

    public function getFriendshipsInformation($user1Id, $user2IdList) {

        /**
         * friendship
         * 0 (not load),
         * 1 (is friend),
         * 2 (is not friend),
         * 3 (send request from itself),
         * 4 (send request from the other side)
         */

        $result = array();
        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
            foreach ($user2IdList as $userId2) {
                $result[$userId2] = 1;
            }
            return $result;
        }

        $isFriendsData = FRIENDS_BOL_Service::getInstance()->findFriendshipsInfo($user2IdList, $user1Id);
        foreach ($isFriendsData as $isFriends) {
            /** @var FRIENDS_BOL_Friendship $isFriends */
            if (isset($isFriends) && $isFriends->status == 'active') {
                $userId = $isFriends->userId == $user1Id ? $isFriends->friendId : $isFriends->userId;
                $result[$userId] = 1;
            } else if ($isFriends != null && $isFriends->friendId == $user1Id && in_array($isFriends->userId, $user2IdList)) {
                $result[$isFriends->userId] = 4;
            } else if ($isFriends != null && in_array($isFriends->friendId, $user2IdList) && $user1Id == $isFriends->userId) {
                $result[$isFriends->friendId] = 3;
            }
        }

        foreach ($user2IdList as $userId2) {
            if (!array_key_exists($userId2, $result)) {
                $result[$userId2] = 2;
            }
        }

        return $result;
    }

    public function removeFriend(){
        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return array("valid" => false);
        }

        $friendId = null;

        if(isset($_GET['friendId'])){
            $friendId = $_GET['friendId'];
        }

        if($friendId == null || !OW::getUser()->isAuthenticated()){
            return array("valid" => false);
        }

        $userId = OW::getUser()->getId();

        if($friendId == $userId){
            return array("valid" => false);
        }

        FRIENDS_BOL_Service::getInstance()->onCancelFriendshipRequest($friendId,$userId);

        $event = new OW_Event('friends.cancelled', array(
            'senderId' => $friendId,
            'recipientId' => $userId
        ));

        OW::getEventManager()->trigger($event);
        $friendshipData = $this->getFriendshipInformation($userId, $friendId);
        return array('valid' => true, 'friendship' => $friendshipData);
    }
}