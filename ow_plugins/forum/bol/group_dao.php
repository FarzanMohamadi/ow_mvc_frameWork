<?php
/**
 * Data Access Object for `forum_group` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_GroupDao extends OW_BaseDao
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
     * @var FORUM_BOL_GroupDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_GroupDao
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
        return 'FORUM_BOL_Group';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_group';
    }

    /**
     * Returns section group id list
     * 
     * @param int $sectionId
     * @return array 
     */
    public function findIdListBySectionId( $sectionId )
    {
        $query = "
    		SELECT `id` FROM `" . $this->getTableName() . "`
    		WHERE `sectionId`=?
    	";

        return $this->dbo->queryForColumnList($query, array($sectionId));
    }

    /**
     * Find latest public groups ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicGroupsIds($first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('isPrivate', 0);
        $example->setLimitClause($first, $count);
        $example->setOrder('id DESC');

        return $this->findIdListByExample($example);
    }

    /**
     * Returns new group order
     * 
     * @param int $sectionId
     * @return int
     */
    public function getNewGroupOrder( $sectionId )
    {
        $query = "
            SELECT MAX( `order` )
            FROM `" . $this->getTableName() . "`
            WHERE `sectionId`=?";

        $order = (int) $this->dbo->queryForColumn($query, array($sectionId));

        return $order + 1;
    }

    /**
     * Returns forum group by entity id
     *
     * @param int $sectionId
     * @param int $entityId
     * @return FORUM_BOL_Group
     */
    public function findByEntityId( $sectionId, $entityId )
    {

        $ex = new OW_Example();
        $ex->andFieldEqual('sectionId', $sectionId);
        $ex->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($ex);
    }
}