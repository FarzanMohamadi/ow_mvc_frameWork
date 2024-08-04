<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceGroup
{
    private static $classInstance;
    const CHUNK_SIZE = 100000;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function leave(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();

        if (!isset($_GET['groupId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $userInvitedBefore = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);
        if($userInvitedBefore != null) {
            if($group->userId == $userId){
                GROUPS_BOL_Service::getInstance()->deleteGroup($groupId);
            }else{
                $userIds = array($userId);
                $deleteUser = GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userIds);
                if (!$deleteUser) {
                    return array('valid' => true, 'leavable' => false);
                }
            }
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
            if($group == null){
                return array('valid' => true, 'leavable' => true, 'group' => null);
            }
            $groupData = $this->getGroupInformation($group);
            return array('valid' => true, 'leavable' => true, 'group' => $groupData);
        }
        return array('valid' => false, 'message' => 'authorization_error');
    }

    public function getUnreadGroupsCount() {
        if (!OW::getUser()->isAuthenticated()) {
            return 0;
        }

        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return 0;
        }
        return (int) GROUPS_BOL_Service::getInstance()->getUnreadGroupsCountForUser(OW::getUser()->getId());
    }

    public function deleteGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!isset($_GET['groupId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $isOwner = OW::getUser()->getId() == $group->userId;
        $isModerator = OW::getUser()->isAuthorized('groups');
        $isAdmin = OW::getUser()->isAdmin();

        $isManager=false;
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId' => $group->id));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isManager=$eventIisGroupsPlusManager->getData()['isUserManager'];
        }
        if ( !$isOwner && !$isModerator  && !$isManager && !$isAdmin )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $check_approval = GROUPS_BOL_Service::getInstance()->ifGroupIsApprovalCanDeletedByUser($group->status);
        if(!$check_approval)
        {
            return array('valid' => false, 'message' => OW::getLanguage()->text('groups', 'delete_unapproved_group_error'));
        }
        GROUPS_BOL_Service::getInstance()->deleteGroup($group->id);

        return array('valid' => true, 'leavable' => true, 'groupId' => $group->id);
    }

    public function acceptInvite(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $accepterUserId = OW::getUser()->getId();

        if (!isset($_GET['groupId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupId, $accepterUserId);
        if($invite != null ){
            GROUPS_BOL_Service::getInstance()->addUser($groupId, $accepterUserId);
        } else {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        return array('valid' => true, 'registration' => true, 'id' => (int) $group->id, 'group' => $this->getGroupInformation($group));
    }


    public function removeUser(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = null;
        $groupId = null;

        if (!isset($_GET['userId']) || !isset($_GET['groupId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $userId = $_GET['userId'];
        $groupId = $_GET['groupId'];
        return $this->removeUserById($userId, $groupId);
    }

    public function removeUserById($userId, $groupId){
        if($userId == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if($groupId == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if($userId == OW::getUser()->getId() || $group->userId == $userId){
            return array('valid' => false, 'message' => 'admin_delete_error');
        }

        $canEdit = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if($canEdit){
            $groupUser = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);
            if($groupUser == null){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $userIds = array($userId);
            $result = GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userIds);
            if ($result == false) {
                return array('valid' => false, 'message' => 'authorization_error', 'leavable' => false);
            }
            $groupInfo = $this->getGroupInformation($group);
            return array('valid' => true, 'message' => 'deleted', 'group' => $groupInfo);
        }
        return array('valid' => false, 'message' => 'authorization_error');
    }

    public function cancelInvite(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $accepterUserId = OW::getUser()->getId();

        if (!isset($_GET['groupId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupId, $accepterUserId);
        if($invite != null ){
            GROUPS_BOL_Service::getInstance()->deleteInvite($groupId, $accepterUserId);
        } else {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        return array('valid' => true, 'registration' => false, 'id' => (int) $group->id );
    }

    public function inviteUser(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }


        $inviterUserId = OW::getUser()->getId();
        $enableQRSearch = (boolean)OW::getConfig()->getValue('groups','enable_QRSearch');
        if ( (!$enableQRSearch && !isset($_GET['userId'])) || (!isset($_GET['userId']) && !isset($_POST['questions']) && !isset($_POST['accountType'])) || !isset($_GET['groupId']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }
        
        if (!isset($_GET['userId'])) {
            return $this->inviteUsersByQuestion();
        }
        
        $userId = $_GET['userId'];
        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!OW::getUser()->isAdmin()){
            if(!GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($group->id)){
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }

        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess'])){
            $hasAccess=true;
        }
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']==true){
            $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically',array('groupId'=>$groupId,'userIds'=>array($userId)));
            OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);
            return array('valid' => true, 'result_key' => 'add_automatically');
        }else {
            if (isset($hasAccess)) {
                $result = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $userId, $inviterUserId);
                return array('valid' => $result);
            }
            if(FRMSecurityProvider::checkPluginActive('friends', true)) {
                $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $inviterUserId);
                if (isset($isFriends) && $isFriends->status == 'active') {
                    $result = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $userId, $inviterUserId);
                    return array('valid' => $result);
                }
            }
        }
        return array('valid' => false, 'message' => 'input_error');
    }

    public function inviteUsers(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }


        $inviterUserId = OW::getUser()->getId();
        $enableQRSearch = (boolean)OW::getConfig()->getValue('groups','enable_QRSearch');
        if ( (!$enableQRSearch && !isset($_POST['userIds'])) || (!$_POST['userIds'] && !isset($_POST['questions']) && !isset($_POST['accountType'])) || !isset($_GET['groupId']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        if (!isset($_POST['userIds'])) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $userIds = (array) json_decode( $_POST['userIds'] );
        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!OW::getUser()->isAdmin()){
            if(!GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($group->id)){
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }

        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess'])){
            $hasAccess=true;
        }
        $final_result = array();
        foreach ($userIds as $userId){
            $result = false;
            if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']==true){
                $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically',array('groupId'=>$groupId,'userIds'=>array($userId)));
                OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);
            }else {
                if (isset($hasAccess)) {
                    $result = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $userId, $inviterUserId);
                }
                if(FRMSecurityProvider::checkPluginActive('friends', true)) {
                    $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $inviterUserId);
                    if (isset($isFriends) && $isFriends->status == 'active') {
                        $result = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $userId, $inviterUserId);
                    }
                }
            }
            $final_result[$userId] = $result;
        }
        return array('valid' => true, 'results' => $final_result);
    }

    public function getInvitableUsers(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $currentUserId = OW::getUser()->getId();

        if ( !isset($_GET['groupId']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_GET['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!OW::getUser()->isAdmin()){
            if(!GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($group->id)){
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $key = '';
        if(isset($_GET['key'])){
            $key = $_GET['key'];
        }

        $idList = GROUPS_BOL_Service::getInstance()->getInvitableUserIds($groupId, $currentUserId);
        $users = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->populateInvitableUserList($idList, $key, $first, $count);
        return $users;
    }

    /**
     * @param $type
     * @param null $groupType
     * @param null $searchValue
     * @return array
     */
    public function getGroups($type,$groupType=null,$searchValue=null){
        if($type != "latest"){
            return array();
        }
        $userId = null;
        if(isset($_GET['userId'])){
            $userId = $_GET['userId'];
        }else if(isset($_GET['username'])){
            $user = BOL_UserService::getInstance()->findByUsername($_GET['username']);
            if($user != null){
                $userId = $user->getId();
            }
        }

        if(OW::getUser()->isAuthenticated() && isset($_GET['my']) && $_GET['my']){
            $userId = OW::getUser()->getId();
        }

        $data = $this->getGroupsByUserId($userId, $type,$groupType,$searchValue);
        return $data;
    }


    public function prepareGetSearchValue()
    {
        $search = null;
        if (isset($_GET['searchValue'])) {
            $search = $_GET['searchValue'];
        }
        $search = UTIL_HtmlTag::stripTagsAndJs($search);
        if (empty($search)) {
            $search = null;
        }
        return $search;
    }

    public function getGroupsByUserId($userId = null, $type = 'latest',$groupType=null,$searchValue=null){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        if ( !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if($userId != null){
            $checkPrivacy = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($userId, 'view_my_groups', 'groups');
            if(!$checkPrivacy){
                return array();
            }
        }

        $groups = array();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        if($userId == null){
            $groups = GROUPS_BOL_Service::getInstance()->findGroupList($type, $first, $count, true);
        }else{
            $groups = GROUPS_BOL_Service::getInstance()->findUserGroupList($userId, $first, $count,true,null,'active',$groupType,$searchValue);
        }

        $groupsAdditionalInfo = array(
            'checkCanView' => false,
        );
        if ($userId == null || $userId == OW::getUser()->getId()) {
            $groupsAdditionalInfo['checkUserExistInGroup'] = false;
        }
        $data = $this->getGroupsInformation($groups, 0, 2, array(), $groupsAdditionalInfo);

        return $data;
    }

    public function getGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        if ( !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupId = null;
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if($groupId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error', 'id' => $groupId);
        }

        $cachedActionIds = array();
        if (isset($_POST['cachedActionIds'])) {
            $cachedActionIds = $_POST['cachedActionIds'];
            $cachedActionIds = explode(',', $cachedActionIds);
            if (sizeof($cachedActionIds) > 0 && $cachedActionIds[sizeof($cachedActionIds) - 1] === '') {
                unset($cachedActionIds[sizeof($cachedActionIds) - 1]);
            }
        }

        $additionalInfo = array();
        if (sizeof($cachedActionIds) > 0) {
            $cachedActions = array();
            $tempCachedActions = NEWSFEED_BOL_ActionDao::getInstance()->findByIdList($cachedActionIds);
            foreach ($tempCachedActions as $tempCachedAction) {
                $actionDataJson = null;
                if(isset($tempCachedAction->data)){
                    $actionDataJson = $tempCachedAction->data;
                }

                if($actionDataJson != null){
                    $actionDataJson = json_decode($actionDataJson);
                }

                if($actionDataJson != null && isset($actionDataJson->contextFeedId) && $actionDataJson->contextFeedId == $groupId && isset($actionDataJson->contextFeedType) && $actionDataJson->contextFeedType == 'groups'){
                    $cachedActions[] = $tempCachedAction;
                }
            }
            if (sizeof($cachedActions) > 0) {
                $additionalInfo['doPrepareActions'] = $cachedActions;
            }
        }

        $data = $this->getGroupInformation($group, $first, $count, array('users', 'files', 'posts', 'subGroups'), $additionalInfo);
        if(FRMSecurityProvider::checkPluginActive('forum', true)) {
            $data['topics'] = $this->getGroupTopics($group);
        }

        $data['can_edit_group'] = false;
        if ( GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group) )
        {
            $data['can_edit_group'] = true;
        }

        OW::getEventManager()->trigger(new OW_Event('newsfeed.feed.render', array('feedType' => 'groups', 'feedId' => $groupId)));

        if (sizeof($cachedActionIds) > 0 && isset($data['posts']) && sizeof($data['posts']) > 0) {
            $deletedActionIds = array();
            $existActionIds = array();
            foreach ($data['posts'] as $post) {
                $existActionIds[] = $post['actionId'];
            }
            foreach ($cachedActionIds as $cachedActionId) {
                if (!in_array($cachedActionId, $existActionIds)) {
                    $deletedActionIds[] = $cachedActionId;
                }
            }
            if (sizeof($deletedActionIds) > 0) {
                $data['deletedPostIds'] = $deletedActionIds;
            }
        }

        $data['unreadCount'] = 0;

        $groupRssEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsrss.get.rss.links.for.group', array('groupId'=>$groupId)));
        if(isset($groupRssEvent->getData()['rssLinks'])) {
            $data['rssLinks'] = $groupRssEvent->getData()['rssLinks'];
        }

        $groupsInvitationLinkEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsinvitationlink.get.invitation.links.for.group', array('groupId' => $groupId, 'first' => $first, 'count' => $count)));
        if(isset($groupsInvitationLinkEvent->getData()['invitationLinks'])) {
            $canSeeLink = $groupsInvitationLinkEvent->getData()['canSeeLinks'];

            if($canSeeLink){
                $data['invitationLinks'] = $groupsInvitationLinkEvent->getData()['invitationLinks'];
            } else{
                $data['invitationLinks'] = array();
            }
            $data['canSeeLinks'] = $canSeeLink;
            $data['canAddLinks'] = $groupsInvitationLinkEvent->getData()['canAddLinks'];
        }

        return $data;
    }

    public function getGroupTopics($group) {
        if ( $group == null)
        {
            return array();
        }
        if (!GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group))
        {
            return array();
        }

        $entity = 'groups';
        $entityId = $group->id;
        $forumService = FORUM_BOL_ForumService::getInstance();
        $forumGroup = $forumService->findGroupByEntityId($entity, $entityId);
        if ( empty($forumGroup) )
        {
            return array();
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }
        $page = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageNumber($first);

        $topicList = $forumService->getGroupTopicList($forumGroup->getId(), $page, $count);
        $topicsData = array();
        foreach ($topicList as $topic){
            $topicsData[] = FRMMOBILESUPPORT_BOL_WebServiceForum::getInstance()->preparedTopic($topic);
        }

        return $topicsData;
    }

    public function canAddTopicToGroup($groupId, $info = array()){

        $params = array('entity' => 'groups', 'entityId' => (int) $groupId, 'action' => 'add_topic', 'info' => $info);
        $event = new OW_Event('forum.check_permissions', $params);
        OW::getEventManager()->trigger($event);

        $canAdd = $event->getData();

        if (isset($info['canCreatePost'])) {
            if ($info['canCreatePost'] == false) {
                $canAdd=false;
            }
        } else {
            $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',
                array('groupId'=>$groupId)));
            $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
            if(isset($isChannelParticipant) && $isChannelParticipant){
                $canAdd=false;
            }
        }

        $canCreateTopic = false;
        if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)){
            if (isset($info['groupSetting'])) {
                $groupSetting = $info['groupSetting'];
            } else {
                $groupSetting = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance()->findByGroupId($groupId);
            }
            if (isset($groupSetting)){
                if($groupSetting->whoCanCreateTopic == FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS)
                {
                    $canCreateTopic = true;
                }
            }
        }

        if ($canCreateTopic) {
            return true;
        }

        return $canAdd;
    }

    public function canUserCreateGroup(){
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'create') ){
            return false;
        }

        return true;
    }

    public function canManageRssGroup(){
        if(!FRMSecurityProvider::checkPluginActive('frmgroupsrss', true)){
            return false;
        }
        return FRMGROUPSRSS_BOL_Service::getInstance()->canManageRssGroups();
    }

    public function canUserAccessWithEntity($entityType, $entityId){
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }
        $activity = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getCreatorActivityOfAction($entityType, $entityId);
        if($activity == null){
            return false;
        }
        $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id));
        $group = null;
        foreach ($feedIdFromActivities as $feedFromActivity){
            if($feedFromActivity->feedType=="groups"){
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($feedFromActivity->feedId);
            }
        }
        if($group == null){
            return false;
        }
        if ( !GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group) )
        {
            return false;
        }

        return true;
    }

    public function removeGroupManager(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('frmgroupsplus', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $managerId = null;
        $groupId = null;
        if (isset($_POST['userId'])){
            $managerId = (int) $_POST['userId'];
        }
        if (isset($_POST['groupId'])){
            $groupId = (int) $_POST['groupId'];
        }
        if($managerId == null || $groupId == null || !OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $canManage = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if (!$canManage) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupUser = GROUPS_BOL_Service::getInstance()->findUser($groupId, $managerId);
        if($groupUser == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $isManager = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagerByUidAndGid($groupId, $managerId);
        if ($isManager) {
            FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->deleteGroupManagerByUidAndGid($groupId, [$managerId]);
        }

        return array('valid' => true, 'userId' => (int) $managerId, 'groupId' => (int) $groupId);
    }

    public function addGroupManager(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('frmgroupsplus', true);
        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $managerId = null;
        $groupId = null;
        if (isset($_POST['userId'])){
            $managerId = (int) $_POST['userId'];
        }
        if (isset($_POST['groupId'])){
            $groupId = (int) $_POST['groupId'];
        }
        if($managerId == null || $groupId == null || !OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $canManage = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if (!$canManage) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupUser = GROUPS_BOL_Service::getInstance()->findUser($groupId, $managerId);
        if($groupUser == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $isManager = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagerByUidAndGid($groupId, $managerId);
        if (!$isManager) {
            FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->addUserAsManager($groupId, $managerId);
        }

        return array('valid' => true, 'userId' => (int) $managerId, 'groupId' => (int) $groupId);
    }

    public function addFile(){
        if ( !isset($_POST['groupId']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $groupId = $_POST['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if(!$this->canAddFile($group)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (isset($_FILES['file']) && isset($_FILES['file']['tmp_name'])) {
            $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
            if (!$isFileClean) {
                return array('valid' => false, 'message' => 'virus_detected');
            }
        }

        $resultArr = FRMGROUPSPLUS_BOL_Service::getInstance()->manageAddFile($groupId, $_FILES['file'], false);
        if(!isset($resultArr) || !$resultArr['result']){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        OW::getEventManager()->call('frmfilemanager.after_file_upload',
            array('entityType'=>'groups', 'entityId'=>$groupId, 'dto'=>$resultArr['dtoArr']['dto'], 'file' => $_FILES['file']));

        $filesList = FRMGROUPSPLUS_BOL_Service::getInstance()->findFileList($group->id, 0, 1);
        $filesInformation = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->preparedFileList($group, $filesList);

        return array('valid' => true, 'files' => $filesInformation);
    }

    public function deleteFile(){
        if ( !isset($_POST['groupId']) || !isset($_POST['id']) )
        {
            return false;
        }

        $groupId = $_POST['groupId'];
        $attachmentId = $_POST['id'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $canDeleteGroupFile = $this->canDeleteFile($group);
        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        $canDeleteFile = true;
        if ($attachment->userId != OW::getUser()->getId()) {
            $canDeleteFile = false;
        }
        if(!$canDeleteFile && !$canDeleteGroupFile){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        try {
            FRMGROUPSPLUS_BOL_Service::getInstance()->deleteFileForGroup($groupId, $attachmentId);
            return array('valid' => true, 'id' => (int) $attachmentId);
        }
        catch (Exception $e){
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function editFile(){
        if ( !isset($_POST['groupId']) || !isset($_POST['id']) )
        {
            return false;
        }

        $groupId = $_POST['groupId'];
        $attachmentId = $_POST['id'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if ($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $canDeleteGroupFile = $this->canDeleteFile($group);
        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        $canDeleteFile = true;
        if ($attachment->userId != OW::getUser()->getId()) {
            $canDeleteFile = false;
        }
        if(!$canDeleteFile && !$canDeleteGroupFile){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        try {
            $new_name = isset($_POST['new_name'])?$_POST['new_name']:null;
            $new_parent_id = isset($_POST['new_parent_id'])?$_POST['new_parent_id']:null;
            BOL_AttachmentService::getInstance()->editAttachmentById($attachmentId, $new_name, $new_parent_id);
            return array('valid' => true, 'id' => (int) $attachmentId);
        }
        catch (Exception $e){
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function addDir(){
        if ( !isset($_POST['groupId']) || !isset($_POST['name']) || !isset($_POST['parent_id']) ){
            return array('valid' => false, 'message' => 'input_error');
        }
        if( !FRMSecurityProvider::checkPluginActive('frmfilemanager', true)) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $groupId = $_POST['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!$this->canAddFile($group)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $service->insert($_POST['name'], $_POST['parent_id'], 'directory', time(), '', true, false);
        $subFolders = $service->getSubfolders('groups', (int) $group->id);
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    public function editDir(){
        if ( !isset($_POST['groupId']) || !isset($_POST['id'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        if( !FRMSecurityProvider::checkPluginActive('frmfilemanager', true)) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $groupId = $_POST['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if ($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        // privacy check
        $isOwner = OW::getUser()->getId() == $group->userId;
        $isModerator = OW::getUser()->isAuthorized('groups');
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId' => $group->id));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isModerator=$eventIisGroupsPlusManager->getData()['isUserManager'];
        }
        if ( !$isOwner && !$isModerator )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $new_name = isset($_POST['new_name'])?$_POST['new_name']:null;
        $new_parent_id = isset($_POST['new_parent_id'])?$_POST['new_parent_id']:null;
        $service->editDirById($_POST['id'], $new_name, $new_parent_id);
        $subFolders = $service->getSubfolders('groups', (int) $group->id);
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    public function deleteDir(){
        if ( !isset($_POST['groupId']) || !isset($_POST['id'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        if( !FRMSecurityProvider::checkPluginActive('frmfilemanager', true)) {
            return array('valid' => false, 'message' => 'input_error');
        }
        $groupId = $_POST['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if ($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        // privacy check
        $isOwner = OW::getUser()->getId() == $group->userId;
        $isModerator = OW::getUser()->isAuthorized('groups');
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId' => $group->id));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isModerator=$eventIisGroupsPlusManager->getData()['isUserManager'];
        }
        if ( !$isOwner && !$isModerator )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $service->deleteDirById($_POST['id']);
        $subFolders = $service->getSubfolders('groups', (int) $group->id);
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    /**
     * @param $group GROUPS_BOL_Group
     * @param array $info
     * @return bool
     * @throws Redirect404Exception
     */
    public function canAddFile($group, $info = array()){
        if(!FRMSecurityProvider::checkPluginActive('groups', true) || !FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)){
            return false;
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return false;
        }

        if (!isset($info['checkUserExistInGroup']) || $info['checkUserExistInGroup']) {
            $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId());
            if(!$isUserInGroup){
                return false;
            }
        }

        if ($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return false;
        }

        $canUploadFile = false;
        if (isset($info['groupSetting'])) {
            $groupSetting = $info['groupSetting'];
        } else {
            $groupSetting = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance()->findByGroupId($group->id);
        }
        if (isset($groupSetting)){
            if($groupSetting->whoCanUploadFile == FRMGROUPSPLUS_BOL_Service::WCU_MANAGERS)
            {
                $canUploadFile = true;
            }
        }

        if (isset($info['isChannel'])) {
            $isChannel = $info['isChannel'];
        } else {
            $isChannel = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->isChannel($group->id);
        }

        if (isset($info['isManager'])) {
            $isManager = $info['isManager'];
        } else {
            $isManager = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagerByUidAndGid($group->id, OW::getUser()->getId());
        }

        $isCreator = $group->userId == OW::getUser()->getId() ? true : false;

        if ((isset($isManager) && $isManager) || $isCreator) {
            return true;
        }

        if (!OW::getUser()->isAuthorized('groups') && !OW::getUser()->isAdmin()) {
            return false;
        }

        if ($canUploadFile) {
            return true;
        }

        if (isset($isChannel) && $isChannel){
            return false;
        }

        return true;
    }

    /**
     * @param $group GROUPS_BOL_Group
     * @param array $info
     * @return bool
     * @throws Redirect404Exception
     */
    public function canDeleteFile($group, $info = array()){
        if(!FRMSecurityProvider::checkPluginActive('groups', true) || !FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)){
            return false;
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return false;
        }

        if ( OW::getUser()->isAuthorized('groups') )
        {
            return true;
        }

        if (!isset($info['checkUserExistInGroup']) || $info['checkUserExistInGroup']) {
            $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId());
            if (!$isUserInGroup) {
                return false;
            }
        }

        if ($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return false;
        }

        if (isset($info['isManager'])) {
            $isManager = $info['isManager'];
        } else {
            $isManager = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagerByUidAndGid($group->id, OW::getUser()->getId());
        }

        $isCreator = $group->userId == OW::getUser()->getId() ? true : false;
        return ((isset($isManager) && $isManager) || $isCreator);
    }

    public function isFollowable($groupId, $groupDto = null, $info = array()) {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return false;
        }

        if(!OW::getUser()->isAuthenticated()) {
            return false;
        }

        if ($groupId == null) {
            return false;
        }

        if ($groupDto == null) {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }

        if ( $groupDto === null )
        {
            return false;
        }

        if ($groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE) {
            if (!isset($info['checkUserExistInGroup']) || $info['checkUserExistInGroup']) {
                $userDtoInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId());
                if ($userDtoInGroup == null) {
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    public function isFollower($groupId) {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return false;
        }
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        return NEWSFEED_BOL_Service::getInstance()->isFollow(OW::getUser()->getId(), 'groups', $groupId);
    }

    public function getFollowers($groupsId) {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array();
        }
        if (!OW::getUser()->isAuthenticated()) {
            return array();
        }
        return NEWSFEED_BOL_Service::getInstance()->isFollowByFeedIds(OW::getUser()->getId(), 'groups', $groupsId);
    }

    public function follow() {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $groupId = null;
        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if(!$this->isFollowable($groupId)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => 'groups',
            'feedId' => $groupId
        );
        OW::getEventManager()->call('feed.add_follow', $eventParams);
        return array('valid' => true, 'follow' => true, 'groupId' => $groupId);
    }

    public function unFollow() {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $groupId = null;
        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if(!$this->isFollowable($groupId)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => 'groups',
            'feedId' => $groupId
        );
        OW::getEventManager()->call('feed.remove_follow', $eventParams);
        return array('valid' => true, 'follow' => false, 'groupId' => $groupId);
    }

    public function isReplyFeatureEnable($entityType, $hasStatus = true,$groupId=null, $checkNewsfeedChatFormEvent = true) {
        $otpEventParams['includeWebService'] = true;
        if(isset($groupId))
        {
            $otpEventParams['groupId']= $groupId;
        }
        if ($checkNewsfeedChatFormEvent) {
            $otpEvent = OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form',$otpEventParams));
            if( !isset($otpEvent->getData()['canReply']) || !$otpEvent->getData()['canReply']){
                return false;
            }
        } else {
            $addReplyFeatureConfig = OW::getConfig()->getValue('newsfeed', 'addReply');
            if (!isset($addReplyFeatureConfig) || $addReplyFeatureConfig != "on") {
                return false;
            }
        }
        if (!in_array($entityType, ['groups-status','user-status']) || !$hasStatus) {
            return false;
        }
        return true;
    }

    public function getGroupsInformation($groups, $first = 0, $count = 10, $params = array('users', 'files', 'posts', 'subGroups'), $additionalInfo = array()) {
        $info = array();
        $groupIds = array();

        foreach ($groups as $group) {
            $groupIds[] = $group->id;
        }

        if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
            $additionalInfo['groups_category_information'] = FRMGROUPSPLUS_BOL_Service::getInstance()->getGroupCategoryByGroupIds($groupIds);
            $additionalInfo['groups_channel_ids'] = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->findChannelIds($groupIds);
            $additionalInfo['groups_settings'] = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance()->findByGroupIds($groupIds);
            $additionalInfo['groups_manager_ids'] = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds($groupIds);
            $additionalInfo['current_user_groups_follow'] = $this->getFollowers($groupIds);
            $additionalInfo['groups_last_action_seen'] = GROUPS_BOL_GroupUserDao::getInstance()->findByGroupsAndUserId($groupIds, OW::getUser()->getId());
        }

        foreach ($groups as $group) {
            $info[] = $this->getGroupInformation($group, $first, $count, $params, $additionalInfo);
        }
        return $info;
    }

    public function getGroupInformation($group, $first = 0, $count = 10, $params = array('users', 'files', 'posts', 'subGroups'), $additionalInfo = array()){
        $imageUrl = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
        $imageUrlPath = GROUPS_BOL_Service::getInstance()->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_SMALL);
        $emptyImage = empty($imageUrlPath) ? true : false;
        $description = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($group->description, false);

        $checkCanView = true;
        if (isset($additionalInfo['checkCanView'])) {
            $checkCanView = $additionalInfo['checkCanView'];
        }
        $canView = true;
        if ($checkCanView) {
            $canView = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group);
        }

        $groupOwnerInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($group->userId);
        if (!$canView)
        {
            $invite = GROUPS_BOL_Service::getInstance()->findInvite($group->id, OW::getUser()->getId());
            if ($invite != null){
                $result = array(
                    "id" => (int) $group->id,
                    "invite" => true,
                    "title" => $group->title,
                    "description" => $description,
                    "privacy" => $group->privacy,
                    "userId" => (int) $group->userId,
                    "user" => $groupOwnerInfo,
                    'emptyImage' => $emptyImage,
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $group->id, $imageUrl, 'group'),
                    "timestamp" => $group->timeStamp,
                    "imageUrl" => $imageUrl,
                    "whoCanView" => $group->whoCanView,
                    "whoCanInvite" => $group->whoCanInvite,
                    "status" =>$group->status,
                    "followable" => false,
                    "follower" => false,
                    "whoCanUploadFile" => 'manager',
                    "whoCanCreateTopic" => 'manager',
                );

                $eventFindParent = OW::getEventManager()->trigger(new OW_Event('frmsubgroups.groups_find_parent', array('groupId'=> $group->id) ));
                if(isset($eventFindParent->getData()['parentId'])){
                    $parentId = $eventFindParent->getData()['parentId'];
                    $parentObject = GROUPS_BOL_Service::getInstance()->findGroupById($parentId);
                    if (isset($parentObject)) {
                        $result['parent'] = array(
                            'title' => $parentObject->title,
                            'id' => (int) $parentId,
                        );
                    }
                }

                return $result;
            }
            return array();
        }

        $categoryValue = "";
        $registered = false;
        $whoCanCreateContent = 'group';
        $whoCanUploadFile = 'participant';
        $whoCanCreateTopic = 'participant';
        $usersCount = GROUPS_BOL_Service::getInstance()->findUserListCount($group->id);

        $filesInformation = array();
        $managerIds = array();
        if (isset($additionalInfo['groups_category_information'])) {
            if (isset($additionalInfo['groups_category_information'][$group->id])) {
                $categoryValue = $additionalInfo['groups_category_information'][$group->id]['label'];
            }
        } else {
            if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $categoryId = FRMGROUPSPLUS_BOL_Service::getInstance()->getGroupCategoryByGroupId($group->id);
                if ($categoryId != null) {
                    $category = FRMGROUPSPLUS_BOL_Service::getInstance()->getCategoryById($categoryId);
                    if ($category != null) {
                        $categoryValue = $category->label;
                    }
                }
            }
        }

        if (isset($additionalInfo['groups_channel_ids'])) {
            if (in_array($group->id, $additionalInfo['groups_channel_ids'])) {
                $whoCanCreateContent = 'channel';
            }
        } else {
            if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $isChannel = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->isChannel($group->id);
                if ($isChannel) {
                    $whoCanCreateContent = FRMGROUPSPLUS_BOL_Service::WCC_CHANNEL;
                }
            }
        }

        $groupSetting = null;
        if (isset($additionalInfo['groups_settings'])) {
            if (isset($additionalInfo['groups_settings'][$group->id])) {
                $groupSetting = $additionalInfo['groups_settings'][$group->id];
                $whoCanCreateTopic = $groupSetting->whoCanCreateTopic;
                $whoCanUploadFile = $groupSetting->whoCanUploadFile;
            }
        } else {
            if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $groupSetting = FRMGROUPSPLUS_BOL_GroupSettingDao::getInstance()->findByGroupId($group->id);
                if (isset($groupSetting)) {
                    $whoCanUploadFile = $groupSetting->getWhoCanUploadFile();
                    $whoCanCreateTopic = $groupSetting->getWhoCanCreateTopic();
                }
            }
        }

        if (isset($additionalInfo['groups_manager_ids'])) {
            if (isset($additionalInfo['groups_manager_ids'][$group->id])) {
                $managerIds = $additionalInfo['groups_manager_ids'][$group->id];
            }
        } else {
            if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $managers = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupId($group->id);
                foreach ($managers as $manager) {
                    $managerIds[] = (int)$manager->userId;
                }
            }
        }

        if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)){
            if (in_array('files', $params)) {
                $filesList = FRMGROUPSPLUS_BOL_Service::getInstance()->findFileList($group->id, $first, $count);
                $filesInformation = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->preparedFileList($group, $filesList);
            }
        }

        $users = array();
        $pendingUsers = array();
        $pendingUsersId = array();
        if (in_array('users', $params)) {
            $idList = array();
            $groupUserList = GROUPS_BOL_GroupUserDao::getInstance()->findListByGroupId($group->id, $first, $count);
            foreach ($groupUserList as $groupUser) {
                $idList[] = $groupUser->userId;
            }

            if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)){
                $pendingUsersList = GROUPS_BOL_Service::getInstance()->findAllInviteList($group->id);
                foreach ($pendingUsersList as $pendingUser) {
                    $pendingUsersId[] = $pendingUser->userId;
                    $idList[] = $pendingUser->userId;
                }
            }


            $idList = array_unique($idList);
            $usersObject = BOL_UserService::getInstance()->findUserListByIdList($idList);
            $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($idList);
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList);
            foreach ($usersObject as $userObject) {
                $username = null;
                if (isset($usernames[$userObject->id])) {
                    $username = $usernames[$userObject->id];
                }

                $avatarUrl = null;
                if (isset($avatars[$userObject->id])) {
                    $avatarUrl = $avatars[$userObject->id];
                }
                $userData = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatarUrl, $username);
                $userData['isManager'] = false;
                if (in_array($userObject->id, $managerIds)){
                    $userData['isManager'] = true;
                }
                if (!in_array($userObject->id, $pendingUsersId)) {
                    $users[] = $userData;
                } else {
                    $pendingUsers[] = $userData;
                }
            }
        }

        $canInviteUser = true;
        $isCurrentUserManager = in_array(OW::getUser()->getId(), $managerIds);
        $checkUserExistInGroup = true;
        if (isset($additionalInfo['checkUserExistInGroup'])) {
            $checkUserExistInGroup = $additionalInfo['checkUserExistInGroup'];
        }
        if (!$isCurrentUserManager && !GROUPS_BOL_Service::getInstance()->isCurrentUserInviteByGroupObject($group, false, $checkUserExistInGroup)) {
            $canInviteUser = false;
        }

        $canCreatePost = true;

        if(OW::getUser()->isAuthenticated()){
            if ($checkUserExistInGroup) {
                $registeredUser = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId());
                if($registeredUser != null){
                    $registered = true;
                }
            } else {
                $registered = true;
            }

            if ($whoCanCreateContent == 'channel' && !$isCurrentUserManager) {
                $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',
                    array('feedId'=> $group->id, 'feedType'=> 'groups', 'isChannel' => $whoCanCreateContent == 'channel', 'isManager' => $isCurrentUserManager, 'group' => $group ) ));
                if(isset($channelEvent->getData()['channelParticipant'])){
                    $channelEvent->getData()['channelParticipant'];
                    $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
                    if($isChannelParticipant){
                        $canCreatePost = false;
                    }
                }
            }

        }

        $registrable = false;
        if(!$registered && ($group->whoCanView != 'invite' || OW::getUser()->isAdmin())) {
            $registrable = true;
        }

        $isAdmin = $isCurrentUserManager || GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group, false);
        $groupInfo = array(
            'checkUserExistInGroup' => $checkUserExistInGroup,
            'isChannel' => $whoCanCreateContent == 'channel',
            'isManager' => $isCurrentUserManager,
            'groupSetting' => $groupSetting,
            'canCreatePost' => $canCreatePost,
            'group_object' => $group,
        );
        if (isset($additionalInfo['doPrepareActions'])) {
            $groupInfo['doPrepareActions'] = $additionalInfo['doPrepareActions'];
        }
        $canAddFile = $this->canAddFile($group, $groupInfo);
        $canDeleteFolder = $canDeleteFile = $this->canDeleteFile($group, $groupInfo);
        $canAddTopic = $this->canAddTopicToGroup($group->id, $groupInfo);

        $lastSeen = null;
        if (isset($_GET['lastSeenPostTime']) && $_GET['lastSeenPostTime'] != null && $first == 0) {
            $lastSeen = $_GET['lastSeenPostTime'];
        }

        $unreadCount = GROUPS_BOL_Service::getInstance()->getUnreadCountForGroupUser($group->id, false, $lastSeen);
        $canAddSubGroups = false;
        $eventHasAccess = OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.create.subgroups', array('groupId' => $group->id)));
        if (isset($eventHasAccess->getData()['canCreateSubGroup']) && $eventHasAccess->getData()['canCreateSubGroup']) {
            $canAddSubGroups = true;
        }

        $postCount = max($count, $unreadCount);
        $postCount = min($postCount, FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getCachedGroupPostsSize());
        $postsInformation = $this->getGroupPosts($group->id, $first, $postCount, $groupInfo);

        $lastActivityString = OW::getLanguage()->text('groups','feed_create_string');
        $lastActivityUsername = $groupOwnerInfo['name'];
        $lastActivityTimestamp = $group->timeStamp;
        if($postsInformation != null && sizeof($postsInformation) > 0){
            $find = false;
            foreach ($postsInformation as $postInformation){
                if($find){
                    break;
                }
                $lastActivity = $postInformation;
                if(isset($lastActivity['text']) && !empty($lastActivity['text'])){
                    $find = true;
                    $lastActivityString = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($lastActivity['text'], true, true);
                    if(isset($lastActivity['user']['name'])){
                        $lastActivityUsername = $lastActivity['user']['name'];
                    }

                    if(isset($lastActivity['time'])){
                        $lastActivityTimestamp = $lastActivity['time'];
                    }
                } else if($lastActivity['entityType'] == 'groups-add-file'){
                    $find = true;
                    $lastActivityString = OW::getLanguage()->text('frmmobilesupport', 'add_file_string');
                    if(isset($lastActivity['user']['name'])){
                        $lastActivityUsername = $lastActivity['user']['name'];
                    }

                    if(isset($lastActivity['time'])){
                        $lastActivityTimestamp = $lastActivity['time'];
                    }
                } else if(in_array($lastActivity['entityType'], array('groups-join', 'groups-leave')) && !empty($lastActivity['activityString'])){
                    $find = true;
                    $lastActivityString = $lastActivity['activityString'];
                    if(isset($lastActivity['user']['name'])){
                        $lastActivityUsername = $lastActivity['user']['name'];
                    }

                    if(isset($lastActivity['time'])){
                        $lastActivityTimestamp = $lastActivity['time'];
                    }
                } else if(in_array($lastActivity['entityType'], array('forum-topic', 'forum-post')) && !empty($lastActivity['activityString'])){
                    $find = true;
                    $lastActivityString = $lastActivity['activityString'];
                    if(isset($lastActivity['user']['name'])){
                        $lastActivityUsername = $lastActivity['user']['name'];
                    }

                    if(isset($lastActivity['time'])){
                        $lastActivityTimestamp = $lastActivity['time'];
                    }
                }
            }
        }

        $followable = !$checkUserExistInGroup || $this->isFollowable($group->id, $group, $groupInfo);
        $isFollower = false;
        if (isset($additionalInfo['current_user_groups_follow'])) {
            if (isset($additionalInfo['current_user_groups_follow'][$group->id])) {
                $isFollower = true;
            }
        } else {
            $this->isFollower($group->id);
        }

        $data = array(
            "id" => (int) $group->id,
            "title" => $group->title,
            "description" => $description,
            "privacy" => $group->privacy,
            "unreadCount" => (int) $unreadCount,
            "can_add_file" => $canAddFile,
            "can_add_sub_groups" => $canAddSubGroups,
            "pendingUsers" => $pendingUsers,
            "can_add_topic" => $canAddTopic,
            "can_delete_file" => $canDeleteFile,
            "userId" => (int) $group->userId,
            "user" => $groupOwnerInfo,
            "timestamp" => $group->timeStamp,
            "imageUrl" => $imageUrl,
            'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $group->id, $imageUrl, 'group'),
            'emptyImage' => $emptyImage,
            "categoryValue" => $categoryValue,
            "isAdmin" => $isAdmin,
            "registered" => $registered,
            "registrable" => $registrable,
            "files" => $filesInformation,
            "can_create_post" => $canCreatePost,
            "can_invite_user" => $canInviteUser,
            "users_count" => isset($usersCount)?$usersCount:0,
            "whoCanView" => $group->whoCanView,
            "whoCanInvite" => $group->whoCanInvite,
            "whoCanCreateContent" => $whoCanCreateContent,
            "whoCanUploadFile" => $whoCanUploadFile,
            "whoCanCreateTopic" => $whoCanCreateTopic,
            "lastActivityString" => $lastActivityString,
            "lastActivityUsername" => $lastActivityUsername,
            "lastActivityTimestamp" => $lastActivityTimestamp,
            "users" => $users,
            "followable" => $followable,
            "follower" => $isFollower,
            "status" => $group->status
        );

        if(FRMSecurityProvider::checkPluginActive('coverphoto', true)){
            $data['coverPhoto'] = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('groups', $group->id);
        }

        if(FRMSecurityProvider::checkPluginActive('frmfilemanager', true)){
            if (in_array('files', $params)) {
                $data['subfolders'] = FRMFILEMANAGER_BOL_Service::getInstance()->getSubfolders('groups', (int) $group->id);
                $data['can_delete_folder'] = $canDeleteFolder;
            }
        }

        if (isset($additionalInfo['groups_last_action_seen'][$group->id]->last_seen_action)) {
            $data['lastSeenPostTime'] = (int) $additionalInfo['groups_last_action_seen'][$group->id]->last_seen_action;
        } else {
            $groupUser = GROUPS_BOL_GroupUserDao::getInstance()->findGroupUser($group->id, OW::getUser()->getId());
            if ($groupUser != null) {
                $data['lastSeenPostTime'] = (int) $groupUser->last_seen_action;
            }
        }

        $eventFindParent = OW::getEventManager()->trigger(new OW_Event('frmsubgroups.groups_find_parent', array('groupId'=> $group->id) ));
        if(isset($eventFindParent->getData()['parentId'])){
            $parentId = $eventFindParent->getData()['parentId'];
            $parentObject = GROUPS_BOL_Service::getInstance()->findGroupById($parentId);
            if (isset($parentObject)) {
                $data['parent'] = array(
                    'title' => $parentObject->title,
                    'id' => (int) $parentId,
                );
            }
        }

        if (in_array('posts', $params)) {
            $data["posts"] = $postsInformation;
            $postEntityIds = array();
            foreach ($postsInformation as $postInfo) {
                if (isset($postInfo['entityId'])) {
                    $postEntityIds[] = $postInfo['entityId'];
                }
            }
            if (sizeof($postEntityIds) > 0 && FRMSecurityProvider::checkPluginActive('notifications', true)) {
                $unMarkedNotifications = array();
                $groupPostsNotifications = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findNotificationsByEntityIds('groups-status', $postEntityIds, OW::getUser()->getId());
                if($groupPostsNotifications != null && is_array($groupPostsNotifications)) {
                    foreach ($groupPostsNotifications as $cNotif) {
                        if ($cNotif->viewed != 1) {
                            $unMarkedNotifications[] = $cNotif->id;
                        }
                    }
                }

                $groupFilesNotifications = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findNotificationsByEntityIds('groups-add-file', $postEntityIds, OW::getUser()->getId());
                if($groupFilesNotifications != null && is_array($groupFilesNotifications)) {
                    foreach ($groupFilesNotifications as $cNotif) {
                        if ($cNotif->viewed != 1) {
                            $unMarkedNotifications[] = $cNotif->id;
                        }
                    }
                }

                $groupTopicsNotifications = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findNotificationsByEntityIds('group-topic-add', $postEntityIds, OW::getUser()->getId());
                if($groupTopicsNotifications != null && is_array($groupTopicsNotifications)) {
                    foreach ($groupTopicsNotifications as $cNotif) {
                        if ($cNotif->viewed != 1) {
                            $unMarkedNotifications[] = $cNotif->id;
                        }
                    }
                }

                $groupJoinsNotifications = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findNotificationsByEntityIds('groups-join', $postEntityIds, OW::getUser()->getId());
                if($groupJoinsNotifications != null && is_array($groupJoinsNotifications)) {
                    foreach ($groupJoinsNotifications as $cNotif) {
                        if ($cNotif->viewed != 1) {
                            $unMarkedNotifications[] = $cNotif->id;
                        }
                    }
                }

                if (sizeof($unMarkedNotifications) > 0){
                    NOTIFICATIONS_BOL_NotificationDao::getInstance()->markViewedByIds($unMarkedNotifications);
                }
            }
        }

        if (in_array('subGroups', $params)) {
            $subGroups = array();

            $eventGetSubGroups = new OW_Event('groups_find_subgroups', array('groupId' => $group->id, 'first' => $first, 'count' => $count));
            OW::getEventManager()->trigger($eventGetSubGroups);
            if(isset($eventGetSubGroups->getData()['groups'])){
                $subGroups = $eventGetSubGroups->getData()['groups'];
            }

            $subGroupsData = $this->getGroupsInformation($subGroups, 0, 2, array());

            if (sizeof($subGroupsData) > 0) {
                $data["subGroups"] = $subGroupsData;
            }
        }

        return $data;
    }


    private function getGroupPosts($groupId, $first = 0, $count = 11, $additionalInfo = array()){
        if ($count != 11) {
            $count += 1;
        }
        $params = array(
            "feedType" => "groups",
            "feedId" => $groupId,
            "offset" => $first,
            "displayCount" => $count,
            "displayType" => "action",
            "checkMore" => true,
            "feedAutoId" => "feed1",
            "startTime" => time(),
            "formats" => null,
            "endTIme" => 0,
            "additionalInfo" => $additionalInfo,
        );
        return FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getActionData($params);
    }

    public function getGroupFields(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $fields = array();
        $language = OW::getLanguage();
        $fields[] = array(
            'name' => 'title',
            'type' => 'text',
            'label' => $language->text('groups', 'create_field_title_label'),
            'presentation' => 'text',
            'values' => array()
        );

        $fields[] = array(
            'name' => 'description',
            'type' => 'text',
            'label' => $language->text('groups', 'create_field_description_label'),
            'presentation' => 'text',
            'values' => array()
        );

        $whoCanViewValues[$language->text('groups', 'form_who_can_view_anybody')] = GROUPS_BOL_Service::WCV_ANYONE;
        $whoCanViewValues[$language->text('groups', 'form_who_can_view_invite')] = GROUPS_BOL_Service::WCV_INVITE;
        $fields[] = array(
            'name' => 'whoCanView',
            'type' => 'select',
            'label' => $language->text('groups', 'form_who_can_view_label'),
            'presentation' => 'radio',
            'values' => $whoCanViewValues
        );

        $whoCanInviteValues[$language->text('groups', 'form_who_can_invite_participants')] = GROUPS_BOL_Service::WCI_PARTICIPANT;
        $whoCanInviteValues[$language->text('groups', 'form_who_can_invite_creator')] = GROUPS_BOL_Service::WCI_CREATOR;

        $fields[] = array(
            'name' => 'whoCanInvite',
            'type' => 'select',
            'label' => $language->text('groups', 'form_who_can_invite_label'),
            'presentation' => 'radio',
            'values' => $whoCanInviteValues
        );

        if(FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
            $whoCanCreateContentValues[$language->text('frmgroupsplus', 'form_who_can_create_content_participants')] = FRMGROUPSPLUS_BOL_Service::WCC_GROUP;
            $whoCanCreateContentValues[$language->text('frmgroupsplus', 'form_who_can_create_content_creators')] = FRMGROUPSPLUS_BOL_Service::WCC_CHANNEL;

            $fields[] = array(
                'name' => 'whoCanCreateContent',
                'type' => 'select',
                'label' => $language->text('frmgroupsplus', 'who_can_create_content'),
                'presentation' => 'radio',
                'values' => $whoCanCreateContentValues,
                'required' => false
            );

            $categories = FRMGROUPSPLUS_BOL_Service::getInstance()->getGroupCategoryList();
            if(sizeof($categories) > 0) {
                $values = array();
                $values[null] = OW::getLanguage()->text('frmgroupsplus', 'select_category');
                foreach ($categories as $category) {
                    $values[$category->label] = $category->id;
                }
                $fields[] = array(
                    'name' => 'categoryStatus',
                    'type' => 'select',
                    'label' => $language->text('frmgroupsplus', 'select_category'),
                    'presentation' => 'radio',
                    'values' => $values,
                    'required' => false
                );
            }
        }

        return $fields;
    }

    public function processCreateGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'create') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['title']) && !GROUPS_BOL_Service::getInstance()->isGroupTitleUnique($_POST['title']))
        {
            if(isset($_POST['whoCanCreateContent']) && ($_POST['whoCanCreateContent'] == 'channel')){
                $error_message = "chanel_name_exists";
            } else{
                $error_message = "group_name_exists";
            }
            return array( 'valid' => false, 'message' => $error_message);
        }

        $valid = true;
        $questions = $this->getGroupFields();
        $formValidator = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkDataFormValid($questions);
        if($formValidator['valid'] == true){
            $result = $this->createGroup();
            if($result == null){
                $valid = false;
            }
            if($valid) {
                return array(
                    'valid' => true,
                    'message' => 'group_created',
                    'group' => $this->getGroupInformation($result, 0, 2, array()),
                );
            }
        }else{
            $valid = false;
        }

        if(!$valid){
            return array('valid' => false, 'message' => 'invalid_data');
        }
    }

    public function processEditGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'create') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupId = null;
        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if($groupId == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['title']) && !GROUPS_BOL_Service::getInstance()->isGroupTitleUnique($_POST['title'], $group->id))
        {
            return array( 'valid' => false, 'message' => OW::getLanguage()->text('groups', 'group_already_exists') );
        }

        $isAdmin = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if(!$isAdmin){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $valid = $this->handleRssLinks();
        $questions = $this->getGroupFields();
        $formValidator = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkDataFormValid($questions);
        if($formValidator['valid'] == true && $valid){
            if(!isset($_POST['whoCanCreateContent'])){
                $_POST['whoCanCreateContent'] = 'group';
            }
            $data = $_POST;
            if (isset($_FILES['file'])){
                if ( !empty($_FILES['file']['name']) ){
                    if ( (int) $_FILES['file']['error'] !== 0 ||
                        !is_uploaded_file($_FILES['file']['tmp_name']) ||
                        !UTIL_File::validateImage($_FILES['file']['name']) ){
                        // Do nothing
                    }
                    else{
                        $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                        if ($isFileClean) {
                            $data['image'] = $_FILES['file'];
                        }
                    }
                }
            }
            $result = GROUPS_BOL_Service::getInstance()->processGroupInfo($group, $data);
            if($result == null){
                $valid = false;
            }
            if($valid) {
                return array(
                    'valid' => true,
                    'message' => 'group_edited',
                    'group' => array(
                        'id' => (int) $result->id,
                        'time' => $result->timeStamp,
                        'userId' => (int) $result->userId,
                        'title' => $result->title,
                        'description' => $result->description,
                        'whoCanInvite' => $result->whoCanInvite,
                        'whoCanView' => $result->whoCanView,
                    ));
            }
        }else{
            $valid = false;
        }

        if(!$valid){
            return array('valid' => false, 'message' => 'invalid_data');
        }
    }

    public function changeCoverPhoto($fileName = 'file'){
        if(!FRMSecurityProvider::checkPluginActive('coverphoto', true)){
            return array('valid' => false, 'message' => 'plugin_not_enabled');
        }
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupId = (isset($_GET['groupId']))? (int) $_GET['groupId']: 0;
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null || !GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $resp = COVERPHOTO_BOL_Service::getInstance()->uploadNewCover('groups', $groupId, 'new_cover', $fileName);
        if(!$resp['result']) {
            return array('valid' => false, 'message' => $resp['code']);
        }

        $avatarUrl = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('groups', $groupId);
        return array('valid' => true, 'message' => 'changed', 'Url' => $avatarUrl);
    }

    public function joinGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        if ( !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $groupId = null;
        $userId = OW::getUser()->getId();
        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if($groupId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $findUser = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);
        if($findUser != null){
            return array('valid' => true, 'message' => 'add_before');
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupId, $userId);

        if ( $invite !== null )
        {
            GROUPS_BOL_Service::getInstance()->markInviteAsViewed($groupId, $userId);
        }
        else if ( $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE  && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups'))
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);
        $groupData = $this->getGroupInformation($group);

        return array('valid' => true, 'message' => 'user_add', 'group' => $groupData);
    }

    public function handleRssLinks()
    {
        $canAddRss=false;
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            $canAddRss = true;
        }
        if(FRMSecurityProvider::checkPluginActive('frmgroupsrss', true)
            && isset($_POST['rssLinks'])
            && !empty($_POST['rssLinks']) && $canAddRss)
        {
            $rssLinks = explode (",", $_POST['rssLinks']);
            $validator = new FRMGROUPSRSS_TagInputValidator(FRMGROUPSRSS_BOL_Service::MAXIMUM_RSS_COUNT_FOR_EACH_GROUP);
            $isRssValid = $validator->isValid($rssLinks);
            if(!$isRssValid){
                return false;
            }
            $_POST['rssLinks'] = $rssLinks;
        }
        return true;
    }

    public function createGroup(){
        if(!OW::getUser()->isAuthenticated()){
            return null;
        }

        if(!in_array($_POST['whoCanInvite'], array(GROUPS_BOL_Service::WCI_CREATOR, GROUPS_BOL_Service::WCI_PARTICIPANT))) {
            return null;
        }

        if(!in_array($_POST['whoCanView'], array(GROUPS_BOL_Service::WCV_ANYONE, GROUPS_BOL_Service::WCV_INVITE))) {
            return null;
        }

        if(!$this->handleRssLinks())
        {
            return null;
        }

        $data = $_POST;
        if (isset($_FILES['file'])){
            if ( !empty($_FILES['file']['name']) ){
                if ( (int) $_FILES['file']['error'] !== 0 ||
                    !is_uploaded_file($_FILES['file']['tmp_name']) ||
                    !UTIL_File::validateImage($_FILES['file']['name']) ){
                    // Do nothing
                }
                else{
                    $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                    if ($isFileClean) {
                        $data['image'] = $_FILES['file'];
                    }
                }
            }
        }
        $group = GROUPS_BOL_Service::getInstance()->createGroup(OW::getUser()->getId(), $data);
        return $group;
    }

    public function activateGroup(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        $pluginPlusActive = FRMSecurityProvider::checkPluginActive('frmgroupsplus', true);

        if(!$pluginActive || !$pluginPlusActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $groupId = null;
        if(isset($_GET['groupId'])){
            $groupId = (int) $_GET['groupId'];
        }

        if($groupId == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(FRMGROUPSPLUS_BOL_Service::getInstance()->approveGroupById($groupId))
        {
            return array('valid' => true, 'message' => 'group_activated', 'group' => $group);
        }
        else
        {
            return array('valid' => false, 'message' => 'group_activation_failed', 'group' => $group);
        }
    }

    public function createInvitationLink() {
        if(!FRMSecurityProvider::checkPluginActive('frmgroupsinvitationlink', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!isset($_POST['groupId'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        $groupId = (int) $_POST['groupId'];

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()->isCurrentUserCanAddLink($groupId)){
            return array('valid' => false, 'message' => 'access_denied');
        }

        $link = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()->generateLink($groupId);
        if(!isset($link)){
            return array('valid' => false, 'message' => 'cant_create_link');
        }

        $hashLink = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code'=>$link->hashLink));

        $userId = $link->getUserId();
        $userObject = BOL_UserService::getInstance()->findUserById($userId);
        $username = BOL_UserService::getInstance()->getDisplayName($userId);
        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
        $user = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatar, $username);

        $linkData = array(
            'id' => $link->getId(),
            'hashLink' => $hashLink,
            'isActive' => $link->getIsActive() == "1" ? true : false,
            'createDate' => (string)$link->getCreateDate(),
            'creatorUser' => $user
        );

        return array(
            'valid' => true,
            'message' => 'link_created',
            'link' => $linkData
        );
    }

    public function deactivateInvitationLink()
    {
        if(!FRMSecurityProvider::checkPluginActive('frmgroupsinvitationlink', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!isset($_POST['groupId']) || !isset($_POST['linkId'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        $groupId = (int) $_POST['groupId'];
        $linkId = (int) $_POST['linkId'];

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()->isCurrentUserCanAddLink($groupId)){
            return array('valid' => false, 'message' => 'access_denied');
        }

        if($linkId != 0){
            // for deactivate specific link of group
            $link = FRMGROUPSINVITATIONLINK_BOL_LinkDao::getInstance()->findById($linkId);
            if(!isset($link)){
                return array('valid' => false, 'message' => 'input_error');
            }
            $result = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()->deactivateLink($link);
            $successMessage = 'link_deactivated';
            $failureMessage = 'cant_deactivate_link';
        } else{
            // for deactivate all links of group
            $result = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()->deactivateAllGroupLinks($groupId);
            $successMessage = 'all_group_links_deactivated';
            $failureMessage = 'cant_deactivate_all_group_links';
        }

        if(!$result){
            return array('valid' => false, 'message' => $failureMessage);
        }

        return array(
            'valid' => true,
            'message' => $successMessage
        );
    }

    private function inviteUsersByQuestion() {
        $groupId = $_GET['groupId'];
        $inviterUserId = OW::getUser()->getId();
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        $count = self::CHUNK_SIZE;

        if($group == null){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!OW::getUser()->isAdmin()){
            if(!GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($group->id)){
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }

        list($first,$accountType,$questions) = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->setupParametersQuestionsToInvite();

        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess'])){
            $hasAccess=true;
        }

        $users = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getGroupSearchedUsersByQuestions($questions, $first, $count, $groupId);
        $resultAddByCheckAccess = false;
        $resultAddByEvent = false;
        while (!empty($users)) {
            if (isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite'] == true) {
                $userIds = array_column($users, 'id');
                $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically', array('groupId' => $groupId, 'userIds' => $userIds));
                OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);
                $resultAddByEvent = true;
            } else {
                if (isset($hasAccess)) {
                    foreach ($users as $user) {
                        $uid = $user->id;
                        if ($inviterUserId == $uid)
                            continue;
                        $resultAddByCheckAccess = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $uid, $inviterUserId);
                    }
                }
                if (FRMSecurityProvider::checkPluginActive('friends', true)) {
                    foreach ($users as $user) {
                        $userId = $user->id;
                        $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $inviterUserId);
                        if (isset($isFriends) && $isFriends->status == 'active') {
                            $resultAddByCheckAccess = GROUPS_BOL_Service::getInstance()->inviteUser($group->id, $userId, $inviterUserId);
                        }
                    }
                }
            }

            $first += $count;
            $users = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getGroupSearchedUsersByQuestions($questions, $first, $count, $groupId);
        }

        if ($resultAddByEvent) {
            return array('valid' => true, 'result_key' => 'add_automatically');
        }
        if ($resultAddByCheckAccess) {
            return array('valid' => $resultAddByCheckAccess);
        }
        return array('valid' => false, 'message' => 'input_error');
    }
}