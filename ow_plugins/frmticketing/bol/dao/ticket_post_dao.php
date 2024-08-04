<?php
/**
 * Data Access Object for `frmticketing_post` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.frmticketing.bol
 */
class FRMTICKETING_BOL_TicketPostDao extends OW_BaseDao
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
     * @var FRMTICKETING_BOL_TicketPostDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMTICKETING_BOL_TicketPostDao
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
        return 'FRMTICKETING_BOL_TicketPost';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_posts';
    }

    /**
     * Returns ticket's post list
     *
     * @param int $ticketId
     * @param int $first
     * @param int $count
     * @return array of FRMTICKETING_BOL_TicketPost
     */
    public function findTicketPostList( $ticketId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('ticketId', $ticketId);


        $example->setOrder('`id`');
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    /**
     * Returns ticket's post count
     *
     * @param int $ticketId
     * @return int
     */
    public function findTicketPostCount( $ticketId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('ticketId', $ticketId);

        return $this->countByExample($example);
    }

    /**
     * Returns post number in the ticket
     *
     * @param int $ticketId
     * @param int $postId
     * @return int
     */
    public function findPostNumber( $ticketId, $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('ticketId', $ticketId);
        $example->andFieldLessOrEqual('id', $postId);

        return $this->countByExample($example);
    }

    /**
     * Finds previous post in the ticket
     *
     * @param int $ticketId
     * @param int $postId
     * @return FRMTICKETING_BOL_TicketPost
     */
    public function findPreviousPost( $ticketId, $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('ticketId', $ticketId);
        $example->andFieldLessThan('id', $postId);
        $example->setOrder('`id` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds ticket post id list
     *
     * @param int $ticketId
     * @return array
     */
    public function findTicketPostIdList( $ticketId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('ticketId', $ticketId);

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

}