<?php
/**
 * Data Access Object for `newsfeed_action` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_ActionDao extends OW_BaseDao
{
    const CACHE_TIMESTAMP_PREFERENCE = 'newsfeed_generate_action_set_timestamp';
    const CACHE_TIMEOUT = 300; // 5 min
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG_INDEX = 'newsfeed_index';
    const CACHE_TAG_USER = 'newsfeed_user';
    const CACHE_TAG_USER_PREFIX = 'newsfeed_user_';
    const CACHE_TAG_FEED = 'newsfeed_feed';
    const CACHE_TAG_FEED_PREFIX = 'newsfeed_feed_';
    const CACHE_TAG_ALL = 'newsfeed_all';

    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_ActionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_ActionDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'NEWSFEED_BOL_Action';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'newsfeed_action';
    }

    /**
     *
     * @param $entityType
     * @param $entityId
     * @return NEWSFEED_BOL_Action
     */
    public function findAction( $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    /**
     *
     * @param $entityType
     * @param $entityId
     * @return NEWSFEED_BOL_Action
     */
    public function findBusinessPost( $start, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    public function findByPluginKey( $pluginKey )
    {
        $example = new OW_Example();
        $example->andFieldEqual('pluginKey', $pluginKey);

        return $this->findListByExample($example);
    }

    public function setStatusByPluginKey( $pluginKey, $status )
    {
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $query = "UPDATE " . $this->getTableName() . " action
            INNER JOIN " . $activityDao->getTableName() . " activity ON action.id = activity.actionId
            SET activity.`status`=:s
            WHERE activity.activityType=:ca AND action.pluginKey=:pk";

        $this->dbo->query($query, array(
            's' => $status,
            'pk' => $pluginKey,
            'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ));
    }

    public function findByFeed( $feedType, $feedId, $limit = null, $startTime = null, $formats = null, $driver = null, $endTime = null, $preparedInfo = array())
    {
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $cacheStartTime = OW::getCacheManager()->load('newsfeed.feed_cache_time_' . $feedType . $feedId);
        if ( $cacheStartTime === null )
        {
            OW::getCacheManager()->save($startTime, 'newsfeed.feed_cache_time_' . $feedType . $feedId, array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_FEED,
                self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findByFeed"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }

        $isChannel = false;
        if (isset($preparedInfo['isChannel'])) {
            $isChannel = $preparedInfo['isChannel'];
        } else {
            $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.is.group.channel', array('feedId'=>$feedId,'feedType'=>$feedType)));
            if (isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel']==true)
            {
                $isChannel = true;
            }
        }
        if ($isChannel) {
            $queryParts["where"] .=" AND action.entityType NOT IN ('groups-join','groups-leave')";
        }

        $privacyCondition = '\''.NEWSFEED_BOL_Service::PRIVACY_EVERYBODY.'\'';
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()){
            $privacyCondition = '\''.NEWSFEED_BOL_Service::PRIVACY_EVERYBODY.'\', '.
                '\''.NEWSFEED_BOL_Service::PRIVACY_FRIENDS.'\', '.
                '\''.NEWSFEED_BOL_Service::PRIVACY_ONLY_ME.'\'';
        }
        else {
            $eventPrivacyCondition = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_QUERY_FEED_CREATE, array('feedId' => $feedId, 'feedType' => $feedType)));
            if (isset($eventPrivacyCondition->getData()['privacy'])) {
                $privacyCondition = $eventPrivacyCondition->getData()['privacy'];
            }
        }
        $oderBy=' DESC ';
        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId

            WHERE ' . $queryParts["where"] . '
                AND activity.status=:s
                AND activity.timeStamp<:st
                AND activity.timeStamp>:st2
                AND (activity.privacy in ('.$privacyCondition.') or activity.userId =:vi )
                AND action_feed.feedType=:ft
                AND action_feed.feedId=:fi
                AND activity.visibility & :v

                AND cactivity.status=:s
                AND cactivity.activityType=:ac
                AND (cactivity.privacy in ('.$privacyCondition.') or cactivity.userId =:vi )
                AND cactivity.visibility & :v

            GROUP BY action.id ORDER BY MAX(activity.timeStamp) ' .$oderBy.  $limitStr;


        $idList = $this->dbo->queryForColumnList($query, array(
            'ft' => $feedType,
            'fi' => $feedId,
            'vi' => OW::getUser()->getId(),
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'v' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_FEED,
            self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
        ));
        $eventActionList = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ACTIONS_LIST_RETURN, array('limit' => $limit, 'driver'=> $driver, 'idList' => $idList)));
        if(isset($eventActionList->getData()['idList']) && isset($eventActionList->getData()['count'])){
            $idList = $eventActionList->getData()['idList'];
            $driver->setCount($eventActionList->getData()['count']);
        }

        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form',['feedType'=>$feedType]));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $idList = array_map('intval', $idList);
            sort($idList);
        }
        return $this->findOrderedListByIdList($idList);
    }

    public function findCountByFeed( $feedType, $feedId, $startTime = null, $formats = null, $endTime = null )
    {
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findCountByFeed"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        /*$query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            ' . $queryParts["join"] . '

            LEFT JOIN ' . $activityDao->getTableName() . ' pactivity ON activity.actionId = pactivity.actionId
                AND (pactivity.status=:s AND pactivity.activityType=:ac AND pactivity.privacy!=:peb AND pactivity.visibility & :v)

            WHERE ' . $queryParts["where"] . ' AND pactivity.id IS NULL AND activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND action_feed.feedType=:ft AND action_feed.feedId=:fi AND activity.visibility & :v';
         * */
        
        $query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId

            WHERE ' . $queryParts["where"] . '
                AND activity.status=:s
                AND activity.timeStamp<:st
                AND activity.timeStamp>:st2
                AND activity.privacy=:peb
                AND action_feed.feedType=:ft
                AND action_feed.feedId=:fi
                AND activity.visibility & :v

                AND cactivity.status=:s
                AND cactivity.activityType=:ac
                AND cactivity.privacy=:peb
                AND cactivity.visibility & :v';

        return (int) $this->dbo->queryForColumn($query, array(
            'ft' => $feedType,
            'fi' => $feedId,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'v' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_FEED,
            self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
        ));
    }

    public function findByUser( $userId, $limit = null, $startTime = null, $formats = null, $driver = null, $endTime = null, $entityType = null, $searchValue = null)
    {
        $cacheKey = md5('user_feed' . $userId . ( empty($limit) ? '' : implode('', $limit) ) );

        $cachedIdList = OW::getCacheManager()->load($cacheKey);

        if ( $cachedIdList !== null )
        {
            $idList = json_decode($cachedIdList, true);

            return $this->findOrderedListByIdList($idList);
        }

        $followDao = NEWSFEED_BOL_FollowDao::getInstance();
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();
        $actionSetDao = NEWSFEED_BOL_ActionSetDao::getInstance();

        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $supportWithClause = false;
        if(defined('SUPPORT_WITH_CLAUSE_IN_MYSQL_VERSION') && SUPPORT_WITH_CLAUSE_IN_MYSQL_VERSION){
            $supportWithClause = true;
        }
        $actionIdListQueryAndParam = array('query' => '', 'params' => array(), 'tableName' => $actionSetDao->getTableName());
        if($supportWithClause){
            $actionIdListQueryAndParam = $actionSetDao->getActionUserActionIdList($userId, $startTime);
        }else{
            $actionSetDao->deleteActionSetUserId($userId);
            $actionSetDao->generateActionSet($userId, $startTime);
        }
        $actionSetTableName = $actionIdListQueryAndParam['tableName'];
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findByUser"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        $followerPrivacyWhereCondition = '';
        $viewerActivityPrivacyWhereCondition = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_USER_FEED_LIST_QUERY_EXECUTE));
        if(isset($privacyConditionEvent->getData()['whereConditionPrivacy'])){
            $followerPrivacyWhereCondition = $privacyConditionEvent->getData()['whereConditionPrivacy']['followerPrivacyWhereCondition'];
            $viewerActivityPrivacyWhereCondition = $privacyConditionEvent->getData()['whereConditionPrivacy']['viewerActivityPrivacyWhereCondition'];
        }

        $orderBy=' ORDER BY MAX(`b`.`timeStamp`) DESC ';

        $changeQueryEvent = OW::getEventManager()->trigger(new OW_Event('change.newsfeed.action.query'));
        if(isset($changeQueryEvent->getData()['orderBy']))
        {
            $orderBy=$changeQueryEvent->getData()['orderBy'];
        }

        $params = array(
            'u' => $userId,
            'va' => NEWSFEED_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => NEWSFEED_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        );

        $queryLike ='';
        $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_before_collect_search_items'));
        $q = $event->getData();
        if(isset($q)) {
            $queryLike = ' AND action.data like :qL ';
            $params['qL'] = $q;
            if (empty($limitStr)) {
                $limitStr = "LIMIT 0,10";
            }
        }

        if(isset($searchValue)){
            $queryLike = ' AND action.data like :qL ';

            $params['qL'] = '%"data":{%status%'.$searchValue.'%}%,"actionDto":%';
//            $params['qL'] = $searchValue;
            if (empty($limitStr)) {
                $limitStr = "LIMIT 0,10";
            }
        }

        if($entityType = "user-status"){
            $queryParts["where"] .= ' AND action.entityType = :eN ';
            $params['eN'] = $entityType;
        }

        $query = $actionIdListQueryAndParam['query'] . ' SELECT  b.`id` FROM
            ( SELECT  action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetTableName . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            LEFT JOIN ' . $followDao->getTableName() . ' follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as 
                AND ( ( follow.userId=:u AND activity.visibility & :vf ) )'.$followerPrivacyWhereCondition.$queryLike.'

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetTableName . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as 
                    AND ( ( activity.userId=:u AND activity.visibility & :va ) ) '.$viewerActivityPrivacyWhereCondition.$queryLike.'

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetTableName . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as
                    AND ( ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) )'.$queryLike.'

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetTableName . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                INNER JOIN ' . $activityDao->getTableName() . ' subscribe ON activity.actionId=subscribe.actionId and subscribe.activityType=:as AND subscribe.userId=:u
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as'.$queryLike.'

                ) b

            GROUP BY b.`id` '.$orderBy . $limitStr;

        $additionalParams = array();
        if(isset($actionIdListQueryAndParam['params'])){
            $additionalParams = $actionIdListQueryAndParam['params'];
        }
        $params = array_merge($additionalParams, $params);
        $idList = array_unique($this->dbo->queryForColumnList($query, $params));
        if ( $limit[0] == 0 )
        {
            $cacheLifeTime = self::CACHE_LIFETIME;
            $cacheTags = array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_USER,
                self::CACHE_TAG_USER_PREFIX . $userId
            );

            OW::getCacheManager()->save(json_encode($idList), $cacheKey, $cacheTags, $cacheLifeTime);
        }
        $eventActionList = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ACTIONS_LIST_RETURN, array('limit' => $limit, 'driver'=> $driver, 'idList' => $idList)));
        if(isset($eventActionList->getData()['idList']) && isset($eventActionList->getData()['count'])){
            $idList = $eventActionList->getData()['idList'];
            $driver->setCount($eventActionList->getData()['count']);
        }
        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form'));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $idList = array_map('intval', $idList);
            sort($idList);
        }
        return $this->findOrderedListByIdList($idList);
    }

    public function findCountByUser( $userId, $startTime, $formats = null, $endTime = null)
    {
        $cacheKey = md5('user_feed_count' . $userId );
        $cachedCount = OW::getCacheManager()->load($cacheKey);

        if ( $cachedCount !== null )
        {
            return $cachedCount;
        }

        $followDao = NEWSFEED_BOL_FollowDao::getInstance();
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();
        $actionSetDao = NEWSFEED_BOL_ActionSetDao::getInstance();

        /*$actionSetDao->deleteActionSetUserId($userId);
        $actionSetDao->generateActionSet($userId, $startTime);*/

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findCountByUser"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }

        $query = 'SELECT COUNT(DISTINCT `id`) FROM ( SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            LEFT JOIN ' . $followDao->getTableName() . ' follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND (
                    ( follow.userId=:u AND activity.visibility & :vf ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND (
                    ( activity.userId=:u AND activity.visibility & :va ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2
            AND ( ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $activityDao->getTableName() . ' subscribe ON activity.actionId=subscribe.actionId and subscribe.activityType=:as AND subscribe.userId=:u
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND activity.timeStamp>:st2 ) a ';

        $count = $this->dbo->queryForColumn($query, array(
            'u' => $userId,
            'va' => NEWSFEED_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => NEWSFEED_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        ));

        $cacheLifeTime = self::CACHE_LIFETIME;
        $cacheTags = array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_USER,
            self::CACHE_TAG_USER_PREFIX . $userId
        );

        OW::getCacheManager()->save($count, $cacheKey, $cacheTags, $cacheLifeTime);

        return $count;
    }

    public function findSiteFeed( $limit = null, $startTime = null, $formats = null, $driver = null, $endTime = null, $searchValue = null, $entityType = null)
    {
        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $cacheStartTime = OW::getCacheManager()->load('newsfeed.site_cache_time');
        if ( $cacheStartTime === null )
        {
            OW::getCacheManager()->save($startTime, 'newsfeed.site_cache_time', array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_INDEX,
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findSiteFeedCount"
        ));

        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        if ( !empty($limit) )
        {
            $maxLimit = intval($limit[1])*100;
        }else {
            $maxLimit =  1000 ;
        }
        /**
        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
        INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) activity ON action.id = activity.actionId
        INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) cactivity ON action.id = cactivity.actionId
        ' . $queryParts["join"] . '
        WHERE ' . $queryParts["where"] . ' AND
        (cactivity.status=:s AND cactivity.activityType=:ac AND cactivity.privacy=:peb AND cactivity.visibility & :v)
        AND
        (activity.status=:s AND activity.privacy=:peb AND activity.visibility & :v AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as)
        GROUP BY action.id
        ORDER BY MAX(activity.timeStamp) DESC ' . $limitStr;
         **/
        $params = array(
            'v' => NEWSFEED_BOL_Service::VISIBILITY_SITE,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            //,'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        );

        $queryLike ='';
        if ($searchValue) {
            $q = $searchValue;
        } else {
            $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_before_collect_search_items'));
            $q = $event->getData();
        }
        if(isset($q)) {
            $queryLike = ' And action.data like :qL ';
            $params['qL'] = $q;
            if (empty($limitStr)) {
                $limitStr = "LIMIT 0,10";
            }
        }

        $queryEntityTypeWhere = '';
        if ($entityType) {
            $queryEntityTypeWhere = ' AND action.entityType=:eT ';
            $params['eT'] = $entityType;
        }

        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
            INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) activity ON action.id = activity.actionId
            ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND
                (activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND activity.visibility & :v
                AND
                 activity.timeStamp<:st AND activity.timeStamp>:st2)'. $queryLike . $queryEntityTypeWhere .
            'GROUP BY action.id
              ORDER BY MAX(activity.timeStamp) DESC ' . $limitStr;

        $idList = $this->dbo->queryForColumnList($query, $params, self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_INDEX
        ));
        $eventActionList = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ACTIONS_LIST_RETURN, array('limit' => $limit, 'driver'=> $driver, 'idList' => $idList)));
        if(isset($eventActionList->getData()['idList']) && isset($eventActionList->getData()['count'])){
            $idList = $eventActionList->getData()['idList'];
            $driver->setCount($eventActionList->getData()['count']);
        }
        return $this->findOrderedListByIdList($idList);
    }

    public function findProductsFeed( $limit = null, $startTime = null, $formats = null, $driver = null, $endTime = null, $searchValue = null, $entityType = null)
    {
        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $cacheStartTime = OW::getCacheManager()->load('newsfeed.site_cache_time');
        if ( $cacheStartTime === null )
        {
            OW::getCacheManager()->save($startTime, 'newsfeed.site_cache_time', array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_INDEX,
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findSiteFeedCount"
        ));

        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        if ( !empty($limit) )
        {
            $maxLimit = intval($limit[1])*100;
        }else {
            $maxLimit =  1000 ;
        }
        /**
        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
        INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) activity ON action.id = activity.actionId
        INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) cactivity ON action.id = cactivity.actionId
        ' . $queryParts["join"] . '
        WHERE ' . $queryParts["where"] . ' AND
        (cactivity.status=:s AND cactivity.activityType=:ac AND cactivity.privacy=:peb AND cactivity.visibility & :v)
        AND
        (activity.status=:s AND activity.privacy=:peb AND activity.visibility & :v AND activity.timeStamp<:st AND activity.timeStamp>:st2 AND activity.activityType NOT LIKE :as)
        GROUP BY action.id
        ORDER BY MAX(activity.timeStamp) DESC ' . $limitStr;
         **/
        $params = array(
            'v' => NEWSFEED_BOL_Service::VISIBILITY_SITE,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            //,'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        );

        $queryLike ='';
        if ($searchValue) {
            $q = $searchValue;
        } else {
            $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_before_collect_search_items'));
            $q = $event->getData();
        }
        if(isset($q)) {
            $queryLike = ' And action.data like :qL ';
            $params['qL'] = $q;
            if (empty($limitStr)) {
                $limitStr = "LIMIT 0,10";
            }
        }

        $queryEntityTypeWhere = '';
        if ($entityType) {
            $queryEntityTypeWhere = ' AND action.entityType=:eT ';
            $params['eT'] = $entityType;
        }

        $params = array(
                'p1'=> '%products%' ,
                'p2'=> '%products%' ,
                'p3'=> '%products%' ,
            );

        $query = 'SELECT id FROM `ow_newsfeed_action` WHERE  
                `data` NOT LIKE \'%"products":null%\' AND
                `data` NOT LIKE \'%"products":"null",%\' AND
                `data` LIKE \'%"products":%\' '. $limitStr;

        $idList = $this->dbo->queryForColumnList($query, $params, self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_INDEX
        ));
        $eventActionList = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ACTIONS_LIST_RETURN, array('limit' => $limit, 'driver'=> $driver, 'idList' => $idList)));
        if(isset($eventActionList->getData()['idList']) && isset($eventActionList->getData()['count'])){
            $idList = $eventActionList->getData()['idList'];
            $driver->setCount($eventActionList->getData()['count']);
        }
        return $this->findOrderedListByIdList($idList);
    }

    public function findSiteFeedQuery($limit = null, $startTime = null, $formats = null, $endTime = null, $searchValue = null, $entityType = null)
    {

        $cacheStartTime = OW::getCacheManager()->load('newsfeed.site_cache_time');
        if ( $cacheStartTime === null )
        {
            OW::getCacheManager()->save($startTime, 'newsfeed.site_cache_time', array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_INDEX,
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findSiteFeedCount"
        ));

        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        if ( !empty($limit) )
        {
            $maxLimit = intval($limit[1])*100;
        }else {
            $maxLimit =  1000 ;
        }

        $params = array(
            'v' => NEWSFEED_BOL_Service::VISIBILITY_SITE,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'st2' => empty($endTime) ? 0 : $endTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            //,'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        );

        $queryLike ='';
        if ($searchValue) {
            $q = $searchValue;
        } else {
            $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_before_collect_search_items'));
            $q = $event->getData();
        }
        if(isset($q)) {
            $queryLike = ' And action.data like :qL ';
            $params['qL'] = $q;
        }

        $queryEntityTypeWhere = '';
        if ($entityType) {
            $queryEntityTypeWhere = ' AND action.entityType=:eT ';
            $params['eT'] = $entityType;
        }

        $query = 'SELECT action.id, MAX(activity.timeStamp) AS lastActivityTimestamp, "groups-status" AS type  FROM ' . $this->getTableName() . ' action
            INNER JOIN (SELECT * from ' . $activityDao->getTableName() . ' ORDER BY id DESC LIMIT 0, '.$maxLimit.' ) activity ON action.id = activity.actionId
            ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND
                (activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND activity.visibility & :v
                AND
                 activity.timeStamp<:st AND activity.timeStamp>:st2)'. $queryLike . $queryEntityTypeWhere .
            'GROUP BY action.id ';
        $result = [
            "query" => $query,
            "params" => $params
        ];

        return $result;
    }


    private function findOrderedListByIdList( $idList )
    {
        if ( empty($idList) )
	    {
	          return array();
	    }
	    
        $unsortedDtoList = $this->findByIdList($idList);
        $unsortedList = array();
        foreach ( $unsortedDtoList as $dto )
        {
            $unsortedList[$dto->id] = $dto;
        }

        $sortedList = array();
        foreach ( $idList as $id )
        {
            if ( !empty($unsortedList[$id]) )
            {
            	$sortedList[] = $unsortedList[$id];
            }
        }

        return $sortedList;
    }

    public function findSiteFeedCount( $startTime = null, $formats = null )
    {
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "NEWSFEED_BOL_ActionDao::findSiteFeedCount"
        ));

        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }

        $query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
                    INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                    LEFT JOIN ' . $activityDao->getTableName() . ' pactivity ON activity.actionId = pactivity.actionId
                        AND (pactivity.status=:s AND pactivity.activityType=:ac AND pactivity.privacy!=:peb AND pactivity.visibility & :v)
                    ' . $queryParts["join"] . '

                    WHERE ' . $queryParts["where"] . ' AND pactivity.id IS NULL AND activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND activity.visibility & :v';

        return $this->dbo->queryForColumn($query, array(
            'v' => NEWSFEED_BOL_Service::VISIBILITY_SITE,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_INDEX
        ));
    }

    public function findListByUserId( $userId )
    {
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $query = "SELECT DISTINCT action.* FROM " . $this->getTableName() . " action
            INNER JOIN " . $activityDao->getTableName() . " activity ON action.id=activity.actionId
            WHERE activity.activityType=:ca AND activity.userId=:u";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'u' => $userId
        ));
    }

    public function setPrivacyByEntityType( $userId, array $entityTypes, $privacy )
    {
        if ( empty($entityTypes) )
        {
            return;
        }

        $query = "UPDATE " . $this->getTableName() . " SET privacy=:p WHERE userId=:u AND entityType IN (" . $this->dbo->mergeInClause($entityTypes) . ")";

        $this->dbo->query($query, array(
            'u' => $userId,
            'p' => $privacy
        ));
    }

    /**
     *
     * @param $actionId
     * @return NEWSFEED_BOL_Action
     */
    public function findActionById( $actionId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $actionId);

        return $this->findObjectByExample($example);
    }

    public function findExpiredIdList( $inactivePeriod, $count = null )
    {
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();
        $systemActivities = NEWSFEED_BOL_Service::getInstance()->SYSTEM_ACTIVITIES;
        $limit = '';

        if ( !empty($count) )
        {
            $limit = ' LIMIT ' . $count;
        }

        $query = 'SELECT DISTINCT cactivity.actionId FROM ' . $activityDao->getTableName() . ' cactivity
            LEFT JOIN ' . $activityDao->getTableName() . ' activity
                    ON cactivity.actionId=activity.actionId AND activity.activityType NOT IN ("' . implode('", "', $systemActivities) . '")
                WHERE activity.id IS NULL AND cactivity.activityType=:c AND cactivity.timeStamp < :ts' . $limit;

        return $this->dbo->queryForColumnList($query, array(
            'c' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'ts' => time() - $inactivePeriod
        ));
    }

    public function findActionListByEntityIdsAndEntityType($entityIdList, $entityType)
    {
        if (empty($entityIdList)){
            return array();
        }
        $entityIdList = implode(',', $entityIdList);
        $query = "SELECT id, entityId, entityType FROM `" . $this->getTableName() . "` 
        WHERE `entityId` IN (" . $entityIdList . ") AND `entityType` = :et ";
        $actionList = $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('et' => $entityType));
        return $actionList;
    }

    public function findActionListByEntityIdsAndEntityTypes($entityIdList, $entityTypes)
    {
        if (empty($entityIdList) || empty($entityTypes)){
            return array();
        }
        $entityIdList = implode(',', $entityIdList);
        $query = "SELECT * FROM `" . $this->getTableName() . "` 
        WHERE `entityId` IN (" . $entityIdList . ") AND `entityType` in (" . $this->dbo->mergeInClause($entityTypes) . ") ";
        $actionList = $this->dbo->queryForObjectList($query, $this->getDtoClassName());
        return $actionList;
    }


    /**
     * @param $actionId
     * @return array
     */
    public function findRepliedActions($actionId)
    {
        $params['replyString']='%"reply_to":"'.$actionId.'"%';
        $query = 'SELECT * FROM `' . $this->getTableName() . '` 
        WHERE `data` like :replyString';
        $actionList = $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
        return $actionList;
    }

    public function productHashtagSearchByArray( $hashtag ){

        $hashtag = trim($hashtag);
        if (empty($hashtag) || strpos($hashtag, ' ') !== false){
            return array();
        }
        $params['hashtag']='%'.$hashtag.'%';
        $query = 'SELECT * FROM `' . $this->getTableName() . '` 
        WHERE `data` like :hashtag';
        $actionList = $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
        return $actionList;

    }
}