<?php
/**
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Service
{
    const VISIBILITY_SITE = 1;
    const VISIBILITY_FOLLOW = 2;
    const VISIBILITY_AUTHOR = 4;
    const VISIBILITY_FEED = 8;

    const VISIBILITY_FULL = 15;

    const ACTION_STATUS_ACTIVE = 'active';
    const ACTION_STATUS_INACTIVE = 'inactive';

    const PRIVACY_EVERYBODY = 'everybody';
    const PRIVACY_ACTION_VIEW_MY_FEED = 'view_my_feed';
    const PRIVACY_FRIENDS = 'friends_only';
    const PRIVACY_ONLY_ME = 'only_for_me';

    const SYSTEM_ACTIVITY_CREATE = 'create';
    const SYSTEM_ACTIVITY_SUBSCRIBE = 'subscribe';

    public $SYSTEM_ACTIVITIES = array(
        self::SYSTEM_ACTIVITY_CREATE,
        self::SYSTEM_ACTIVITY_SUBSCRIBE
    );
    
    const EVENT_BEFORE_ACTION_DELETE = "feed.before_action_delete";
    const EVENT_AFTER_ACTION_ADD = "feed.after_action_add";

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NEWSFEED_BOL_Service
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
     * @var NEWSFEED_BOL_ActionDao
     */
    private $actionDao;

    /**
     *
     * @var NEWSFEED_BOL_FollowDao
     */
    private $followDao;

    /**
     *
     * @var NEWSFEED_BOL_ActionFeedDao
     */
    private $actionFeedDao;

    /**
     *
     * @var NEWSFEED_BOL_LikeDao
     */
    private $likeDao;

    /**
     *
     * @var NEWSFEED_BOL_StatusDao
     */
    private $statusDao;

    /**
     *
     * @var NEWSFEED_BOL_ActivityDao
     */
    private $activityDao;

    /**
     *
     * @var NEWSFEED_BOL_UserActionDao
     */
    private $actionSetDao;

    private function __construct()
    {
        $this->actionDao = NEWSFEED_BOL_ActionDao::getInstance();
        $this->actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $this->followDao = NEWSFEED_BOL_FollowDao::getInstance();
        $this->likeDao = NEWSFEED_BOL_LikeDao::getInstance();
        $this->statusDao = NEWSFEED_BOL_StatusDao::getInstance();
        $this->activityDao = NEWSFEED_BOL_ActivityDao::getInstance();
        $this->actionSetDao = NEWSFEED_BOL_ActionSetDao::getInstance();
    }

    public function saveAction( NEWSFEED_BOL_Action $action )
    {
        $this->actionDao->save($action);

        return $action;
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @return NEWSFEED_BOL_Action
     */
    public function findAction( $entityType, $entityId )
    {
        $dto = $this->actionDao->findAction($entityType, $entityId);

        return $dto;
    }

    /**
     *
     * @param int $actionId
     * @return NEWSFEED_BOL_Action
     */
    public function findActionById( $actionId )
    {
        $dto = $this->actionDao->findById($actionId);

        return $dto;
    }

    /**
     * @param $actionIds
     * @return array
     */
    public function findActionByIds( $actionIds )
    {
        if (empty($actionIds)) {
            return array();
        }
        $actions = array();
        $list = $this->actionDao->findByIdList($actionIds);
        foreach ($list as $item) {
            $actions[$item->id] = $item;
        }
        return $actions;
    }

    public function onRabbitMQLogRelease( OW_Event $event ){
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }

        $data = $data->body;
        $data = (array) json_decode($data);

        if (!isset($data['itemType']) || $data['itemType'] != 'remove_feed') {
            return;
        }
        $this->removeAction($data['entityType'], $data['entityId']);
    }

    public function removeAction( $entityType, $entityId, $dto = null)
    {
        if ($dto == null) {
            $dto = $this->actionDao->findAction($entityType, $entityId);
        }

        if ( $dto === null )
        {
            return;
        }

        $groupId = null;

        $event = new OW_Event(self::EVENT_BEFORE_ACTION_DELETE, array(
            "actionId" => $dto->id,
            "entityType" => $dto->entityType,
            "entityId" => $dto->entityId
        ));
        OW::getEventManager()->trigger($event);

        BOL_VoteDao::getInstance()->deleteEntityItemVotes($dto->entityId, $dto->entityType);
        $this->actionDao->delete($dto);

        $activityIds = $this->activityDao->findIdListByActionIds(array($dto->id));

        $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids($activityIds);
        foreach ($activityIds as $activityId) {
            if(isset($feedList[$activityId])) {
                foreach ($feedList[$activityId] as $feed) {
                    if ($feed->feedType == 'groups') {
                        $groupId = $feed->feedId;
                    }
                }
            }
        }

        $this->actionFeedDao->deleteByActivityIds($activityIds);
        $this->activityDao->deleteByIdList($activityIds);

        $commentEntity = BOL_CommentService::getInstance()->findCommentEntity($dto->entityType, $dto->entityId);

        if ( !empty($commentEntity) && $commentEntity->pluginKey == 'newsfeed' )
        {
            BOL_CommentService::getInstance()->deleteEntityComments($commentEntity->entityType, $commentEntity->entityId);
            BOL_CommentService::getInstance()->deleteCommentEntity($commentEntity->id);
        }

        $actionData = json_decode($dto->data, true);

        // delete attachments
        if( !empty($actionData['attachmentId']) )
        {
            BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("newsfeed", $actionData['attachmentId']);
        }

        if (FRMSecurityProvider::isSocketEnable()) {
            if ($groupId != null && FRMSecurityProvider::checkPluginActive('groups', true)) {
                $groupUsers = GROUPS_BOL_GroupUserDao::getInstance()->findUserIdsByGroupId($groupId);
                $socketData = array();
                $socketData['type'] = 'delete_post';
                $socketData['params']= array('feedId' => (int) $groupId, 'feedType' => 'groups','actionId' => $dto->id, 'entityType' => $dto->entityType, 'entityId' => $dto->entityId);
                OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $groupUsers)));
            }
        }
    }

    public function removeActionById( $id )
    {
        /* @var $dto NEWSFEED_BOL_Action */
        $dto = $this->actionDao->findById($id);

        if ( $dto === null  )
        {
            return;
        }

        $this->removeAction($dto->entityType, $dto->entityId);
        OW::getLogger()->writeLog(OW_Log::INFO, 'delete_action', ['actionType'=>OW_Log::DELETE, 'enType'=>'newsfeed', 'enId'=>$id]);
    }

    public function removeActionListByPluginKey( $pluginKey )
    {
        $list = $this->actionDao->findByPluginKey($pluginKey);

        foreach ( $list as $dto )
        {
            /* @var $dto NEWSFEED_BOL_Action */
            $this->removeAction($dto->entityType, $dto->entityId);
        }
    }

    public function findExpiredActions( $inactivePeriod )
    {
        $this->actionDao->findExpired($inactivePeriod);
    }

    public function setActionStatusByPluginKey( $pluginKey, $status )
    {
        $this->actionDao->setStatusByPluginKey($pluginKey, $status);
    }


    // Activity

    public function saveActivity( NEWSFEED_BOL_Activity $activity, $action = null)
    {
        $frmEventSecurity = new OW_Event('newsfeed.activity.visibility', array('activity' => $activity, 'action' => $action));
        OW::getEventManager()->trigger($frmEventSecurity);
        if(isset($frmEventSecurity->getData()['visibilityChanged'])){
            $activity->visibility=$frmEventSecurity->getData()['visibilityChanged'];
        }
        $this->activityDao->saveOrUpdate($activity);

        return $activity;
    }

    public function addActivityToFeed( NEWSFEED_BOL_Activity $activity, $feedType, $feedId, $action = null)
    {
        $actionFeed = new NEWSFEED_BOL_ActionFeed();
        $actionFeed->activityId = (int) $activity->id;
        $actionFeed->feedType = trim($feedType);
        $actionFeed->feedId = (int) $feedId;

        $createFeed = true;
        $createFeedEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ACTIVITY_CREATE,array('action' => $action, 'activityType' => $activity->activityType, 'actionId' => $activity->actionId)));
        if(isset($createFeedEvent->getData()['createFeed'])){
            $createFeed = $createFeedEvent->getData()['createFeed'];
        }

        if($createFeed) {
            $this->actionFeedDao->addIfNotExists($actionFeed);
        }
        return $actionFeed;
    }

    public function deleteActivityFromFeed( $activityId, $feedType, $feedId )
    {
        $this->actionFeedDao->deleteByFeedAndActivityId($feedType, $feedId, $activityId);
    }
    
    public function findFeedListByActivityids( $activityIds )
    {
        $list = $this->actionFeedDao->findByActivityIds($activityIds);
        $out = array();
        foreach ( $list as $af )
        {
            $out[$af->activityId] = isset($out[$af->activityId]) 
                    ? $out[$af->activityId] : array();
            
            $out[$af->activityId][] = $af;
        }
        
        return $out;
    }
    
    /**
     *
     * @param string $activityType
     * @param int $activityId
     * @param int $actionId
     * @return NEWSFEED_BOL_Activity
     */
    public function findActivityItem( $activityType, $activityId, $actionId )
    {
        return $this->activityDao->findActivityItem($activityType, $activityId, $actionId);
    }

    public function processActivityKey( $activityKey, $context = null )
    {
        $params = array();
        $keys = array();

        $_keys = is_array($activityKey) ? $activityKey : explode(',', $activityKey);
        foreach ( $_keys as $key )
        {
            $_key = is_array($key) ? $key : explode(',', $key);
            $keys = array_merge($keys, $_key);
        }

        foreach ( $keys as $key )
        {
            $params[] = $this->parseActivityKey($key, $context);
        }

        return $params;
    }

    public function parseActivityKey( $key, $context = null )
    {
        $key = str_replace('*', '', $key);

        $temp = explode(':', $key);

        $userId = empty($temp[2]) ? null : $temp[2];
        $actionKey = empty($temp[1]) ? null : $temp[1];
        $activityKey = empty($temp[0]) ? null : $temp[0];

        $out = array(
            'action' => array( 'entityType' => null, 'entityId' => null, 'id' => null ),
            'activity' => array( 'activityType' => null, 'activityId' => null, 'id' => null, 'userId' => $userId)
        );

        if ( is_numeric($actionKey) && strpos($actionKey, '.') === false )
        {
            $out['action']['id'] = $actionKey;
        }
        else
        {
            $temp = explode('.', $actionKey);

            $out['action']['entityType'] = $temp[0];
            $out['action']['entityId'] = empty($temp[1]) ? null : $temp[1];

        }

        if ( is_numeric($activityKey) && strpos($activityKey, '.') === false )
        {
            $out['activity']['id'] = $activityKey;
        }
        else
        {
            $temp = explode('.', $activityKey);
            $out['activity']['activityType'] = empty($temp[0]) ? null : $temp[0];
            $out['activity']['activityId'] = empty($temp[1]) ? null : $temp[1];
        }

        if ( !empty($context) )
        {
            $context = $this->parseActivityKey( $context );
            foreach ( $context as $k => $c )
            {
                $out[$k] = array_merge($c, array_filter($out[$k]));
            }
        }

        return $out;
    }

    public function testActivityKey( $key, $testKey, $all = false )
    {
        $key = $this->parseActivityKey($key);
        $testKey= $this->processActivityKey($testKey);

        $result = true;
        foreach ( $testKey as $tk )
        {
            $result = true;
            foreach ( $tk as $type => $f )
            {
                foreach ( $f as $k => $v )
                {
                    $r = empty($key[$type][$k]) ? true : empty($v) || $key[$type][$k] == $v;
                    if ( !$r )
                    {
                        $result = false;

                        break 2;
                    }
                }
            }

            if ( $result && !$all || !$result && $all)
            {
                break;
            }
        }

        return $result;
    }

    /**
     * Find activity by special key
     *
     * [activityType].[activityId]:[entityType].[entityId]:[userId]
     * 
     * @param $activityKey
     * @return array
     */
    public function findActivity( $activityKey, $context = null )
    {
        $params = $this->processActivityKey($activityKey, $context);

        return $this->activityDao->findActivity($params);
    }

    public function findActionsByFeedTypeAndFeedId( $entityType, $entityId)
    {
        return $this->activityDao->findActionsByFeedTypeAndFeedId( $entityType, $entityId);
    }


    public function updateActivity( $activityKey, $updateFields, $context = null )
    {
        if ( empty($updateFields) )
        {
            return;
        }

        $params = $this->processActivityKey($activityKey, $context);

        return $this->activityDao->updateActivity($params, $updateFields);
    }

    public function removeActivity( $activityKey, $context = null )
    {
        $params = $this->processActivityKey($activityKey, $context);

        $this->activityDao->deleteActivity($params);
    }

    public function setActivityPrivacy( $activityKeys, $privacy, $userId )
    {
        $this->updateActivity($activityKeys, array('privacy' => $privacy), '*:*:' . $userId);
    }


    //Follow

    public function isFollow( $userId, $feedType, $feedId, $permission = self::PRIVACY_EVERYBODY )
    {
        return $this->followDao->findFollow($userId, $feedType, $feedId, $permission) !== null;
    }

    public function isFollowByFeedIds( $userId, $feedType, $feedIds )
    {
        return $this->followDao->findFollows($userId, $feedType, $feedIds);
    }

    public function findFollowByFeedList( $userId, $feedList, $permission = self::PRIVACY_EVERYBODY )
    {
        $follows = $this->followDao->findFollowByFeedList($userId, $feedList, $permission);

        $out = array();
        foreach ( $follows as $follow )
        {
            $out[$follow->feedType . $follow->feedId] = $follow;
        }

        return $out;
    }

    public function isFollowList( $userId, $feedList, $permission = self::PRIVACY_EVERYBODY )
    {
        $follows = $this->findFollowByFeedList($userId, $feedList, $permission);

        $out = array();
        foreach ( $feedList as $feed )
        {
            if ( !isset($out[$feed["feedType"]]) )
            {
                $out[$feed["feedType"]] = array();
            }

            $out[$feed["feedType"]][$feed["feedId"]] = !empty($follows[$feed["feedType"].$feed["feedId"]]);
        }

        return $out;
    }

    public function findFollowList( $feedType, $feedId, $permission = null )
    {
        return $this->followDao->findList($feedType, $feedId, $permission);
    }

    public function addFollow( $userId, $feedType, $feedId, $permission = self::PRIVACY_EVERYBODY )
    {
        return $this->followDao->addFollow($userId, $feedType, $feedId, $permission);
    }

    public function removeFollow( $userId, $feedType, $feedId, $permission = null )
    {
        return $this->followDao->removeFollow($userId, $feedType, $feedId, $permission);
    }

    public function isLiked( $userId, $entityType, $entityId )
    {
        return $this->likeDao->findLike($userId, $entityType, $entityId) !== null;
    }

    public function findEntityLikesCount( $entityType, $entityId )
    {
        return $this->likeDao->findCountByEntity($entityType, $entityId);
    }

    public function findUserLikes( $userId )
    {
        return $this->likeDao->findByUserId($userId);
    }

    /**
     * @deprecated
     * @see BOL_VoteService::findEntityLikes
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function findEntityLikes($entityType, $entityId )
    {
        return $this->likeDao->findByEntity($entityType, $entityId);
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::findLikesByEntityList
     * @param $entityList
     * @return array
     */
    public function findLikesByEntityList($entityList )
    {
        $list = $this->likeDao->findByEntityList($entityList);

        $out = array();
        foreach ( $list as $likeDto )
        {
            $out[$likeDto->entityType][$likeDto->entityId][] = $likeDto;
        }

        return $out;
    }

    public function findEntityLikeUserIds( $entityType, $entityId )
    {
        $likes = BOL_VoteService::getInstance()->findEntityLikes($entityType, $entityId);
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

        return $out;
    }

    public function addLike( $userId, $entityType, $entityId, $vote )
    {
        $like = BOL_VoteDao::getInstance()->addLike($userId, $entityType, $entityId, $vote);
        $event = new OW_Event('feed.after_like_added', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => $userId,
            'vote'=>$vote
        ), array(
            'likeId' => $like->id
        ));
        OW::getEventManager()->trigger($event);
        return $like;
    }

    /**
     * @deprecated
     * @see BOL_VoteService::removeVote
     * @param $userId
     * @param $entityType
     * @param $entityId
     * @return int
     */
    public function removeLike($userId, $entityType, $entityId )
    {
        return $this->likeDao->removeLike($userId, $entityType, $entityId);
    }

    /**
     * @deprecated
     * @see BOL_VoteService::findUserVote
     * @param $userId
     * @param $entityType
     * @param $entityId
     * @return mixed
     */
    public function findLike($userId, $entityType, $entityId )
    {
        return $this->likeDao->findLike($userId, $entityType, $entityId);
    }

    /**
     * @deprecated
     * @see BOL_VoteService::deleteUserVotes
     * @param $userId
     */
    public function removeLikesByUserId($userId )
    {
        $this->likeDao->removeLikesByUserId($userId);
    }

    public function removeActivityByUserId( $userId )
    {
        $this->activityDao->deleteByUserId($userId);
    }

    /***
     * @param $actionId
     * @return bool
     */
    public function canCurrentUserViewAction($actionId){
        $action = NEWSFEED_BOL_Service::getInstance()->findActionById($actionId);
        if(empty($action)) {
            return false;
        }
        if (OW::getUser()->isAdmin()) {
            return true;
        }
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized($action->pluginKey)) {
            return true;
        }
        try{
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('actionId' => $actionId)));
        }catch (Exception $e){
            return false;
        }
        return true;
    }

    public function addStatus( $userId, $feedType, $feedId, $visibility, $status, $data = array() )
    {
        $statusDto = NEWSFEED_BOL_Service::getInstance()->saveStatus($feedType, $feedId, $status);

        if(!empty($_POST['reply_to'])){
            if(!$this->canCurrentUserViewAction($_POST['reply_to'])){
                unset($_POST['reply_to']);
                unset($data['reply_to']);
            }else {
                $data['reply_to'] = $_POST['reply_to'];
            }
        }
        $data["statusId"] = (int) $statusDto->id;
        $data["status"] = $status;
        
        $event = new OW_Event('feed.after_status_update', array(
            'feedType' => $feedType,
            'feedId' =>  $feedId,
            'visibility' => (int) $visibility,
            'userId' => $userId
        ), $data);

        OW::getEventManager()->trigger($event);
        $entityType=$feedType . '-status';
        $entityId=$statusDto->id;
        $context = null;
        OW::getEventManager()->trigger( new OW_Event('feed.hashtag', array('entityId'=>$entityId,'entityType'=>$entityType,'context' => $context)) );

        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.on.update.group.status', array('feedId' => $feedId,
            'feedType' => $feedType, 'status' => $status, 'statusId'=>$entityId,'entityType'=>$entityType));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);

        return array(
            'entityType' => $feedType . '-status',
            'entityId' => $statusDto->id
        );
    }
    
    public function saveStatus( $feedType, $feedId, $status )
    {
        return $this->statusDao->saveStatus($feedType, $feedId, $status);
    }

    public function getStatus( $feedType, $feedId )
    {
        $dto = $this->findStatusDto( $feedType, $feedId );

        if ( $dto === null )
        {
            return null;
        }

        return $dto->status;
    }

    /**
     *
     * @param $feedType
     * @param $feedId
     * @return NEWSFEED_BOL_Status
     */
    public function findStatusDto( $feedType, $feedId )
    {
        return $this->statusDao->findStatus( $feedType, $feedId );
    }

    /**
     *
     * @param $feedType
     * @param $feedId
     * @return NEWSFEED_BOL_Status
     */
    public function findStatusDtoById( $statusId )
    {
        return $this->statusDao->findById($statusId);
    }

    public function removeStatus( $feedType, $feedId )
    {
        $this->statusDao->removeStatus($feedType, $feedId);
    }

    public function findActionsByUserId( $userId )
    {
        return $this->actionDao->findListByUserId($userId);
    }

    //Privacy

    private $privacy = array();

    public function collectPrivacy()
    {
        $event = new BASE_CLASS_EventCollector('feed.collect_privacy');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $item )
        {
            $key = $item[0];
            $privacyAction = $item[1];
            $this->privacy[$privacyAction][] = $key;
        }
    }

    public function getActivityKeysByPrivacyAction( $privacyAction )
    {
        return empty($this->privacy[$privacyAction]) ? array() : $this->privacy[$privacyAction];
    }

    public function getPrivacyActionByActivityKey( $activityKey )
    {
        foreach ( $this->privacy as $action => $keys )
        {
            if ( $this->testActivityKey($activityKey, $keys) )
            {
                return $action;
            }
        }

        return null;
    }

    /**
     * use only for cron jobs
     *
     * @param int $timestamp
     */

    public function deleteActionSetByTimestamp($timestamp)
    {
        $this->actionSetDao->deleteActionSetByTimestamp($timestamp);
    }

    public function deleteActionSetByUserId($userId)
    {
        $this->actionSetDao->deleteActionSetUserId($userId);
        BOL_PreferenceService::getInstance()->savePreferenceValue(NEWSFEED_BOL_ActionDao::CACHE_TIMESTAMP_PREFERENCE, 0, $userId);
    }

    public function clearUserFeedCahce( $userId )
    {
        //BOL_PreferenceService::getInstance()->savePreferenceValue(NEWSFEED_BOL_ActionDao::CACHE_TIMESTAMP_PREFERENCE, 0, $userId);

        $this->clearCache();
    }

    public function clearCache()
    {
        OW::getCacheManager()->clean(array(
            NEWSFEED_BOL_ActionDao::CACHE_TAG_ALL,
            NEWSFEED_BOL_ActionDao::CACHE_TAG_INDEX,
            NEWSFEED_BOL_ActionDao::CACHE_TAG_USER,
            NEWSFEED_BOL_ActionDao::CACHE_TAG_FEED
        ));
    }

    public function getActionPermalink( $actionId, $feedType = null, $feedId = null )
    {
        $url = OW::getRouter()->urlForRoute('newsfeed_view_item', array(
            'actionId' => $actionId
        ));

        return OW::getRequest()->buildUrlQueryString($url, array(
            'ft' => $feedType,
            'fi' => $feedId
        ));
    }
    public function removeLikeNotifications($entityType,$entityId){
        $likes = BOL_VoteService::getInstance()->findEntityLikes($entityType, $entityId);
        foreach ($likes as $like){
            OW::getEventManager()->trigger(new OW_Event('notifications.remove', array(
                'entityType' => 'status_like',
                'entityId' => $like->id
            )));
        }
    }

    public function findActionListByEntityIdsAndEntityType($entityIdList, $entityType){
        return $this->actionDao->findActionListByEntityIdsAndEntityType($entityIdList, $entityType);
    }

    public function deleteActionByList($actionList){
        foreach ($actionList as $action){
            if ($action->entityType == 'groups-status' || $action->entityType == 'user-status'){
                $this->removeLikeNotifications($action->entityType, $action->entityId);
                OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
                    "entityType" => $action->entityType,
                    "entityId" => $action->entityId
                )));
            }
            $this->removeActionById($action->id);
            OW_EventManager::getInstance()->trigger(new OW_Event('hashtag.on_entity_change', array('entityType' => $action->entityType, 'entityId'=>$action->entityId, 'actionId' => $action->id)));
        }
    }

    public function updateGroupFeedsPrivacy(OW_Event $event)
    {
        $params= $event->getParams();
        if(!isset($params['groupPrivacy']))
        {
            return;
        }
        /**
         * set public value as initial
         */
        $visibility=15;
        if($params['groupPrivacy']==GROUPS_BOL_Service::WCV_INVITE)
        {
            $visibility=14;
        }
        $actionList = NEWSFEED_BOL_ActionDao::getInstance()->findByFeed('groups',122);
        foreach ( $actionList as $action )
        {
            $createActivity =FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getCreatorActivityOfActionById($action->id);
            if($createActivity->visibility!=$visibility)
            {
                $createActivity->visibility =$visibility;
                NEWSFEED_BOL_ActivityDao::getInstance()->saveOrUpdate($createActivity);
            }
        }
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function unreadCountForGroup(OW_Event $event){
        $params = $event->getParams();
        $dbp = OW_DB_PREFIX;

        $userCondition = '';
        if(!isset($params['includeAuthored']) || !$params['includeAuthored']){
            $userCondition = " AND activity.userId<>:uId ";
        }

        $lastSeenActionCondition = 'gu.last_seen_action';
        if (isset($params['lastSeenAction'])) {
            $lastSeenActionCondition = OW::getDbo()->escapeValue($params['lastSeenAction']);
        }


        $query = "SELECT action_feed.feedId as gID, COUNT(*) as `CNT`
            FROM (SELECT * FROM {$dbp}newsfeed_action_feed WHERE feedType='groups') as action_feed
            INNER JOIN {$dbp}newsfeed_activity activity ON action_feed.activityId=activity.id
            INNER JOIN (SELECT * FROM {$dbp}groups_group_user WHERE userId = :uId) gu ON action_feed.feedId=gu.groupId
            WHERE activity.timeStamp > " . $lastSeenActionCondition . " " . $userCondition . "
            GROUP BY action_feed.feedId";

        $q_params['uId'] = $params['userId'];

        if(isset($params['only_count']) && $params['only_count']){
            $query = 'SELECT count(*) as CNT from ('. $query . ') as t1';
            $result = OW::getDbo()->queryForList($query, $q_params);
            $event->setData(['count'=>$result[0]['CNT']]);
        }else{
            $result = OW::getDbo()->queryForList($query, $q_params);$items = [];
            foreach($result as $item){
                $items[$item['gID']] = $item['CNT'];
            }
            $event->setData(['items'=>$items]);
        }
    }

    public function checkShowChatFormActive(OW_Event $event )
    {
        $validDashboardActions=array('dashboard','loadItemList','loadItem');
        $whiteListController=array('NEWSFEED_MCTRL_Ajax','NEWSFEED_CTRL_Ajax');
        $whiteListAction=array('loadItemList');
        $validGroupListController=array('GROUPS_MCTRL_Groups');
        $validGroupListAction=array('view');
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        $ctrlKey=$attr[OW_RequestHandler::ATTRS_KEY_CTRL];
        $actionKey=$attr[OW_RequestHandler::ATTRS_KEY_ACTION];
        $eventData=$event->getData();
        $params = $event->getParams();
        $checkDashboardForWhiteList=false;
        $checkGroupForWhiteList=false;
        $groupId=null;
        $additionalInfo = array();
        if (isset($params['additionalInfo'])) {
            $additionalInfo = $params['additionalInfo'];
        }
        if (isset($params['cache'])) {
            $additionalInfo['cache'] = $params['cache'];
        }
        $mobileVersion=false;
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true)
        {
            $mobileVersion=true;
        }

        $group = null;
        if(isset($params['group'])){
            $group = $params['group'];
        }

        if(isset($params['groupId']))
        {
            $groupId = $params['groupId'];
        }
        else if (isset($params['action']) && $params['action']->getActivity("create")!=null ) {
            $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
            $createActivity = null;

            $createActivity = $params['action']->getActivity("create");
            $createActivityId = $createActivity->id;
            $actionFeed = null;
            if (isset($params['cache']['feed_by_creator_activity']) && array_key_exists($createActivityId, $params['cache']['feed_by_creator_activity'])) {
                if (isset($params['cache']['feed_by_creator_activity'][$createActivityId])) {
                    $actionFeedItem = $params['cache']['feed_by_creator_activity'][$createActivityId];
                    if($actionFeedItem->feedType == "groups"){
                        $actionFeed = $actionFeedItem;
                    }
                }
            } else {
                $actionFeeds = $actionFeedDao->findByActivityIds(array($createActivityId));
                if (!empty($actionFeeds)){
                    foreach ($actionFeeds as $actionFeedItem){
                        if($actionFeedItem->feedType == "groups"){
                            $actionFeed = $actionFeedItem;
                            break;
                        }
                    }
                }
            }
            if ($actionFeed != null) {
                $groupId = $actionFeed->feedId;
            }
        }
        else if(in_array($actionKey,$whiteListAction) && in_array($ctrlKey,$whiteListController) && $mobileVersion)
        {
            if(isset($_GET['p']))
            {
                $jsonData=json_decode($_GET['p']);
                if($jsonData->data->feedType=='my')
                {
                    $checkDashboardForWhiteList=true;
                }
                if($jsonData->data->feedType=='groups')
                {
                    $checkGroupForWhiteList=true;
                }
            }
        }

        else if(in_array($actionKey,$validGroupListAction) && in_array($ctrlKey,$validGroupListController))
        {
            $checkGroupForWhiteList=true;
        }
        else {
            if ($mobileVersion && isset($params['feedType']) && $params['feedType'] == 'groups') {
                $checkGroupForWhiteList = true;
            }
        }
        if(isset($params['includeWebService']) || $checkGroupForWhiteList || isset($groupId))
        {
            $eventData['hideCommentFeatures']=$this->checkDisableComment();
            $eventData['hideLikeFeatures']=$this->checkDisableLike();
            $showGroupChatForm = OW::getConfig()->getValue('newsfeed', 'showGroupChatForm');
            $eventData['canReply'] = $this->checkAddReplyFeature($groupId, $group, $additionalInfo);
            if (isset($showGroupChatForm) && $showGroupChatForm=="on") {
                if($mobileVersion) {
                    $eventData['showOtpForm'] = true;
                }
                $eventData['showGroupChatForm'] = true;
            }
        }

        if(!isset($groupId) && (in_array($actionKey,$validDashboardActions) || isset($params['includeWebService']) || $checkDashboardForWhiteList))
        {
            $showDashboardChatForm = OW::getConfig()->getValue('newsfeed', 'showDashboardChatForm');
            $eventData['hideCommentFeatures']=$this->checkDisableComment();
            $eventData['hideLikeFeatures']=$this->checkDisableLike();
            $eventData['canReply']= $this->checkAddReplyFeature();
            if (isset($showDashboardChatForm) && $showDashboardChatForm=="on") {
                if($mobileVersion) {
                    $eventData['showOtpForm'] = true;
                }
                $eventData['showDashboardChatForm']=true;
            }
        }
        $groupPage = false;
        if(isset($_GET['p'])) {
            $jsonData = json_decode($_GET['p']);
            if (isset($jsonData->data)  && isset($jsonData->data->feedType)  && $jsonData->data->feedType == 'groups') {
                $groupPage = true;
            }
        }

        if (in_array($actionKey,$validDashboardActions) && !$groupPage){
            $removeDashboardStatusForm = OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm');
            if (isset($removeDashboardStatusForm) && $removeDashboardStatusForm=="on") {
                $eventData['removeDashboardStatusForm']=true;
            }
        }

        $event->setData($eventData);

    }


    public function checkAddReplyFeature($groupId = null, $group = null, $additionalInfo = array())
    {
        $addReply=false;
        $addReplyFeatureConfig = OW::getConfig()->getValue('newsfeed', 'addReply');
        if (isset($addReplyFeatureConfig) && $addReplyFeatureConfig=="on") {
            $addReply=true;
            if (isset($groupId)) {
                $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',
                    array('groupId' =>$groupId, 'group' => $group, 'additionalInfo' => $additionalInfo)));
                $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
                if (!isset($isChannelParticipant) || !$isChannelParticipant) {
                    $addReply = true;
                }
            }
        }
        return $addReply;
    }

    /**
     * @return bool
     */
    public function checkDisableComment()
    {
        $disableComments=false;
        $disableCommentsConfig = OW::getConfig()->getValue('newsfeed', 'disableComments');
        if (isset($disableCommentsConfig) && $disableCommentsConfig=="on") {
            $disableComments = true;
        }
        return $disableComments;
    }

    /**
     * @return bool
     */
    public function checkDisableLike()
    {
        $disableLike=false;
        $disableLikeConfig = OW::getConfig()->getValue('newsfeed', 'disableLikes');
        if (isset($disableLikeConfig) && $disableLikeConfig=="on") {
            $disableLike = true;
        }
        return $disableLike;
    }



    /**
     * @param OW_Event $event
     */
    public function checkChangeHeaderName(OW_Event $event)
    {
        $removeDashboardStatusForm = OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm');
        if (isset($removeDashboardStatusForm) && $removeDashboardStatusForm=="on") {
            $event->setData(['title'=>OW::getLanguage()->text('newsfeed','stream_title')]);
        }
    }

    /**
     * @param OW_Event $event
     */
    public function onRenderNewsfeedUserProfile(OW_Event $event)
    {
        $params = $event->getParams();
        $data= $event->getData();
        $disableNewsfeedFromUserProfile = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
        if (isset($disableNewsfeedFromUserProfile) && $disableNewsfeedFromUserProfile=="on") {
            $data['disable']=true;
        }
        if(isset($params['userId'])) {
            $userDto = BOL_UserService::getInstance()->findUserById($params['userId']);
            $info = new BASE_MCMP_ProfileInfo($userDto);
            $data['info']=$info->render();

        }
        $event->setData($data);
    }
    public function replyNotification( $status, $statusId, $groupEntityId = null, $group = null)
    {
        $reply_action_id = $_POST['reply_to'];
        $reply_action = $this->findActionById($reply_action_id);
        $reply_status_url = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $reply_action_id));
        $userId = OW::getUser()->getId();
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);

        if (isset($reply_action)) {
            $decoded_data = json_decode($reply_action->data);
            $reply_action_uid = $decoded_data->data->userId;
            if ($reply_action_uid != $userId){
                if (isset($group) && OW::getPluginManager()->isPluginActive('frmgroupsplus')) {
                    $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
                    $action = NEWSFEED_BOL_Service::getInstance()->findAction('groups-status', $statusId);
                    $actionId = $action->id;
                    $mainUrl = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $actionId));

                    $notificationParams = array(
                        'pluginKey' => 'newsfeed',
                        'action' => 'reply-to-status',
                        'entityType' => 'groups-status',
                        'entityId' => $groupEntityId,
                        'userId' => $reply_action_uid,
                        'time' => time()
                    );

                    $notificationData = array(
                        'string' => array(
                            "key" => empty(trim($status))? 'frmgroupsplus+notif_reply_to_no_status' : 'frmgroupsplus+notif_reply_to',
                            "vars" => array(
                                'groupTitle' => $group->title,
                                'groupUrl' => $groupUrl,
                                'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                                'userUrl' => $userUrl,
                                'status' => UTIL_String::truncate($status, 120, '...'),
                                'reply_status_url' => isset($reply_status_url) ? $reply_status_url : $groupUrl
                            )
                        ),
                        'avatar' => $avatar,
                        'content' => '',
                        'url' => isset($mainUrl) ? $mainUrl : $groupUrl
                    );
                    $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
                    OW::getEventManager()->trigger($event);
                } else {
                    $action = NEWSFEED_BOL_Service::getInstance()->findAction('user-status', $statusId);
                    $actionId = $action->id;
                    $mainUrl = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $actionId));
                    $reply_status_url = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $reply_action_id));
                    $notificationParams = array(
                        'pluginKey' => 'newsfeed',
                        'action' => 'reply-to-status',
                        'entityType' => 'user_status',
                        'entityId' => $statusId,
                        'userId' => $reply_action_uid,
                        'time' => time()
                    );

                    $notificationData = array(
                        'string' => array(
                            "key" => empty(trim($status))? 'newsfeed+notif_reply_to_no_status':'newsfeed+notif_reply_to',
                            "vars" => array(
                                'userName' => BOL_UserService::getInstance()->getDisplayName($userId),
                                'userUrl' => $userUrl,
                                'status' => UTIL_String::truncate($status, 120, '...'),
                                'reply_status_url' => $reply_status_url
                            )
                        ),
                        'avatar' => $avatar,
                        'content' => UTIL_String::truncate($status, 100, '...'),
                        'url' => $mainUrl,
                        'format' => "text"
                    );
                    $e = new OW_Event('notifications.add', $notificationParams, $notificationData);
                    OW::getEventManager()->trigger($e);
                }
            }
        }
    }

    /**
     * @param $searchValue
     * @return false|mixed|string
     */
    public static function generateDataSearchStringForNewsFeed($searchValue)
    {
        $searchValue = trim($searchValue);
        $searchValue = UTIL_HtmlTag::stripTagsAndJs($searchValue);
        $searchValue = json_encode($searchValue);
        $searchValue = str_replace('"', '', $searchValue);
        $searchValue = str_replace("\\", '\\\\', $searchValue);
        $searchValue = '%"data":{%status%' . $searchValue . '%}%,"actionDto":%';
        return $searchValue;
    }

    /***
     * @param OW_Event $event
     */
    public function addFollowersAndFollowingsEvent( OW_Event $event ){
        $params = $event->getParams();
        $result = null;
        if(isset($params['userId'])){
            $result = $this->getFollowingAndFollowersAndPostsInformation( $params['userId'], true );
            $event->setData( array(
                'followers' =>  isset($result['followers']) ? $result['followers'] : 0,
                'followings' =>  isset($result['followings']) ? $result['followings'] : 0,
                'posts' =>  isset($result['posts']) ? $result['posts'] : 0,
                'cmp' =>  isset($result['cmp']) ? $result['cmp'] : ""
            ) );
        }
    }

    /***
     * @param $userId
     * @param bool $cmp use true value for getting component in the result
     * @return null
     */
    public function getFollowingAndFollowersAndPostsInformation( $userId, $cmp = false ){
        $result = null;
        if(OW::getConfig()->getValue('newsfeed', 'showFollowersAndFollowings') && isset($userId)){
            $result['followers'] =  NEWSFEED_BOL_FollowDao::getInstance()->findFollowersCount($userId);
            $result['followings'] = NEWSFEED_BOL_FollowDao::getInstance()->findFollowingCount($userId);
            $result['posts'] = NEWSFEED_BOL_ActionFeedDao::getInstance()->findActionsCountByFeedId($userId);
            if(isset($cmp) && $cmp === true){
                $infoCmp = new NEWSFEED_CMP_Info($result['followers'], $result['followings'], $result['posts']);
                $result['cmp'] = $infoCmp->render();
            }
        }
        return $result;
    }

}