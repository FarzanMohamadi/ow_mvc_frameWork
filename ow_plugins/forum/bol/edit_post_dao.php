<?php
/**
 * Data Access Object for `forum_edit_post` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_EditPostDao extends OW_BaseDao
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
     * @var FORUM_BOL_EditPostDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_EditPostDao
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
        return 'FORUM_BOL_EditPost';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_edit_post';
    }

    /**
     * Deletes post edit info by postId
     * 
     * @param int $postId
     */
    public function deleteByPostId( $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('postId', $postId);

        $this->deleteByExample($example);
    }

    /**
     * Deletes post edit info by postId list
     * 
     * @param array $postIdList
     */
    public function deleteByPostIdList( $postIdList )
    {
        $example = new OW_Example();

        $example->andFieldInArray('postId', $postIdList);

        $this->deleteByExample($example);
    }

    /**
     * Returns post edit info
     * 
     * @param int $postId
     * @return FORUM_BOL_EditPost
     */
    public function findByPostId( $postId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('postId', $postId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns post edit info list
     * 
     * @param array $postIds
     * @return array of FORUM_BOL_EditPost
     */
    public function findByPostIdList( $postIds )
    {
        $example = new OW_Example();

        $example->andFieldInArray('postId', $postIds);

        return $this->findListByExample($example);
    }
}