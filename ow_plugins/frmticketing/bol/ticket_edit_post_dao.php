<?php
class FRMTICKETING_BOL_TicketEditPostDao extends OW_BaseDao
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
     * @var FRMTICKETING_BOL_TicketEditPostDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMTICKETING_BOL_TicketEditPostDao
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
        return 'FRMTICKETING_BOL_TicketEditPost';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_edit_post';
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
     * @return FRMTICKETING_BOL_TicketEditPost
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
     * @return array of FRMTICKETING_BOL_TicketEditPostDao
     */
    public function findByPostIdList( $postIds )
    {
        $example = new OW_Example();

        $example->andFieldInArray('postId', $postIds);

        return $this->findListByExample($example);
    }
}