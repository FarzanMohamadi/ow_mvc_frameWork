<?php
class FRMFORUMPLUS_BOL_TopicDao extends OW_BaseDao
{
    const GROUP_ID = 'groupId';
    const STATUS = 'status';
    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var FORUM_BOL_TopicDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_TopicDao
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
        return 'FORUM_BOL_Topic';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_topic';
    }

    /**
     * @param $limit
     * @param null $excludeGroupIdList
     * @param bool $lastTopics
     * @param bool $mostViewed
     * @param bool $lastPosts
     * @param int $timeStart
     * @param int $timeEnd
     * @return array
     */
    public function findCustomLastTopicList( $limit, $excludeGroupIdList = null , $lastTopics=true,$mostViewed=false,$lastPosts=false,$timeStart = 0, $timeEnd = 0)
    {
        $postDao = FORUM_BOL_PostDao::getInstance();
        $groupDao = FORUM_BOL_GroupDao::getInstance();
        $sectionDao = FORUM_BOL_SectionDao::getInstance();
        $params = array();
        $excludeCond = $excludeGroupIdList ? ' AND `g`.`id` NOT IN ('.implode(',', $excludeGroupIdList).') = 1' : '';
        $whereClause='';
        if($timeStart || $timeEnd){
            if ( $timeStart )
            {
                $whereClause .= ' AND b.timeStamp >= :timeStampStart';
                $params['timeStampStart']=$timeStart;
            }

            if ( $timeEnd )
            {
                $whereClause .= ' AND b.timeStamp <= :timeStampEnd';
                $params['timeStampEnd']=$timeEnd;

            }
        }
        $params['status']=FORUM_BOL_ForumService::STATUS_APPROVED;
        $params['limit']=(int)$limit;

        if($lastTopics){
            $orderBy =' ORDER BY `t`.`id` DESC ';
        }
        else if($mostViewed){
            $orderBy = ' ORDER BY `t`.`viewCount` DESC ';
        }
        else if($lastPosts){
            $orderBy = ' ORDER BY `p`.`createStamp` DESC ';
        }
        $query = 'SELECT `t`.*
            FROM `' . $this->getTableName() . '` AS `t`
                INNER JOIN `' . $groupDao->getTableName() . '` AS `g` ON (`t`.`groupId` = `g`.`id`)
                INNER JOIN `' . $sectionDao->getTableName() . '` AS `s` ON (`s`.`id` = `g`.`sectionId`)
                INNER JOIN `' . $postDao->getTableName() . '` AS `p` ON (`t`.`lastPostId` = `p`.`id`)
            WHERE `s`.`isHidden` = 0 AND `t`.`status` = :status ' . $excludeCond .
            $orderBy.'
            LIMIT :limit';
        $list = $this->dbo->queryForList($query, $params);

        if ( $list )
        {
            $topicIdList = array();
            foreach ( $list as $topic )
            {
                $topicIdList[] = $topic['id'];
            }
            $counters = $this->getPostCountForTopicIdList($topicIdList);
            foreach ( $list as &$topic )
            {
                $topic['postCount'] = $counters[$topic['id']];
            }

        }

        return $list;
    }

    public function getPostCountForTopicIdList( $topicIdList )
    {
        $postDao = FORUM_BOL_PostDao::getInstance();

        $query = "SELECT `p`.`topicId`, COUNT(`p`.`id`) AS `postCount`
            FROM `".$postDao->getTableName()."` AS `p`
            WHERE `p`.`topicId` IN (".$this->dbo->mergeInClause($topicIdList).")
            GROUP BY `p`.`topicId`";

        $countList = $this->dbo->queryForList($query);

        $counters = array();
        foreach ( $countList as $count )
        {
            $counters[$count['topicId']] = $count['postCount'];
        }

        return $counters;
    }
}