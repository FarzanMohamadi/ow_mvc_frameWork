<?php
/**
 * Data Access Object for `forum_post` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_PostDao extends OW_BaseDao
{

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
     * @var FORUM_BOL_PostDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_PostDao
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
        return 'FORUM_BOL_Post';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_post';
    }

    /**
     * Returns topic's post list
     *
     * @param int $topicId
     * @param int $first
     * @param int $count
     * @param int $lastPostId
     * @param bool $reverse_sort
     * @return array of FORUM_BOL_Post
     */
    public function findTopicPostList($topicId, $first, $count, $lastPostId = null, $reverse_sort = false)
    {
        $firstPost = $this->findTopicOpeningAndClosingPosts($topicId)[0];
        $queryParams = array('topicId' => $topicId, 'firstPost' => $firstPost->id, 'f' => $first, 'c' => $count);
        $where = "";
        if ( $lastPostId )
        {
            $where = 'AND `p`.`id` > :lastPostId';
            $queryParams['lastPostId'] = $lastPostId;
        }

        $sort_order = 'ASC';
        if ($reverse_sort){
            $sort_order = 'DESC';
        }

        $sql = 'SELECT `p`.*, `CM`.`entityId`, COUNT(*) AS commentsCount
                FROM ' . $this->getTableName() . ' AS `p`
                LEFT JOIN (
                    SELECT `ce`.`id`, `c`.`commentEntityId`, `ce`.`entityType`, `ce`.`entityId`
                    from ' . BOL_CommentEntityDao::getInstance()->getTableName() . ' AS `ce`
                    INNER JOIN ' . BOL_CommentDao::getInstance()->getTableName() . ' AS `c`
                    ON `ce`.`id` = `c`.`commentEntityId`
                    WHERE `ce`.`entityType` = "forum-post" ) `CM`
                ON `p`.`id` = `CM`.`entityId`
                WHERE `p`.`topicId` = :topicId
                AND `p`.`id` <> :firstPost '. $where .'
                GROUP BY `p`.`id`
                ORDER BY `p`.`id` '. $sort_order .'
                LIMIT :f, :c';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $queryParams);
    }

    /**
     * Returns topic's first and closing posts
     *
     * @param int $topicId
     * @return array
     */
    public function findTopicOpeningAndClosingPosts($topicId)
    {
        $forCommentsCounts = 'LEFT JOIN (
                        SELECT `ce`.`id`, `c`.`commentEntityId`, `ce`.`entityType`, `ce`.`entityId`
                        from ' . BOL_CommentEntityDao::getInstance()->getTableName() . ' AS `ce`
                        INNER JOIN ' . BOL_CommentDao::getInstance()->getTableName() . ' AS `c`
                        ON `ce`.`id` = `c`.`commentEntityId`
                        WHERE `ce`.`entityType` = "forum-post" ) `CM`
                    ON `p`.`id` = `CM`.`entityId`';

        $sqlOpening = 'SELECT `p`.*, `CM`.`entityId`, COUNT(*) AS commentsCount
                    FROM ' . $this->getTableName() . ' AS `p`
                    '. $forCommentsCounts .'
                    WHERE `p`.`topicId` = :topicId
                    GROUP BY `p`.`id`
                    ORDER BY `p`.`id` ASC
                    LIMIT 0, 1';

        $queryParams = array('topicId' => $topicId);

        $topic = FORUM_BOL_ForumService::getInstance()->findTopicById($topicId);
        $forumConclusionPostId = $topic->conclusionPostId;
        $config = OW::getConfig();
        if (isset($forumConclusionPostId) &&
            $config->configExists('forum', 'showClosedTopicLastPostInTopSection') &&
            $config->getValue('forum', 'showClosedTopicLastPostInTopSection')){
            $sql = '('. $sqlOpening .')
                 UNION
                    (SELECT `p`.*, `CM`.`entityId`, COUNT(*) AS commentsCount
                    FROM ' . $this->getTableName() . ' AS `p`
                    '. $forCommentsCounts .'
                    WHERE `p`.`topicId` = :topicId
                    AND `p`.`id` = :id
                    GROUP BY `p`.`id`)';
            $queryParams['id'] = $forumConclusionPostId;
        }
        else{
            $sql = $sqlOpening;
        }

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $queryParams);
    }

    /**
     * Returns topic's post count
     *
     * @param int $topicId
     * @return int
     */
    public function findTopicPostCount( $topicId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);

        return $this->countByExample($example);
    }

    /**
     * Returns post number in the topic
     *
     * @param int $topicId
     * @param int $postId
     * @return int
     */
    public function findPostNumber( $topicId, $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);
        $example->andFieldLessOrEqual('id', $postId);

        return $this->countByExample($example) - 1;
    }

    /**
     * Finds previous post in the topic
     *
     * @param int $topicId
     * @param int $postId
     * @return FORUM_BOL_Post
     */
    public function findPreviousPost( $topicId, $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);
        $example->andFieldLessThan('id', $postId);
        $example->setOrder('`id` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds topic post id list
     *
     * @param int $topicId
     * @return array
     */
    public function findTopicPostIdList( $topicId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);

        $query = "
		SELECT `id` FROM `" . $this->getTableName() . "`
		" . $example;

        return $this->dbo->queryForColumnList($query);
    }

    public function findUserPostIdList( $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);

        $query = "
            SELECT `id` FROM `" . $this->getTableName() . "` " . $example;

        return $this->dbo->queryForColumnList($query);
    }
    
    public function findUserPostList( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    public function findTopicFirstPost( $topicId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);
        $example->setOrder("`id`");
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    public function findTopicsPostByIds( $ids )
    {
        if (empty($ids)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('topicId', $ids);

        $res = $this->findListByExample($example);
        $data = array();
        foreach ($res as $item) {
            if (!isset($data[$item->topicId])) {
                $data[$item->topicId] = array();
            }
            $data[$item->topicId][$item->id] = $item;
        }
        return $data;
    }
    
    public function findGroupLastPost( $groupId )
    {
        if ( empty($groupId) )
        {
            return null;
        }
        /*
         * use lastPostId to get last post for a topic correctly
         * Mohammad Aghaabbasloo
         */
        $whereClause = 'and `t`.`lastPostId` = `p`.`id`';
        $sql = 'SELECT `p`.*, `t`.`title`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . FORUM_BOL_TopicDao::getInstance()->getTableName() . '` AS `t`
                    ON(`p`.`topicId`=`t`.`id` '.$whereClause.')
            WHERE `t`.`groupId` = :groupId AND `t`.`status` = :status
            ORDER BY `p`.`createStamp` DESC
            LIMIT 1';

        return $this->dbo->queryForRow($sql, array('groupId' => $groupId, 'status' => FORUM_BOL_ForumService::STATUS_APPROVED));
    }

    /**
     * Returns users post count list by ids
     * 
     * @param array $userIds
     * @return array 
     */
    public function findPostCountListByUserIds( array $userIds )
    {
        if ( !$userIds )
        {
            return array();
        }

        $userIds = $this->dbo->mergeInClause($userIds);

        $query = "
		SELECT `userId` , COUNT( * ) AS `postsCount`
		FROM `" . $this->getTableName() . "`
		WHERE `userId` IN (" . $userIds .") GROUP BY `userId`";

        $values =  $this->dbo->queryForList($query);
        $processedValues = array();

        foreach($values as $value) {
            $processedValues[$value['userId']] = $value['postsCount'];
        }

        return $processedValues;
    }

    /**
     * Returns post list by ids
     * 
     * @param array $postIds
     * @return array 
     */
    public function findListByPostIds( array $postIds )
    {
        if ( !$postIds )
        {
            return array();
        }

        $postsIn = $this->dbo->mergeInClause($postIds);

        $query = "
		SELECT  *
		FROM `" . $this->getTableName() . "`
		WHERE id IN (" . $postsIn .") ORDER BY FIELD(id, " . $postsIn . ")";

        return $this->dbo->queryForList($query);
    }

    private function countTokenWords( $token )
    {
        $str = preg_replace("/ +/", " ", $token);
        $array = explode(" ", $str);

        return count($array);
    }
}