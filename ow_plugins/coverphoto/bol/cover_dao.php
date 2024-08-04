<?php
/**
 * coverphoto
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class COVERPHOTO_BOL_CoverDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var COVERPHOTO_BOL_CoverDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return COVERPHOTO_BOL_CoverDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'COVERPHOTO_BOL_Cover';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'cover_photo';
    }

    /***
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function findCovers( $entityType, $entityId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityId', $entityId);
        return $this->findListByExample($ex);
    }

    /***
     * @param $entityType
     * @param $entityId
     * @return mixed
     */
    public function findCover( $entityType, $entityId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityId', $entityId);
        $ex->andFieldEqual('isCurrent', 1);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $entityType
     * @param $entityId
     */
    public function unselectAllCovers( $entityType, $entityId )
    {
        $sql = 'UPDATE '.$this->getTableName().' SET isCurrent=0 WHERE entityType=:t AND entityId=:i';
        $this->dbo->query($sql, array('t' => $entityType, 'i' => $entityId));
    }

    /***
     * @param $id
     */
    public function selectCover( $id )
    {
        $cover = $this->findById($id);
        $cover->isCurrent = 1;
        $this->save($cover);
    }

    /**
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function findListOrderedByTitle( $entityType, $entityId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityId', $entityId);
        $ex->setOrder("`title` DESC");
        return $this->findListByExample($ex);
    }

    /**
     * @param $userId
     * @return array
     */
    public function findCoversByUserId( $userId )
    {
        return $this->findCovers('profile', $userId);
    }

}