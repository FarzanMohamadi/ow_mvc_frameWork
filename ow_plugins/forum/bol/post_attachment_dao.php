<?php
/**
 * Data Access Object for `forum_post_attachment` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_PostAttachmentDao extends OW_BaseDao
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
     * @var FORUM_BOL_PostAttachmentDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_PostAttachmentDao
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
        return 'FORUM_BOL_PostAttachment';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_post_attachment';
    }

    public function findAttachmentsByPostIdList( $postIds )
    {
        $query = "
            SELECT *
            FROM `" . $this->getTableName() . "`
            WHERE `postId` IN (" . $this->dbo->mergeInClause($postIds) . ")
        ";

        return $this->dbo->queryForList($query);
    }

    public function findAttachmentsByPostId( $postId )
    {
        $query = "
            SELECT *
            FROM `" . $this->getTableName() . "`
            WHERE `postId` = :postId
        ";

        return $this->dbo->queryForList($query, array('postId' => $postId));
    }

    public function getAttachmentsCountByTopicIdList( $topicIds )
    {
        $postDao = FORUM_BOL_PostDao::getInstance();

        $query = "
            SELECT `p`.`topicId`, COUNT(*) AS `attachments`
            FROM `" . $this->getTableName() . "` AS `a`
            LEFT JOIN `" . $postDao->getTableName() . "` AS `p` ON (`a`.`postId`=`p`.`id`)
            WHERE `p`.`topicId` IN (" . $this->dbo->mergeInClause($topicIds) . ")
            GROUP BY `topicId`
        ";

        return $this->dbo->queryForList($query);
    }
}