<?php
/**
 * Data Access Object for `video_clip` table.  
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.bol
 * @since 1.0
 * 
 */
class VIDEO_BOL_ClipDao extends OW_BaseDao
{
    /**
     * Class instance
     *
     * @var VIDEO_BOL_ClipDao
     */
    private static $classInstance;
    
    const CACHE_TAG_VIDEO_LIST = 'video.list';

    const STATUS_BLOCKED  = 'blocked';
    const STATUS_APPROVAL  = 'approval';
    const STATUS_APPROVED  = 'approved';

    const DEFAULT_PRIVACY  = 'everybody';

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns class instance
     *
     * @return VIDEO_BOL_ClipDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     * @return string
     */
    public function getDtoClassName()
    {
        return 'VIDEO_BOL_Clip';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     * @return string
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'video_clip';
    }

    /**
     * Find latest clips authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicClipsAuthorsIds($first, $count)
    {
        $query = "SELECT
            `userId`
        FROM
            `" . $this->getTableName() . "`
        WHERE
            `privacy` = :privacy
                AND
            `status` = :status
        GROUP BY
            `userId`
        ORDER BY 
            MAX(`addDatetime`) DESC
        LIMIT :f, :c";

        return $this->dbo->queryForColumnList($query, array(
            'privacy' => self::DEFAULT_PRIVACY,
            'status' => self::STATUS_APPROVED,
            'f' => (int) $first,
            'c' => (int) $count,
        ));
    }

    /**
     * Get clips list (featured|latest|toprated)
     *
     * @param string $listtype
     * @param int $page
     * @param int $limit
     * @param $ids
     * @return array of VIDEO_BOL_Clip
     */
    public function getClipsList( $listtype, $page, $limit, $ids = array() )
    {
        $first = ($page - 1 ) * $limit;

        $cacheLifeTime = $first == 0 ? 24 * 3600 : 0;
        $cacheTags = $first == 0 ? array(self::CACHE_TAG_VIDEO_LIST) : array();

        $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => "c",
            BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => "c"
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => "userId",
            BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => "id"
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
            BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => "video.list"
        ));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('video')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`c`', 'listType' => $listtype, 'objectType' => 'video')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $idsFilteringCondition = '';
        if(is_array($ids) && sizeof($ids)>0){
            $idsFilteringCondition = ' AND `c`.id in ('.OW::getDbo()->mergeInClause($ids).') ';
        }
        switch ( $listtype )
        {
            case 'featured':
                $clipFeaturedDao = VIDEO_BOL_ClipFeaturedDao::getInstance();

                $query = "
                    SELECT
                        `c`.*
                    FROM
                        `" . $this->getTableName() . "` AS `c`
                    LEFT JOIN `" . $clipFeaturedDao->getTableName() . "` AS `f` ON (`f`.`clipId`=`c`.`id`)
                    {$queryParts["join"]}
                    WHERE
                        {$queryParts["where"]}
                            AND
                        `c`.`status` = '" . self::STATUS_APPROVED . "'
                            AND
                        `f`.`id` IS NOT NULL " . $idsFilteringCondition . $privacyConditionWhere . "
                    ORDER BY
                        `c`.`addDatetime` DESC
                    LIMIT
                        :first, :limit";

                $qParams = array('first' => (int) $first, 'limit' => (int) $limit);
                if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $qParams = array_merge($qParams, $privacyConditionEvent->getData()['params']);
                }
                return $this->dbo->queryForObjectList($query, 'VIDEO_BOL_Clip', $qParams, $cacheLifeTime, $cacheTags);

            case 'latest':
                $query = "
                    SELECT
                        `c`.*
                    FROM
                        `" . $this->getTableName() . "` AS `c`
                    {$queryParts["join"]}
                    WHERE
                        {$queryParts["where"]}
                            AND
                        `c`.`status` = '" . self::STATUS_APPROVED . "'
                              " . $idsFilteringCondition . $privacyConditionWhere . "
                    ORDER BY
                        `c`.`addDatetime` DESC
                    LIMIT
                        :first, :limit";

                $qParams = array('first' => (int) $first, 'limit' => (int) $limit);
                if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $qParams = array_merge($qParams, $privacyConditionEvent->getData()['params']);
                }
                return $this->dbo->queryForObjectList($query, 'VIDEO_BOL_Clip', $qParams, $cacheLifeTime, $cacheTags);

            default :
        }

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_VIDEO, array('this' => $this, 'listtype' =>$listtype, 'first' => $first, 'limit' => $limit, 'cacheLifeTime' => $cacheLifeTime, 'cacheTags' => $cacheTags)));
        if(isset($resultsEvent->getData()['result'])){
            return $resultsEvent->getData()['result'];
        }
        return null;
    }

    /**
     * Get user video clips list
     *
     * @param int $userId
     * @param $page
     * @param int $itemsNum
     * @param array $exclude
     * @return array of VIDEO_BOL_Clip
     */
    public function getUserClipsList( $userId, $page, $itemsNum, $exclude )
    {
        $first = ($page - 1 ) * $itemsNum;

        $example = new OW_Example();

        $example->andFieldEqual('status', self::STATUS_APPROVED);
        $example->andFieldEqual('userId', $userId);

        if ( $exclude )
        {
            $example->andFieldNotEqual('id', $exclude);
        }

        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('video')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('example' => $example, 'ownerId' => $userId, 'objectType' => 'video')));
            if(isset($privacyConditionEvent->getData()['example'])){
                $example = $privacyConditionEvent->getData()['example'];
            }
        }

        $example->setOrder('`addDatetime` DESC');
        $example->setLimitClause($first, $itemsNum);

        return $this->findListByExample($example);
    }

    public function getUncachedThumbsClipsList( $limit )
    {
        $example = new OW_Example();
        $example->andFieldIsNull('thumbUrl');
        $example->andFieldNotEqual('provider', 'undefined');
        $example->setOrder('`thumbCheckStamp` ASC');
        $example->setLimitClause(0, $limit);

        return $this->findListByExample($example);
    }

    /**
     * Counts clips
     *
     * @param string $listtype
     * @return int
     */
    public function countClips( $listtype )
    {
        
        
        $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => "c",
            BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => "c"
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => "userId",
            BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => "id"
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
            BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => "video.list"
        ));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('video')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`c`', 'listType' => $listtype, 'objectType' => 'video')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        switch ( $listtype )
        {
            case 'featured':
                $featuredDao = VIDEO_BOL_ClipFeaturedDao::getInstance();

                $query = "
                    SELECT
                        COUNT(`c`.`id`)
                    FROM
                        `" . $this->getTableName() . "` AS `c`
                    {$queryParts["join"]}
                    LEFT JOIN
                        `" . $featuredDao->getTableName() . "` AS `f`
                    ON
                        ( `c`.`id` = `f`.`clipId` )
                    WHERE
                        {$queryParts["where"]}
                            AND
                        `c`.`status` = '" . self::STATUS_APPROVED . "'
                            AND
                        `f`.`id` IS NOT NULL " . $privacyConditionWhere . "
                ";
                $qParams = array();
                if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $qParams = array_merge($qParams, $privacyConditionEvent->getData()['params']);
                }
                return $this->dbo->queryForColumn($query, $qParams);

            case 'latest':
                $query = "
                    SELECT
                        COUNT(`c`.`id`)
                    FROM
                        `" . $this->getTableName() . "` AS `c`
                    {$queryParts["join"]}
                    WHERE
                        {$queryParts["where"]}
                            AND
                        `c`.`status` = '" . self::STATUS_APPROVED . "' ". $privacyConditionWhere ."

                ";
                $qParams = array();
                if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $qParams = array_merge($qParams, $privacyConditionEvent->getData()['params']);
                }
                return $this->dbo->queryForColumn($query, $qParams);

            default :
        }
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_COUNT_ITEM_VIDEO, array('this' => $this, 'listType' =>$listtype)));
        if(isset($resultsEvent->getData()['result'])){
            return $resultsEvent->getData()['result'];
        }

        return null;
    }

    /**
     * Counts clips added by a user
     *
     * @param int $userId
     * @return int
     */
    public function countUserClips( $userId )
    {
        $example = new OW_Example();
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('video')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('example' => $example, 'ownerId' => $userId, 'objectType' => 'video')));
            if(isset($privacyConditionEvent->getData()['example'])){
                $example = $privacyConditionEvent->getData()['example'];
            }
        }
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('status', self::STATUS_APPROVED);

        return $this->countByExample($example);
    }
    
    public function findByUserId( $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);

        return $this->findIdListByExample($example);
    }
    
    public function updatePrivacyByUserId( $userId, $privacy )
    {
        $sql = "UPDATE `".$this->getTableName()."` SET `privacy` = :privacy 
            WHERE `userId` = :userId";
        
        $this->dbo->query($sql, array('privacy' => $privacy, 'userId' => $userId));
    }

    public function findVideoByCode( $code )
    {
        $example = new OW_Example();

        $example->andFieldEqual('code', $code);

        return $this->findIdListByExample($example);
    }

    public function findListAllPrivacy( $userId,$q="", $showAll = false,$first = -1, $count = -1)
    {
        if ($first < 0)
        {
            $first = 0;
        }

        if ($count < 0)
        {
            $count = PHP_INT_MAX;
        }

        $count = (int) $count;
        $params = array(
            'userId'=>$userId,
            'showAll'=>$showAll,
            'q'=>'%'.$q.'%',
            'first'=>$first,
            'count'=> $count
        );

        $query="SELECT *
            FROM( 
            
              SELECT `c`.*
                FROM `".OW_DB_PREFIX."video_clip` AS `c`
                    
                WHERE `c`.`userId` = :userId AND (`c`.`title` like :q OR `c`.`description` like :q)
                
              UNION
              
              SELECT `c`.*
                FROM `".OW_DB_PREFIX."video_clip` AS `c`
                    
                WHERE `c`.`privacy` = 'everybody' AND (`c`.`title` like :q OR `c`.`description` like :q)
                
              UNION
                
              SELECT `c`.*
                FROM `".OW_DB_PREFIX."video_clip` AS `c`
                    
                WHERE `c`.`privacy` = 'only_for_me' AND (`c`.`userId` = :userId OR :showAll) AND (`c`.`title` like :q OR `c`.`description` like :q)
                
              UNION
                
              SELECT `c`.*
                FROM `".OW_DB_PREFIX."video_clip` AS `c`
                    
                    LEFT JOIN ".OW_DB_PREFIX."friends_friendship AS `f1` ON `f1`.`friendId` = `c`.`userId`
                    LEFT JOIN ".OW_DB_PREFIX."friends_friendship AS `f2` ON `f2`.`userId` = `c`.`userId`
            WHERE `c`.`privacy` = 'friends_only' AND (`f1`.`userId` = :userId OR `f2`.`friendId` = :userId OR `c`.`userId` = :userId OR  :showAll) AND (`c`.`title` like :q OR `c`.`description` like :q)
            ) AS `clips` LIMIT :first, :count";

        return $this->dbo->queryForList($query, $params);
    }

}