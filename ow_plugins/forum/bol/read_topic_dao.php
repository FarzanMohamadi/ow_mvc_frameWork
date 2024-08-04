<?php
/**
 * Data Access Object for `forum_read_topic` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_ReadTopicDao extends OW_BaseDao
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
     * @var FORUM_BOL_ReadTopicDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_ReadTopicDao
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
        return 'FORUM_BOL_ReadTopic';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_read_topic';
    }

    /**
     * Returns topic read info by User
     *
     * @param int $topicId
     * @param int $userId
     * @return FORUM_BOL_ReadTopic
     */
    public function findTopicRead( $topicId, $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);
        $example->andFieldEqual('userId', $userId);

        $this->findObjectByExample($example);
    }

    /**
     * Deletes topics read info
     *
     * @param int $topicId
     */
    public function deleteByTopicId( $topicId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('topicId', $topicId);

        $this->deleteByExample($example);
    }

    /**
     * Returns user reads topic ids
     *
     * @param array $topicIds
     * @param int $userId
     * @return array
     */
    public function findUserReadTopicIds( $topicIds, $userId )
    {
        $example = new OW_Example();

        $example->andFieldInArray('topicId', $topicIds);
        $example->andFieldEqual('userId', $userId);

        $query = "
    	SELECT `topicId` FROM `" . $this->getTableName() . "`
   		" . $example;

        return $this->dbo->queryForColumnList($query);
    }
}