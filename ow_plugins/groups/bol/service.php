<?php
/**
 * Groups Service
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_Service
{
    const IMAGE_WIDTH_SMALL = 40;
    const IMAGE_WIDTH_BIG = 100;
    
    const IMAGE_SIZE_SMALL = 1;
    const IMAGE_SIZE_BIG = 2;
    
    const WIDGET_PANEL_NAME = 'group';

    const EVENT_ON_DELETE = 'groups_on_group_delete';
    const EVENT_DELETE_COMPLETE = 'groups_group_delete_complete';
    const EVENT_CREATE = 'groups_group_create_complete';
    const EVENT_BEFORE_CREATE = 'groups_group_before_create';
    const EVENT_EDIT = 'groups_group_edit_complete';
    const EVENT_USER_ADDED = 'groups_user_signed';
    const EVENT_USER_BEFORE_ADDED = 'groups_before_user_signed';
    const EVENT_USER_DELETED = 'groups_user_left';
    const EVENT_DELETE_FORUM = 'forum.delete_group';

    const EVENT_INVITE_ADDED = 'groups.invite_user';
    const EVENT_INVITE_DELETED = 'groups.invite_removed';

    const EVENT_UNINSTALL_IN_PROGRESS = 'groups.uninstall_in_progress';

    const WCV_ANYONE = 'anyone';
    const WCV_INVITE = 'invite';

    const WCI_CREATOR = 'creator';
    const WCI_PARTICIPANT = 'participant';

    const PRIVACY_EVERYBODY = 'everybody';
    const PRIVACY_ACTION_VIEW_MY_GROUPS = 'view_my_groups';

    const LIST_MOST_POPULAR = 'most_popular';
    const LIST_LATEST = 'latest';

    const LIST_ALL = 'all';

    const ENTITY_TYPE_WAL = 'groups_wal';
    const ENTITY_TYPE_GROUP = 'groups';
    const FEED_ENTITY_TYPE = 'group';
    const GROUP_FEED_ENTITY_TYPE = 'groups-status';

    private static $classInstance;
    private $unread_counts_for_current_user = false;
    private $isQuestionRolesModerator = null;
    private $whereClauseForGroupModerator;
    /**
     * Returns class instance
     *
     * @return GROUPS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var GROUPS_BOL_InviteDao
     */
    private $inviteDao;

    /**
     *
     * @var GROUPS_BOL_GroupDao
     */
    private $groupDao;

    /**
     *
     * @var GROUPS_BOL_GroupUserDao
     */
    private $groupUserDao;

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        $this->groupDao = GROUPS_BOL_GroupDao::getInstance();
        $this->groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();
        $this->inviteDao = GROUPS_BOL_InviteDao::getInstance();
    }

    public function saveGroup( GROUPS_BOL_Group $groupDto )
    {
        $this->groupDao->save($groupDto);
        OW::getLogger()->writeLog(OW_Log::INFO, 'edit_group', ['actionType'=>OW_Log::UPDATE, 'enType'=>'group', 'enId'=>$groupDto->getId()]);
    }

    public function saveImages( $postFile, GROUPS_BOL_Group $group )
    {
        $service = GROUPS_BOL_Service::getInstance();

        $smallFile = $service->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_SMALL);
        $bigFile = $service->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG);

        $tmpDir = OW::getPluginManager()->getPlugin('groups')->getPluginFilesDir();
        $smallTmpFile = $tmpDir . FRMSecurityProvider::generateUniqueId('small_') . '.jpg';
        $bigTmpFile = $tmpDir . FRMSecurityProvider::generateUniqueId('big_') . '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $postFile['tmp_name'], 'destination' => $smallTmpFile)));
        if(isset($checkAnotherExtensionEvent->getData()['destination'])){
            $smallTmpFile = $checkAnotherExtensionEvent->getData()['destination'];
        }

        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $postFile['tmp_name'], 'destination' => $bigTmpFile)));
        if(isset($checkAnotherExtensionEvent->getData()['destination'])){
            $bigTmpFile = $checkAnotherExtensionEvent->getData()['destination'];
        }
        $image = new UTIL_Image($postFile['tmp_name']);
        $image->resizeImage(GROUPS_BOL_Service::IMAGE_WIDTH_BIG, GROUPS_BOL_Service::IMAGE_WIDTH_BIG, true)
            ->saveImage($bigTmpFile)
            ->resizeImage(GROUPS_BOL_Service::IMAGE_WIDTH_SMALL, GROUPS_BOL_Service::IMAGE_WIDTH_SMALL, true)
            ->saveImage($smallTmpFile);

        try
        {
            OW::getStorage()->copyFile($smallTmpFile, $smallFile);
            OW::getStorage()->copyFile($bigTmpFile, $bigFile);
        }
        catch ( Exception $e ) {}

        OW::getStorage()->removeFile($smallTmpFile);
        OW::getStorage()->removeFile($bigTmpFile);
    }

    public function processGroupInfo($group, $values){
        $service = GROUPS_BOL_Service::getInstance();

        if(isset($values['deleteGroupImage']) && $values['deleteGroupImage']==1 && empty($values['image']))
        {
            if ( !empty($group->imageHash) )
            {
                OW::getStorage()->removeFile($service->getGroupImagePath($group));
                OW::getStorage()->removeFile($service->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG));
                $group->imageHash=null;
            }
        }
        if ( !empty($values['image']) )
        {
            if ( !empty($group->imageHash) )
            {
                OW::getStorage()->removeFile($service->getGroupImagePath($group));
                OW::getStorage()->removeFile($service->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG));
            }

            $group->imageHash = FRMSecurityProvider::generateUniqueId();
        }

        $group->title = strip_tags($values['title']);

        $values['description'] = UTIL_HtmlTag::stripTagsAndJs($values['description'], array('frame'), array(), true);

        $group->description = $values['description'];
        $group->whoCanInvite = $values['whoCanInvite'];
        $group->whoCanView = $values['whoCanView'];
        $group->lastActivityTimeStamp = time();
        $service->saveGroup($group);
        $categoryStatus = null;
        $reportEnableStatus = false;
        if(isset($values['categoryStatus'])){
            $categoryStatus = $values['categoryStatus'];
        }
        if(isset($values['reportEnableStatus'])){
            $reportEnableStatus = $values['reportEnableStatus'];
        }
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_CATEGORY_TO_GROUP, array('groupId' => $group->getId(), 'categoryId' => $categoryStatus, 'reportEnableStatus'=> $reportEnableStatus)));
        if(isset($values['whoCanCreateContent'])){
            OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.set.channel.for.group', array('groupId' => $group->getId(),'isChannel' => $values['whoCanCreateContent'])));
        }

        if(isset($values['rssLinks'])){
            OW::getEventManager()->trigger(new OW_Event('frmgroupsrss.set.rss.for.group.on.edit', array('groupId' => $group->getId(),'rssLinks' => $values['rssLinks'], 'creatorId' => OW::getUser()->getId())));
        }

        OW::getEventManager()->trigger(new OW_Event('set.group.setting',
            array('values' => $values,'groupId' => $group->getId())));

        if ( !empty($values['image']) )
        {
            $service->saveImages($values['image'], $group);
        }
        OW::getEventManager()->trigger(new OW_Event('update.group.feeds.privacy', array('groupPrivacy' => $values['whoCanView'])));
        return $group;
    }

    public function createGroup($userId, $values)
    {
        $group = new GROUPS_BOL_Group();
        $group->timeStamp = time();
        $group->userId = $userId;
        $data = array();
        foreach ( $group as $key => $value )
        {
            $data[$key] = $value;
        }

        $event = new OW_Event(GROUPS_BOL_Service::EVENT_BEFORE_CREATE, array('groupId' => $group->id), $data);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $k => $v )
        {
            $group->$k = $v;
        }

        $service = GROUPS_BOL_Service::getInstance();

        if ( isset($values['image']) && $values['image'] )
        {
            if ( !empty($group->imageHash) )
            {
                OW::getStorage()->removeFile($service->getGroupImagePath($group));
                OW::getStorage()->removeFile($service->getGroupImagePath($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG));
            }

            $group->imageHash = FRMSecurityProvider::generateUniqueId();
        }

        $group->title = strip_tags($values['title']);

        $values['description'] = UTIL_HtmlTag::stripTagsAndJs($values['description'], array('frame'), array(), true);

        $group->description = $values['description'];
        $group->whoCanInvite = $values['whoCanInvite'];
        $group->whoCanView = $values['whoCanView'];
        $group->lastActivityTimeStamp = time();

        /**
         * change group status if it needs to be approved
         */
        $frmgroupsplusEvent = new OW_Event('frmgroupsplus.on.group.create.set.approve.setting', array('group' => $group, 'userId' => $group->userId));
        OW::getEventManager()->trigger($frmgroupsplusEvent);
        $service->saveGroup($group);
        $categoryStatus = null;
        $reportEnableStatus = null;
        if(isset($values['categoryStatus'])){
            $categoryStatus = $values['categoryStatus'];
        }
        if(isset($values['reportEnableStatus'])){
            $reportEnableStatus = $values['reportEnableStatus'];
        }
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_CATEGORY_TO_GROUP, array('groupId' => $group->getId(), 'categoryId' => $categoryStatus, 'reportEnableStatus'=> $reportEnableStatus)));

        $whoCanCreateContent = null;
        if(isset($values['whoCanCreateContent'])){
            $whoCanCreateContent = $values['whoCanCreateContent'];
        }
        OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.set.channel.for.group', array('groupId' => $group->getId(),'isChannel' => $whoCanCreateContent)));

        if(isset($values['rssLinks'])){
            OW::getEventManager()->trigger(new OW_Event('frmgroupsrss.set.rss.for.group.on.create', array('groupId' => $group->getId(),'rssLinks' => $values['rssLinks'], 'creatorId' => $userId)));
        }

        OW::getEventManager()->trigger(new OW_Event('set.group.setting',
            array('values' => $values,'groupId'=>$group->getId())));

        if ( isset($values['image']) && !empty($values['image']) )
        {
            $this->saveImages($values['image'], $group);
        }

        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');
        // Add forum group
        if ( $is_forum_connected )
        {
            $event = new OW_Event('forum.create_group', array('entity' => 'groups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
            OW::getEventManager()->trigger($event);
        }

        if ( $group )
        {
            $event = new OW_Event(GROUPS_BOL_Service::EVENT_CREATE, array('groupId' => $group->id));
            OW::getEventManager()->trigger($event);

            OW::getEventManager()->trigger(new OW_Event('frmfilemanager.insert',
                array('name' => 'frm:groups:'.$group->id, 'parent' => 'frm:groups', 'mime' => 'directory',
                    'time' => time(), 'content' => '', 'write' => true, 'locked' => true)));
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($group->id);

        if($group != null){
            $this->addUser($group->id, $userId);
            $event = new OW_Event('Groups.After.Create', array('group' => $group, 'userId' => $userId));
            OW::getEventManager()->trigger($event);
        }

        return $group;
    }

    public function deleteGroup( $groupId )
    {
        $event = new OW_Event(self::EVENT_ON_DELETE, array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);

        $this->deleteGroupInformation($groupId);
        $this->groupDao->deleteById($groupId);

        $event = new OW_Event(self::EVENT_DELETE_COMPLETE, array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);

        OW::getLogger()->writeLog(OW_Log::INFO, 'delete_group', ['actionType'=>OW_Log::DELETE, 'enType'=>'group', 'enId'=>$groupId]);
    }

    private function deleteGroupInformation($groupId){

        if(class_exists('NEWSFEED_BOL_Service')) {
            $actions=NEWSFEED_BOL_Service::getInstance()->findActionsByFeedTypeAndFeedId('groups',$groupId);
            foreach ($actions as $action) {
                if($action!=null) {
                    $entityId = (int)$action['entityId'];
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'status_comment',
                        'entityId' => $entityId
                    ));

                    // TODO: delete all notifications with one query
                    BOL_CommentService::getInstance()->deleteEntityComments(self::GROUP_FEED_ENTITY_TYPE, $entityId);
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'base_profile_wall',
                        'entityId' => $entityId
                    ));

                    // TODO: fetch all items with one query
                    OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
                        'entityType' => self::GROUP_FEED_ENTITY_TYPE,
                        'entityId' => $entityId,
                        'actionId' => $action['id']
                    )));
                }
            }
            NEWSFEED_BOL_Service::getInstance()->removeStatus('groups', $groupId);
        }

        OW::getEventManager()->trigger(new OW_Event('frmfilemanager.remove',
            array('name' => 'frm:groups:'.$groupId)));
    }
    public function deleteUser( $groupId, array $userIds, $groupDelete = false )
    {
        if(!$groupDelete) {
            $groupUserDtoIds = $this->groupUserDao->findGroupUserIdsByGroupIdAndUserIds($groupId,$userIds);
            $event = new OW_Event('groups.before.user.leave', array(
                'groupId' => $groupId,
                'userIds' => $userIds,
                'groupUserIds' => $groupUserDtoIds
            ));
            $eventResult = OW::getEventManager()->trigger($event);
            if (!$groupDelete && isset($eventResult->getData()['cancel']) && $eventResult->getData()['cancel'] == true) {
                return false;
            }
        }else{
            $groupUserDtoIds = $this->groupUserDao->findGroupUsers($groupId);
            $userIds = $groupUserDtoIds;
        }

        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.delete.user.as.manager', array('groupId'=>$groupId,'userIds'=>$userIds));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);

        $event = new OW_Event('frmgroupsplus.on_delete_user', array(
            'groupId' => $groupId,
            'userIds' => $userIds,
            'groupUserIds' => $groupUserDtoIds,
            'groupDelete' =>$groupDelete
        ));
        OW::getEventManager()->trigger($event);

        $this->groupUserDao->deleteByIdList($groupUserDtoIds);

        $event = new OW_Event(self::EVENT_USER_DELETED, array(
            'groupId' => $groupId,
            'userIds' => $userIds,
            'groupUserIds' => $groupUserDtoIds
        ));
        OW::getEventManager()->trigger($event);
        if(!$groupDelete){
            OW::getLogger()->writeLog(OW_Log::INFO, 'delete_group_user', ['actionType'=>OW_Log::DELETE, 'enType'=>'group', 'enId'=>$groupId, 'ids'=>$userIds]);
        }
        return true;
    }

    public function onUserUnregister( $userId, $withContent )
    {
        if ( $withContent )
        {
            $groups = $this->groupDao->findAllUserGroups($userId);

            foreach ( $groups as $group )
            {
                GROUPS_BOL_Service::getInstance()->deleteGroup($group->id);
            }
        }

        $this->inviteDao->deleteByUserId($userId);
        $this->groupUserDao->deleteByUserId($userId);
    }

    public function findUserGroupList( $userId, $first = null, $count = null, $orderWithLastActivity = true ,
        $parentId=null,$status=GROUPS_BOL_Group::STATUS_ACTIVE,$type=null,$searchTitle=null)
    {
        return $this->groupDao->findByUserId($userId, $first, $count, null, $searchTitle, $orderWithLastActivity,$parentId,$status,$type);
    }

    /***
     * @param $groupId
     * @param int $userId
     */
    public function updateLastSeenForGroupUser( $groupId, $userId=0 )
    {
        if($userId == 0){
            if(!OW::getUser()->isAuthenticated()){
                return;
            }
            $userId = OW::getUser()->getId();
        }
        $gU = $this->groupUserDao->findGroupUser($groupId, $userId);
        if(!empty($gU)) {
            $gU->last_seen_action = time();
            $this->groupUserDao->save($gU);
        }
    }

    /***
     * @param $groupId
     * @param bool $includeAuthored
     * @param null $lastSeen
     * @return int
     */
    public function getUnreadCountForGroupUser( $groupId, $includeAuthored = false, $lastSeen = null )
    {
        if (!OW::getUser()->isAuthenticated()) {
            return 0;
        }
        $userId = OW::getUser()->getId();

        // this happens at most once in any session:
        // fixes the problem with multiple calls to database
        if ($this->unread_counts_for_current_user === false){
            $this->unread_counts_for_current_user = [];

            $params = array(
                'byUserId' => true,
                'userId' => $userId,
                'includeAuthored' => $includeAuthored,
            );
            if ($lastSeen !== null) {
                $params['lastSeenAction'] = $lastSeen;
            }
            $event = OW::getEventManager()->trigger(new OW_Event('groups.unread_count.group_user', $params));
            if (isset($event->getData()['items'])) {
                $this->unread_counts_for_current_user = $event->getData()['items'];
            }
        }

        if (!empty($this->unread_counts_for_current_user[$groupId])) {
            return (int)$this->unread_counts_for_current_user[$groupId];
        }
        return 0;
    }

    /***
     * @param bool $includeAuthored
     * @return array|bool
     */
    public function getUnreadCountForEachGroupUser( $includeAuthored = false )
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array();
        }
        $userId = OW::getUser()->getId();

        if ($this->unread_counts_for_current_user === false){
            $this->unread_counts_for_current_user = [];
            $event = OW::getEventManager()->trigger(new OW_Event('groups.unread_count.group_user', array('byUserId' => true, 'userId' => $userId, 'includeAuthored' => $includeAuthored)));
            if (isset($event->getData()['items'])) {
                $this->unread_counts_for_current_user = $event->getData()['items'];
            }
        }
        if ($this->unread_counts_for_current_user === false || !is_array($this->unread_counts_for_current_user)) {
            return array();
        }
        return $this->unread_counts_for_current_user;
    }

    /***
     * @param int $userId
     * @param bool $includeAuthored
     * @return int
     */
    public function getUnreadGroupsCountForUser( $userId=0, $includeAuthored = false )
    {
        if($userId == 0){
            if(!OW::getUser()->isAuthenticated()){
                return 0;
            }
            $userId = OW::getUser()->getId();
        }

        $count = 0;

        $event = OW::getEventManager()->trigger(new OW_Event('groups.unread_count.group_user', array('byUserId' => true, 'userId' => $userId, 'includeAuthored' => $includeAuthored, 'only_count'=>true)));
        if (isset($event->getData()['count'])) {
            $count = $event->getData()['count'];
        }

        return $count;
    }

    public function findUserGroupListCount( $userId )
    {
        return $this->groupDao->findCountByUserId($userId);
    }

    /**
     *
     * @param $groupId
     * @return GROUPS_BOL_Group
     */
    public function findGroupById( $groupId )
    {
        return $this->groupDao->findById((int) $groupId);
    }
    
    public function findGroupListByIds( $groupIds )
    {
        return $this->groupDao->findByIdList($groupIds);
    }

    /**
     * Find latest public group list ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicGroupListIds($first, $count)
    {
        return $this->groupDao->findLatestPublicGroupListIds($first, $count);
    }

    public function findGroupList( $listType, $first=null, $count=null, $nativeMobile = false)
    {
        $isNativeAdminOrGroupModerator = false;
        $event = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.get.groups.list.mobile', array('nativeMobile' => $nativeMobile)));
        if (isset($event->getData()['isAdminOrGroupModerator'])) {
            $isNativeAdminOrGroupModerator = $event->getData()['isNativeAdminOrGroupModerator'];
        }
        switch ( $listType )
        {
            case self::LIST_MOST_POPULAR:
                return $this->groupDao->findMostPupularList($first, $count, $isNativeAdminOrGroupModerator);

            case self::LIST_LATEST:
                return $this->groupDao->findOrderedList($first, $count, $isNativeAdminOrGroupModerator);

            case self::LIST_ALL:
                return $this->groupDao->findAllLimited( $first, $count, $isNativeAdminOrGroupModerator);
        }

        throw new InvalidArgumentException('Undefined list type');
    }

    public function findGroupListCount( $listType )
    {
        switch ( $listType )
        {
            case self::LIST_MOST_POPULAR:
            case self::LIST_LATEST:
                return $this->groupDao->findAllCount();
        }

        throw new InvalidArgumentException('Undefined list type');
    }

    public function findInvitedGroups( $userId, $first=null, $count=null )
    {
        return $this->groupDao->findUserInvitedGroups($userId, $first, $count);
    }

    public function findInvitedGroupsCount( $userId )
    {
        return $this->groupDao->findUserInvitedGroupsCount($userId);
    }

    public function findMyGroups( $userId, $first=null, $count=null, $type=null )
    {
        return $this->groupDao->findMyGroups($userId, $first, $count, $type);
    }

    public function findGroupsWithIds( $ids, $first=null, $count=null )
    {
        return $this->groupDao->findGroupsWithIds($ids, $first, $count);
    }

    public function findMyGroupsCount( $userId )
    {
        return $this->groupDao->findMyGroupsCount($userId);
    }


    /**
     * @param string $groupTableAlias
     * @return string
     */
    public function generateInClauseForGroupForQuestionRoles($groupTableAlias ='g') {

        if(!empty($this->whereClauseForGroupModerator))
        {
            return $this->whereClauseForGroupModerator;
        }
        $whereClause = " ";

        $userRoles = GROUPS_BOL_Service::getInstance()->getUserRolesToManageSpecificUsers();
        $isQuestionRoleModerator = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers($userRoles);

        if (!OW::getUser()->isAuthorized('groups') && !OW::getUser()->isAdmin() && $isQuestionRoleModerator) {
            $userIds = OW::getEventManager()->trigger(new OW_Event('frmquestionroles.getUsersByRolesData', array('rolesData' => $userRoles)));
            $userIds = $userIds->getData();
            if (!empty($userIds)) {
                $whereClause = ' AND `'.$groupTableAlias.'`.`userId` IN (' . OW::getDbo()->mergeInClause($userIds) . ') ';
            }
        }
        $this->whereClauseForGroupModerator = $whereClause;
        return $whereClause;
    }
    /**
     * @param bool $popular
     * @param string $status
     * @param null $latest
     * @param null $first
     * @param null $count
     * @param null $userId
     * @param array $groupIds
     * @param null $searchTitle
     * @param null $parentId
     * @param bool $isNativeAdminOrGroupModerator
     * @return array
     */
    public function findGroupsByFiltering($popular=false,$status=GROUPS_BOL_Group::STATUS_ACTIVE,$latest=null,
                                          $first=null, $count=null,$userId=null, $groupIds=array(),
                                          $searchTitle=null, $type=null, $isNativeAdminOrGroupModerator = false)
    {
        $parentId=null;
        $filters =['popular'=> $popular,'status'=> $status, 'latest'=> $latest, 'first'=>$first , 'count'=> $count, 'userId'=> $userId,
            'groupIds' => $groupIds,'searchTitle'=>$searchTitle, 'parentId'=>$parentId,'isNativeAdminOrGroupModerator' => $isNativeAdminOrGroupModerator];
        if($userId!=null)
        {
            return $this->groupDao->findByUserId($userId, $first, $count, $groupIds,
                $searchTitle,true,$parentId,$status,$type);
        }

        $whereClause = ' WHERE 1=1 ';
        $OrderClause="";
        $limit="";
        $params = array();
        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $whereClause .= ' AND (`g`.`whoCanView`="' . GROUPS_BOL_Service::WCV_ANYONE.'"';
            if(OW::getUser()->isAuthenticated()){
                $whereClause .=" OR `gu`.`userId`=:userId ) ";
                $params['userId']=OW::getUser()->getId();
                $filters['userId']=OW::getUser()->getId();
            }else{
                $whereClause .=" ) ";
            }
        }
        if ( $first !== null && $count !== null )
        {
            $limit = " LIMIT $first, $count";
        }
        if($userId!=null)
        {
            $whereClause.=" AND `gu`.`userId`=:u";
            $params['u']=$userId;
        }
        if($groupIds!=null && sizeof($groupIds)>0){
            $whereClause.=  " AND `g`.`id` in (". OW::getDbo()->mergeInClause($groupIds) .")";
        }
        if(!empty($searchTitle)){
            $whereClause.=' AND UPPER(`g`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $searchTitle . '%';
        }
        if(!$isNativeAdminOrGroupModerator){
            $whereClause.=" AND `g`.`status`=:s ";
            $params['s']=$status;
        }
        if(isset($latest) || isset($userId)){
            $OrderClause=" ORDER BY `g`.`timeStamp` DESC";
        }

        $joinClauseQuerySubGroup='';
        if(isset($parentId))
        {
            $eventSubGroupClause=OW::getEventManager()->trigger(new OW_Event('groups.list.add.where.clause',array('parentGroupId'=>$parentId,'joinColumnWithParentId'=>'`g`.`id`')));
            if(isset($eventSubGroupClause->getData()['joinClauseQuerySubGroup']))
            {
                $joinClauseQuerySubGroup=$eventSubGroupClause->getData()['joinClauseQuerySubGroup'];
            }
        }
        if($popular) {
            $whereClause=str_replace("`g`.","`g1`.",$whereClause);
            $query="
                SELECT count(`gu`.`userId`),  `g`.`id`, `g`.`title`, `g`.`description`, `g`.`imageHash`, `g`.`timeStamp`, `g`.`userId`,`g`.`privacy`,
                 `g`.`whoCanView`, `g`.`whoCanInvite`, `g`.`status`  
    from (select `g1`.`id`, `g1`.`title`, `g1`.`description`, `g1`.`imageHash`, `g1`.`timeStamp`, `g1`.`userId`,`g1`.`privacy`, `g1`.`whoCanView`, `g1`.`whoCanInvite`, `g1`.`status`  
    from `".$this->groupDao->getTableName(). "` as g1 inner join `".OW_DB_PREFIX."groups_group_user` as `gu` on `g1`.`id` = `gu`.`groupId`  ". $joinClauseQuerySubGroup.$whereClause." 
    group by `g1`.`id` ) as `g` 
    inner join `".OW_DB_PREFIX."groups_group_user` as `gu` on `g`.`id` = `gu`.`groupId`  
    group by `gu`.`groupId` order by count(`gu`.`userId`) desc" .$limit;

            $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list',['filters'=>$filters, 'type'=>'popular', 'limit'=> $limit]));
            if(isset($eventQuery->getData()['query']))
            {
                $query = $eventQuery->getData()['query'];
            }
            return $this->groupDao->findGroupsByFiltering($query, $params, true);
        }else{
            $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list',['filters'=>$filters, 'type'=>'latest', 'limit'=> $limit]));
            if(isset($eventQuery->getData()['query']))
            {
                $query = $eventQuery->getData()['query'];
            }
            if(!isset($query)) {
                if($status==GROUPS_BOL_Group::STATUS_APPROVAL) {
                    $whereClause .= GROUPS_BOL_Service::getInstance()->generateInClauseForGroupForQuestionRoles();
                }
                $query = "SELECT DISTINCT g.* FROM " . $this->groupDao->getTableName() . " g
            INNER JOIN " . $this->groupUserDao->getTableName() . " gu ON g.id = gu.groupId"
                    . $joinClauseQuerySubGroup . $whereClause . $OrderClause . $limit;
            }
            return $this->groupDao->findGroupsByFiltering($query, $params, false);
        }
    }

    /**
     * @param bool $popular
     * @param string $status
     * @param null $latest
     * @param null $userId
     * @param array $groupIds
     * @param null $searchTitle
     * @param null $parentId
     * @return int
     */
    public function findGroupsByFilteringCount($popular=false,$status=GROUPS_BOL_Group::STATUS_ACTIVE,$latest=null,
                                               $userId=null, $groupIds=array(),$searchTitle=null, $type=null)
    {
        return $this->groupDao->findGroupsByFilteringCount($popular,$status,$latest,$userId, $groupIds,$searchTitle, $type);
    }

    public function findAllGroupCount()
    {
        return $this->groupDao->findAllCount();
    }

    public function findByTitle( $title )
    {
        return $this->groupDao->findByTitle($title);
    }

    public function isGroupTitleUnique($title, $groupId = null)
    {
        $dto = GROUPS_BOL_Service::getInstance()->findByTitle($title);


        /**
         * check for creating new group
         */
        if(isset($dto) && !isset($groupId))
        {
            return false;
        }

        /**
         * check for editing a group
         */
        if(isset($dto) && isset($groupId) && $dto->id != $groupId)
        {
            return false;
        }

        return true;
    }

    public function ifGroupIsApprovalCanDeletedByUser($groupStatus)
    {
        $isModerator = OW::getUser()->isAuthorized('groups');
        $isAdmin = OW::getUser()->isAdmin();

        if(!$isModerator && !$isAdmin && $groupStatus == "approval")
        {
            return false;
        }

        return true;
    }

    public function findLimitedList( $count )
    {
        return $this->groupDao->findLimitedList($count);
    }

    public function findUserListCount( $groupId )
    {
        return $this->groupUserDao->findCountByGroupId($groupId);
    }

    public function findUserListCountBySearch( $groupId, $searchValue=null)
    {
        return $this->groupUserDao->findCountByGroupIdBySearch($groupId, $searchValue);
    }

    public function findUserCountForList( $groupIdList )
    {
        return $this->groupUserDao->findCountByGroupIdList($groupIdList);
    }

    public function findUserList( $groupId, $first, $count )
    {
        $groupUserList = $this->groupUserDao->findListByGroupId($groupId, $first, $count);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return BOL_UserService::getInstance()->findUserListByIdList($idList);
    }

    public function findUserListBySearch( $groupId, $first, $count,$searchValue=null )
    {
        $groupUserList = $this->groupUserDao->findListByGroupIdBySearch($groupId, $first, $count, $searchValue);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return BOL_UserService::getInstance()->findUserListByIdList($idList);
    }

    public function findGroupUserIdList( $groupId, $privacy = null )
    {
        $groupUserList = $this->groupUserDao->findByGroupId($groupId, $privacy);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return $idList;
    }

    public function addUser( $groupId, $userId )
    {
        $dto = $this->findUser($groupId, $userId);
        if ( $dto !== null )
        {
            return true;
        }

        $dto = new GROUPS_BOL_GroupUser();
        $dto->timeStamp = time();

        $dto->groupId = $groupId;
        $dto->userId = $userId;
        $dto->last_seen_action = time();

        $data = array();
        foreach ( $dto as $key => $value )
        {
            $data[$key] = $value;
        }

        $event = new OW_Event(self::EVENT_USER_BEFORE_ADDED, array(
            'groupId' => $groupId,
            'userId' => $userId
        ), $data);

        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $k => $v )
        {
            $dto->$k = $v;
        }

        $this->groupUserDao->save($dto);

        $this->deleteInvite($groupId, $userId);

        $event = new OW_Event(self::EVENT_USER_ADDED, array(
                'groupId' => $groupId,
                'userId' => $userId,
                'groupUserId' => $dto->id
            ));

        OW::getEventManager()->trigger($event);
    }

    public function findUser( $groupId, $userId )
    {
        return $this->groupUserDao->findGroupUser($groupId, $userId);
    }

    public function getGroupImageFileName( GROUPS_BOL_Group $group = null, $size = self::IMAGE_SIZE_SMALL )
    {
        if ( $group == null || empty($group->imageHash) )
        {
            return null;
        }

        $suffix = $size == self::IMAGE_SIZE_BIG ? "big-" : "";
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => OW::getPluginManager()->getPlugin('groups')->getUserFilesDir() . 'group-' . $group->id . '-'  . $suffix . $group->imageHash)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return 'group-' . $group->id . '-'  . $suffix . $group->imageHash . $ext;
    }

    public function getGroupImageUrl( GROUPS_BOL_Group $group = null, $size = self::IMAGE_SIZE_SMALL, $returnPath = false )
    {
        $noPictureUrl = OW::getPluginManager()->getPlugin('base')->getStaticUrl(). 'css/images/default_group_image.svg';
        if($group == null){
            return $noPictureUrl;
        }
        $path = $this->getGroupImagePath($group, $size);

        return (empty($path) || !OW::getStorage()->fileExists($path)) ? $noPictureUrl : OW::getStorage()->getFileUrl($path, $returnPath);
    }

    public function getGroupImagePath( GROUPS_BOL_Group $group = null, $size = self::IMAGE_SIZE_SMALL )
    {
        if($group == null){
            return null;
        }
        $fileName = $this->getGroupImageFileName($group, $size);

        return empty($fileName) ? null : OW::getPluginManager()->getPlugin('groups')->getUserFilesDir() . $fileName;
    }

    public function getGroupUrl( GROUPS_BOL_Group $group )
    {
        return OW::getRouter()->urlForRoute('groups-view', array('groupId' => $group->id));
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
    }

    /***
     * @param $actionId
     * @param $entityId
     * @param $type
     * @return int|null
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
        }
        return null;
    }



    public function searchAdditionalParameters(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['entityType']) || !isset($params['entityId']) || $params['entityType'] !='groups')
        {
            return;
        }
        $whereClause = " AND `user`.`id` NOT IN (SELECT `gu`.`userId` FROM `".OW_DB_PREFIX."groups_group_user` `gu` WHERE `gu`.`groupId`=:groupId)
					 AND `user`.`id` NOT IN (SELECT `gi`.`userId` FROM `".OW_DB_PREFIX."groups_invite` `gi` WHERE `gi`.`groupId`=:groupId) ";

        $data['where']= $whereClause;
        $data['whereParams']= ['groupId'=>$params['entityId']];
        $event->setData($data);
    }


    public function onDeleteFeed(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['action']))
        {
            return;
        }
        $action = $params['action'];
        $groupEntityArr = ['groups-join','groups-status','groups-leave'];
        if(!in_array($action->entityType,$groupEntityArr)) {
            return;
        }
        $groupId = $this->findGroupIdByActionId($action->id,$action->entityId,'groups');
        $group = $this->findGroupById($groupId);
        if(!isset($group)) {
            return;
        }
        $canEdit = $this->isCurrentUserCanEdit($group);
        if($canEdit)
        {
            $data['canDeleteGroupFeed']=true;
            $event->setData($data);
        }
    }
    public function isCurrentUserCanEdit( GROUPS_BOL_Group $group, $checkManager = true)
    {
        if(empty($group)){
            OW::getLogger()->writeLog(OW_Log::WARNING, 'group_can_edit_not_found');
            return false;
        }
        $isManager = false;
        if ($checkManager) {
            $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$group->getId()));
            OW::getEventManager()->trigger($eventIisGroupsPlusManager);
            if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
                $isManager=$eventIisGroupsPlusManager->getData()['isUserManager'];
            }
        }

        $canUserModerateThisUserByQuestionRole = $this->canUserModerateThisUserByQuestionRole($group->userId,$group->status);

        return ($group->userId == OW::getUser()->getId()) || OW::getUser()->isAuthorized('groups')  || $isManager==true || OW::getUser()->isAdmin() || $canUserModerateThisUserByQuestionRole;
    }

    public function checkAccessUpdateStatus(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['feedId']) || !isset($params['feedType']) || $params['feedType']!='groups')
        {
            return;
        }

        $groupId = $params['feedId'];

        $group = $this->findGroupById($groupId);

        $canAddPost = $this->isCurrentUserCanAddPost($group);
        if(!$canAddPost)
        {
            $event->setData(['not_allowed'=>true]);
        }
    }

    public function isCurrentUserCanCreate()
    {
        return OW::getUser()->isAuthorized('groups', 'create');
    }

    public function isCurrentUserCanView( GROUPS_BOL_Group $group , $redirectOnInvite = false, $params = array())
    {
        if (!isset($group)) {
            return false;
        }

        if ( $group->userId == OW::getUser()->getId() )
        {
            return true;
        }

        if ( OW::getUser()->isAuthorized('groups') )
        {
            return true;
        }

        if ($this->canUserModerateThisUserByQuestionRole($group->userId,$group->status)) {
            return true;
        }

        $cache = array();
        if (isset($params['cache'])) {
            $cache = $params['cache'];
        }
        if (isset($params['params']['cache'])) {
            $cache = $params['params']['cache'];
        }
        $eventHasViewAccess=OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.view.subgroup.details',array('subGroupId'=>$group->id)));
        if (isset($eventHasViewAccess->getData()['canView']))
        {
            $canView=$eventHasViewAccess->getData()['canView'];
        }
        if(isset($canView) && !$canView)
        {
            return false;
        }
        $canView =  $group->status == GROUPS_BOL_Group::STATUS_ACTIVE && OW::getUser()->isAuthorized('groups', 'view');

        $isMember = false;
        if (isset($cache['users_groups']) && isset($cache['users_groups'][OW::getUser()->getId()])) {
            $isMember = in_array($group->id, $cache['users_groups'][OW::getUser()->getId()]);
        } else {
            $isMember = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId()) !== null;
        }
        if ($canView && !$isMember && !$this->isCurrentUserCanEdit($group) && !OW::getUser()->isAdmin() && $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE ){
            if($redirectOnInvite && GROUPS_BOL_Service::getInstance()->findInvite($group->getId(), OW::getUser()->getId()))
            {
                $invitations = OW::getRouter()->urlForRoute('groups-invite-list');
                OW::getApplication()->redirect($invitations);
                exit();
            }
            if (FRMSecurityProvider::checkPluginActive('frmgroupsinvitationlink', true)){
                $isFromInvitationLink = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()
                    ->isUserVisitedGroupLink($group->id, OW::getUser()->getId());
                return $canView || $isMember || $isFromInvitationLink;
            } else{
                return false;
            }
        }

        if (FRMSecurityProvider::checkPluginActive('frmgroupsinvitationlink', true)){
            $isFromInvitationLink = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()
                ->isUserVisitedGroupLink($group->id, OW::getUser()->getId());
            return $canView || $isMember || $isFromInvitationLink;
        }

        return $canView;
    }

    public function isCurrentUserCanAddPost( GROUPS_BOL_Group $group , $redirectOnInvite = false)
    {
        $canView = $this->isCurrentUserCanView($group, $redirectOnInvite);
        if (!$canView) {
            return false;
        }
        if($group->status != GROUPS_BOL_Group::STATUS_ACTIVE) {
            return false;
        }
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget', array('feedId'=> $group->id, 'feedType'=> self::ENTITY_TYPE_GROUP)));
        $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
        if (!(isset($isChannelParticipant) && $isChannelParticipant))
        {
            return true;
        }
        return false;
    }

    public function isCurrentUserCanViewList()
    {
        return OW::getUser()->isAuthorized('groups', 'view');
    }

    public function updateLastTimeStampOfGroup($groupId){
        if($groupId != null) {
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
            if ($group != null) {
                $group->lastActivityTimeStamp = time();
                GROUPS_BOL_Service::getInstance()->saveGroup($group);
            }
        }
    }

    public function getInvitableUserIds($groupId, $userId){
        $users = null;

        if ( OW::getEventManager()->call('plugin.friends') )
        {
            $users = OW::getEventManager()->call('plugin.friends.get_friend_list', array(
                'userId' => $userId,
                'count' => 1000
            ));
        }

        if ( $users === null )
        {
            $users = array();
            $userDtos = BOL_UserService::getInstance()->findRecentlyActiveList(0, 1000);

            foreach ( $userDtos as $u )
            {
                if ( $u->id != $userId )
                {
                    $users[] = $u->id;
                }
            }
        }
        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',['groupId'=>$groupId]);
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['parentGroupUserIds'])){
            $users= $eventIisGroupsPlusCheckCanSearchAll->getData()['parentGroupUserIds'];
        }
        else if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['userIds'])){
            $users=$eventIisGroupsPlusCheckCanSearchAll->getData()['userIds'];
        }
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['allUsersIdList'])){
            $users= $eventIisGroupsPlusCheckCanSearchAll->getData()['allUsersIdList'];
        }
        $idList = array();

        if ( !empty($users) )
        {
            $groupUsers = $this->findGroupUserIdList($groupId);
            $invitedList = $this->findInvitedUserIdList($groupId, $userId);
            $blockedUsers = BOL_UserService::getInstance()->findBlockedListByUserIdList($userId, $users);
            $blockedByUsers = BOL_UserService::getInstance()->findBlockedByListByUserIdList($userId, $users);

            foreach ( $users as $uid )
            {
                $isBlocked = false;
                if (isset($blockedUsers[$uid]) && $blockedUsers[$uid]) {
                    $isBlocked = true;
                }
                if (isset($blockedByUsers[$uid]) && $blockedByUsers[$uid]) {
                    $isBlocked = true;
                }
                if ( in_array($uid, $groupUsers) || in_array($uid, $invitedList) || $isBlocked)
                {
                    continue;
                }

                $idList[] = $uid;
            }
        }
        return $idList;
    }

    public function isCurrentUserInvite( $groupId, $checkManager = true, $checkUserExistInGroup = true, $group = null)
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            return false;
        }

        if ($group == null) {
            $group = $this->findGroupById($groupId);
        }
        return $this->isCurrentUserInviteByGroupObject($group, $checkManager, $checkUserExistInGroup);
    }

    public function isCurrentUserInviteByGroupObject( $group, $checkManager = true, $checkUserExistInGroup = true)
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            return false;
        }

        if($group == null) {
            return false;
        }

        if ( $group->status != GROUPS_BOL_Group::STATUS_ACTIVE )
        {
            return false;
        }
        if ($checkManager) {
            $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$group->getId()));
            OW::getEventManager()->trigger($eventIisGroupsPlusManager);
            if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
                $isManager=$eventIisGroupsPlusManager->getData()['isUserManager'];
                if($isManager){
                    return true;
                }
            }
        }
        if ( $group->whoCanInvite == self::WCI_CREATOR )
        {
            return $group->userId == $userId;
        }

        if ( $group->whoCanInvite == self::WCI_PARTICIPANT  )
        {
            if ($checkUserExistInGroup) {
                return $this->findUser($group->id, $userId) !== null;
            }
            return true;
        }

        return false;
    }

    public function inviteUser( $groupId, $userId, $inviterId )
    {
        $invite = $this->inviteDao->findInvite($groupId, $userId, $inviterId);

        if ( $invite !== null  )
        {
            return false;
        }

        if (OW::getUser()->isAuthenticated() && $inviterId != $userId) {
            $blocked = BOL_UserService::getInstance()->isBlocked($inviterId, $userId);
            if ($blocked) {
                return false;
            }
        }

        $invite = new GROUPS_BOL_Invite();
        $invite->userId = $userId;
        $invite->groupId = $groupId;
        $invite->inviterId = $inviterId;
        $invite->timeStamp = time();
        $invite->viewed = false;

        $this->inviteDao->save($invite);

        $event = new OW_Event(self::EVENT_INVITE_ADDED, array(
            'groupId' => $groupId,
            'userId' => $userId,
            'inviterId' => $inviterId,
            'inviteId' => $invite->id
        ));

        OW::getEventManager()->trigger($event);

        return true;
    }

    public function deleteInvite( $groupId, $userId )
    {
        $this->inviteDao->deleteByUserIdAndGroupId($groupId, $userId);

        $event = new OW_Event(self::EVENT_INVITE_DELETED, array(
            'groupId' => $groupId,
            'userId' => $userId
        ));

        OW::getEventManager()->trigger($event);
        OW::getLogger()->writeLog(OW_Log::INFO, 'delete_group_user_invite', ['actionType'=>OW_Log::DELETE, 'enType'=>'group', 'enId'=>$groupId, 'id'=>$userId]);
    }

    public function findInvite( $groupId, $userId, $inviterId = null )
    {
        return $this->inviteDao->findInvite($groupId, $userId, $inviterId);
    }

    public function markInviteAsViewed( $groupId, $userId, $inviterId = null )
    {
        $invite = $this->inviteDao->findInvite($groupId, $userId, $inviterId);

        if ( empty($invite) )
        {
            return false;
        }

        $invite->viewed = true;
        $this->inviteDao->save($invite);

        return true;
    }

    public function markAllInvitesAsViewed( $userId )
    {
        $list = $this->inviteDao->findInviteListByUserId($userId);

        foreach ( $list as $item )
        {
            $item->viewed = true;

            $this->inviteDao->save($item);
        }
    }

    public function findAllInviteList( $groupId )
    {
        return $this->inviteDao->findInviteList($groupId);
    }

    public function findInvitedUserIdList( $groupId, $inviterId )
    {
        $list = $this->inviteDao->findListByGroupIdAndInviterId($groupId, $inviterId);
        $out = array();
        foreach ( $list as $item )
        {
            $out[] = $item->userId;
        }

        return $out;
    }

    public function findUserInvitedGroupsCount( $userId, $newOnly = false )
    {
        return $this->groupDao->findUserInvitedGroupsCount($userId, $newOnly);
    }

    /**
     * Find latest group authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestGroupAuthorsIds($first, $count)
    {
        return $this->groupDao->findLatestGroupAuthorsIds($first, $count);
    }

    public function findAllGroupsUserList()
    {
        $users = $this->groupUserDao->findAll();

        $out = array();
        foreach ( $users as $user )
        {
            /* @var $user GROUPS_BOL_GroupUser */
            $out[$user->groupId][] = $user->userId;
        }

        return $out;
    }

    public function setGroupsPrivacy( $ownerId, $privacy )
    {
        $this->groupDao->setPrivacy($ownerId, $privacy);
    }

    public function setGroupUserPrivacy( $userId, $privacy )
    {
        $this->groupUserDao->setPrivacy($userId, $privacy);
    }

    public function clearListingCache()
    {
        OW::getCacheManager()->clean(array( GROUPS_BOL_GroupDao::LIST_CACHE_TAG ));
    }

    public function removeFeedsOfPrivateGroupsByGroupId($groupId){
        if (FRMSecurityProvider::checkPluginActive('forum', true)) {
            $groupForum = FORUM_BOL_ForumService::getInstance()->findGroupByEntityId('groups', $groupId);
            if ($groupForum != null) {
                $topics = FORUM_BOL_TopicDao::getInstance()->findIdListByGroupIds(array($groupForum->id));
                if ($topics != null) {
                    foreach ($topics as $topic) {
                        $actionGroupForumTopic = NEWSFEED_BOL_ActionDao::getInstance()->findAction('forum-topic', $topic);
                        if ($actionGroupForumTopic != null) {
                            $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByActionIds(array($actionGroupForumTopic->getId()));
                            $activityIds = array();
                            foreach ($activities as $activity) {
                                $activityIds[] = $activity->id;
                            }
                            $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids($activityIds);
                            foreach ($activityIds as $activityId) {
                                if(isset($feedList[$activityId])) {
                                    foreach ($feedList[$activityId] as $feed) {
                                        if ($feed->feedType == 'user') {
                                            NEWSFEED_BOL_ActionFeedDao::getInstance()->deleteByFeedAndActivityId('user', $feed->feedId, $activityId);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function removeFeedsOfPrivateGroups(){
        $groups = GROUPS_BOL_GroupDao::getInstance()->findAll();
        foreach ($groups as $group) {
            if($group->whoCanView == GROUPS_BOL_Service::WCV_INVITE) {
                $this->removeFeedsOfPrivateGroupsByGroupId($group->getId());
            }
        }
    }

    public function onGroupDeletePostRemoveNotificationHandler(OW_Event $event){
        $params=$event->getParams();
        if(isset($params['entityType']) && $params['entityType']==self::GROUP_FEED_ENTITY_TYPE){
            OW::getEventManager()->call('notifications.remove', array(
                'entityType' => $params['entityType'],
                'entityId' => $params['entityId']
            ));
        }
    }

    public function groupStatusFlagRenderer(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['label']) & isset($params['entityType'])){
            if($params['entityType'] == self::GROUP_FEED_ENTITY_TYPE){
                $event->setData(array('label'=>OW::getLanguage()->text('base','ow_ic_script')));
            }
        }
    }

    public function onLikeNotification( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'groups-status' )
        {
            return;
        }

        $userId = $params['userId'];
        $userService = BOL_UserService::getInstance();

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);
        $status = (empty($actionData['data']['status'])
            ? $actionData['string']
            : empty($actionData['data']['status'])) ? null : $actionData['data']['status'];

        $contentImage = empty($actionData['contentImage']) ? null : $actionData['contentImage'];

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = NEWSFEED_BOL_Service::getInstance()->findActivity( NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $ownerId = $cActivity->userId;
        }
        else
        {
            $ownerId = $actionData['data']['userId'];
        }

        $url = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $action->id));

        if ( $ownerId != $userId )
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, true, false);

            $stringKey = empty($status)
                ? 'newsfeed+email_notifications_empty_status_like'
                : 'newsfeed+email_notifications_status_like';

            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'newsfeed',
                'action' => 'newsfeed-status_like',
                'entityType' => 'status_like',
                'entityId' => $data['likeId'],
                'userId' => $ownerId
            ), array(
                'format' => "text",
                'avatar' => $avatar[$userId],
                'string' => array(
                    'key' => $stringKey,
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'url' => $url
                    )
                ),
                'url' => $url
            ));

            OW::getEventManager()->trigger($event);
        }
    }

    public function setUserAsOwner( OW_Event $event )
    {
        $params = $event->getParams();


        if(isset($params['contextParentActionKey']) && isset($params['userId']) &&
            isset($params['groupOwnerId'])&& isset($params['groupId']) && isset($params['contextActionMenu'])){
            if ($params['userId'] != $params['groupOwnerId']) {
                $contextAction = new BASE_ContextAction();
                $contextAction->setParentKey($params['contextParentActionKey']);
                if ($params['groupOwnerId'] != $params['userId']) {
                    $isManager = false;
                    if (isset($params['managerIds'])) {
                        $isManager = in_array($params['userId'], $params['managerIds']);
                    } else {
                        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$params['groupId'], 'userId' => $params['userId']));
                        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
                        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
                            $isManager = $eventIisGroupsPlusManager->getData()['isUserManager'];
                        }
                    }

                    if($isManager){
                        $contextAction->setKey('set_user_as_owner');
                        $contextAction->setLabel(OW::getLanguage()->text('groups', 'set_user_as_owner_label'));
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $setOwnerUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'setUserAsOwner', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));

                        $contextAction->setUrl('javascript://');
                        $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'set_user_as_owner_confirmation'));
                        $contextAction->addAttribute('onclick', "return confirm_redirect($(this).data().message, '$setOwnerUrl')");
                        $contextAction->addAttribute('class', "set_user_as_owner_icon");
                        $params['contextActionMenu']->addAction($contextAction);
                    }
                }
            }
        }

    }

    public function setMobileUserAsOwner(OW_Event $event)
    {
        $params = $event->getParams();
        $additionalInfo = array();
        if (isset($params['additionalInfo'])) {
            $additionalInfo = $params['additionalInfo'];
        }
        if(isset($params['contextMenu']) && isset($params['userId']) &&
            isset($params['groupOwnerId'])&& isset($params['groupId'])){
            if ($params['userId'] != $params['groupOwnerId']) {
                if ($params['groupOwnerId'] != $params['userId']) {
                    $isManager=false;
                    $checkGroupManager = true;
                    if (isset($additionalInfo['cache']['groups_managers'][$params['groupId']])) {
                        $isManager = in_array($params['userId'], $additionalInfo['cache']['groups_managers'][$params['groupId']]);
                        $checkGroupManager = false;
                    }
                    if ($checkGroupManager) {
                        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$params['groupId'], 'userId' => $params['userId']));
                        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
                        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
                            $isManager = $eventIisGroupsPlusManager->getData()['isUserManager'];
                        }
                    }
                    if( $isManager ){
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $setOwnerUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_MCTRL_Groups', 'setUserAsOwner', array(
                            'groupId' => $params['groupId'],
                            'userId' => $params['userId']
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));
                        array_unshift($params['contextMenu'], array(
                            'label' => OW::getLanguage()->text('groups', 'set_user_as_owner_label'),
                            'attributes' => array(
                                'onclick' => 'return confirm_redirect($(this).data(\'confirm-msg\'), \''.$setOwnerUrl.'\');',
                                "data-confirm-msg" => OW::getLanguage()->text('groups', 'set_user_as_owner_confirmation')
                            ),
                            "class" => "owm_red_btn",
                            "order" => "3"
                        ));
                        $event->setData(array('contextMenu'=>$params['contextMenu']));

                    }
                }
            }
        }
    }

    public function selectGroup($isRequired = false)
    {
        $groupSelector = new Selectbox('gId');
        $grouplist = $this->findGroupList(GROUPS_BOL_Service::LIST_ALL);
        $option = array();
        $groupsNumber = count($grouplist);
        for ($i=0 ; $i < $groupsNumber ; $i++) {
            $option[$grouplist [$i]->id] = $grouplist [$i]-> title;// $group->title;
        }
        if($isRequired)
            $groupSelector->setRequired();
        $groupSelector ->setOptions($option);
        return  $groupSelector;
    }

    public function setGroupOwner($groupId, $userId){
    if(!isset($groupId) || !isset($userId) ){
        return;
    }
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ($group != null) {
            $previousOwner = $group->userId;
            $group->userId = $userId;
            GROUPS_BOL_Service::getInstance()->saveGroup($group);

            $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$groupId, 'userId' => $previousOwner));
            OW::getEventManager()->trigger($eventIisGroupsPlusManager);
            if( isset($eventIisGroupsPlusManager->getData()['isUserManager']) && !($eventIisGroupsPlusManager->getData()['isUserManager']) ){
                FRMGROUPSPLUS_BOL_Service::getInstance()->addUserAsManager($groupId, $previousOwner);
            }

        }
}
    public function onGroupUserLeave( OW_Event $event )
    {
        $params = $event->getParams();
        if(isset($params['groupDelete']) && $params['groupDelete'])
        {
            return;
        }
        $userId = $params['userIds'][0];
        $groupUserDto = $this->groupUserDao->findGroupUser($params["groupId"], $userId);
        $leaveFeedString = true;
        if (OW::getConfig()->configExists('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed')) {
            $fileUploadFeedValue = json_decode(OW::getConfig()->getValue('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed'));
            if (!in_array('leaveFeed', $fileUploadFeedValue)) {
                $leaveFeedString = false;
            }
        }
        $groupService = GROUPS_BOL_Service::getInstance();
        $groupService->updateLastTimeStampOfGroup($params["groupId"]);
        $group = $groupService->findGroupById($params["groupId"]);
        //in delete group: first group is deleted then the user deleted
        if (isset($group)) {
            $url = $groupService->getGroupUrl($group);
            $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

            if ($leaveFeedString) {
                $data = array(
                    'time' => time(),
                    'string' => array(
                        "key" => 'frmgroupsplus+feed_leave_string',
                        "vars" => array(
                            'groupTitle' => $title,
                            'groupUrl' => $url
                        )
                    ),
                    'view' => array(
                        'iconClass' => 'ow_ic_add'
                    ),
                    'data' => array(
                        'joinUsersId' => $userId
                    )
                );

                $event = new OW_Event('feed.action', array(
                    'feedType' => 'groups',
                    'feedId' => $group->id,
                    'entityType' => 'groups-leave',
                    'entityId' => $groupUserDto->getId(),
                    'pluginKey' => 'groups',
                    'userId' => $userId,
                    'visibility' => 8,
                ), $data);

                OW::getEventManager()->trigger($event);
            }
        }
    }

    public function newsfeedFeedRender(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['feedType']) || $params['feedType'] != 'groups' )
            return;
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.group.approve.status', $params);
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUnapprovedGroup'])  && $eventIisGroupsPlusManager->getData()['isUnapprovedGroup'] == true){
            return;
        }
        $this->updateLastSeenForGroupUser($params['feedId']);
    }

    public function newsfeedWidgetFeedParams(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['params']))
            return;
        if(!OW::getUser()->isAuthenticated()){
            return;
        }
        if(empty($params['params']->additionalParamList['entityId']) || empty($params['params']->additionalParamList['entity'])){
            return;
        }
        $feedId = $params['params']->additionalParamList['entityId'];
        $feedType = $params['params']->additionalParamList['entity'];
        if ($feedType != 'groups')
            return;
        $data = $event->getData();
        $currentCount = $data['displayCount'];
        $count = $this->getUnreadCountForGroupUser($feedId, true);
        $count = ($count < $currentCount)?$currentCount:$count;
        $count = ($count > NEWSFEED_CLASS_Driver::$MAX_ITEMS)?NEWSFEED_CLASS_Driver::$MAX_ITEMS:$count;
        $data['displayCount'] = $count;
        $event->setData($data);
    }

    public function genericItemRender(OW_Event $event)
    {
        $params = $event->getParams();
        $feedType = $params['feedType'];
        if (!isset($params['feedType']) || ($params['feedType'] != 'groups' && $params['feedType'] != 'my' &&  $params['feedType'] != 'site'))
            return;
        $data = $event->getData();
        $groupId=null;

        $group = null;
        if (isset($params['group'])) {
            $group = $params['group'];
        }

        if(isset($params['action']['data']['contextFeedType'])  && $params['action']['data']['contextFeedType']== 'groups')
        {
            if(isset($params['action']['data']['contextFeedId'])) {
                $groupId = $params['action']['data']['contextFeedId'];
            }
        }
        if(empty($params['action']['id']) || empty($params['action']['entityId']) || empty($params['action']['entityType'])){
            return;
        }
        $actionId = $params['action']['id'];
        $entityId = $params['action']['entityId'];
        $entityType = $params['action']['entityType'];
        if(!in_array($entityType, ['groups-status','user-status']) || empty($params['action']['data']['status'])){
            return;
        }

        $additionalInfo = array();
        if (isset($params['additionalInfo'])) {
            $additionalInfo = $params['additionalInfo'];
        }
        if (isset($params['cache'])) {
            $additionalInfo['cache'] = $params['cache'];
        }

        if ($groupId != null && isset($params['cache']['groups'][$groupId])) {
            $group = $params['cache']['groups'][$groupId];
        }

        $canReply = false;
        if (isset($params['additionalInfo']['canReplyInGroup'])) {
            $canReply = $params['additionalInfo']['canReplyInGroup'];
        } else {

            $otpEvent = OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form', ['groupId' => $groupId, 'group' => $group, 'additionalInfo' => $additionalInfo]));
            $isMemberOfGroup = false;
            if ($groupId != null) {
                $isMemberOfGroup = false;
                if (isset($params['cache']['users_groups']) && isset($params['cache']['users_groups'][OW::getUser()->getId()])) {
                    $isMemberOfGroup = in_array($groupId, $params['cache']['users_groups'][OW::getUser()->getId()]);
                } else {
                    $isMemberOfGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
                }
            }
            if (isset($otpEvent->getData()['canReply']) && $otpEvent->getData()['canReply'] && $isMemberOfGroup) {
                $canReply = true;
            }
        }
        if($canReply){
            $authorId = $params['action']['userId'];
            $authorDisplayName = BOL_UserService::getInstance()->getDisplayName($authorId);

            $text = OW::getLanguage()->text('groups', 'in_reply_to', ['author'=>$authorDisplayName]);

            $actionOnClick = "addPostReplyTo($actionId, '$text')";
            $replyToUsername = BOL_UserService::getInstance()->findUserById($event->getParams()['action']['userId'])->getUsername();
            $replyToId = $event->getParams()['action']['id'];
            if ($params['action']['entityType'] == 'user-status'){
                $reloadUrl = OW::getRouter()->urlForRoute('base_member_dashboard') . '?replyToUsername=' . $replyToUsername . '&replyToId=' . $replyToId;
                $actionOnClick = "window.location = '" . $reloadUrl . "'";
            } else if ($params['action']['entityType'] == 'groups-status' && ($feedType == 'site' || $feedType == 'my')){
                $action = null;
                if (isset($params['cache']['actions'][$event->getParams()['action']['id']])) {
                    $action = $params['cache']['actions'][$event->getParams()['action']['id']];
                }
                if ($action == null) {
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findActionById($event->getParams()['action']['id']);
                }
                $groupUrl = json_decode($action->data)->context->url;
                $reloadUrl = $groupUrl . '?replyToUsername=' . $replyToUsername . '&replyToId=' . $replyToId;
                $actionOnClick = "window.location = '" . $reloadUrl . "'";
            }

            array_unshift($data['contextMenu'], array(
                'label' => OW::getLanguage()->text('groups', 'reply_to'),
                'attributes' => array(
                    'onClick' => $actionOnClick,
                    'data-entity-id' => $entityId,
                ),
                "class" => "groups_reply_to"
            ));
        }

        $event->setData($data);
    }

    public function generateDefaultImageUrl()
    {
        return OW::getPluginManager()->getPlugin('groups')->getStaticUrl() . 'images/group_default_image.svg';
    }

    public function getUserSearchForm()
    {
        $searchForm = new Form('searchUserForm');
        $searchForm->setMethod(Form::METHOD_GET);
        $searchField = new TextField('searchValue');
        $searchField->setInvitation(OW::getLanguage()->text('base','search_users'));
        $searchField->setHasInvitation(true);
        $searchField->setId('searchValue');
        $searchForm->addElement($searchField);
        //TODO because in submit GET form csrf parameters and all unnecessary data passed to url, these codees are commented for now.
        //$submit = new Submit('searchUsers');
        //$submit->setValue(OW::getLanguage()->text('admin', 'search'));
        //$searchForm->addElement($submit);
        return $searchForm;
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


    /**
     * @param int $userId
     * @return bool
     */
    public function canUserModerateThisUserByQuestionRole($userId,$status)
    {
        if(isset( $this->isQuestionRolesModerator ))
        {
            return  $this->isQuestionRolesModerator;
        }
        $this->isQuestionRolesModerator=false;
        $approveSettingEnableEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.check.status.and.approve.setting.enable',array('groupStatus'=>$status)));
        if(isset($approveSettingEnableEvent->getData()['roleModeratorCanCheck']) && $approveSettingEnableEvent->getData()['roleModeratorCanCheck']) {
            $questionRolesModeratorIds = OW::getEventManager()->trigger(
                new OW_Event(FRMEventManager::FIND_MODERATOR_FOR_USER,
                    array('userId' => $userId), array()));
            $this->isQuestionRolesModerator = in_array(OW::getUser()->getId(),$questionRolesModeratorIds->getData());
        }

        return $this->isQuestionRolesModerator;
    }

    /**
     * @return mixed
     */
    public function getUserRolesToManageSpecificUsers() {
        $canUserModerateThisUserByQuestionRole = OW::getEventManager()->trigger(new OW_Event('frmquestionroles.getUserRolesToManageSpecificUsers', array('userId' => OW::getUser()->getId())));
        $canUserModerateThisUserByQuestionRole = $canUserModerateThisUserByQuestionRole->getData();
        return $canUserModerateThisUserByQuestionRole;
    }

    /**
     * @param array|null $userRoles
     * @return bool
     */
    public function checkIfUserHasRolesToManageSpecificUsers($userRoles=null) {
       if(isset($this->isQuestionRolesModerator))
       {
           return $this->isQuestionRolesModerator;
       }
        if (is_null($userRoles)) {
            $this->isQuestionRolesModerator = !empty($this->getUserRolesToManageSpecificUsers());
        }
        return $this->isQuestionRolesModerator;
    }

}