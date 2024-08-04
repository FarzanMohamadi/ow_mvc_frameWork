<?php
class FRMGROUPSPLUS_BOL_ForcedGroupsDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var FRMGROUPSPLUS_BOL_ForcedGroupsDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_ForcedGroupsDao
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
        return 'FRMGROUPSPLUS_BOL_ForcedGroups';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsplus_forced_groups';
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function findByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findListByExample($example);
    }
    /**
     * @param $groupId
     * @return FRMGROUPSPLUS_BOL_ForcedGroups
     */
    public function findForeceGroubObjByGroupId ($groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        return $this->findObjectByExample($ex);
    }



    /**
     * @param int $groupId
     * @return int
     */
    public function deleteByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($example);
    }

    /**
     * @param array $objects
     * @return int
     */
    public function batchReplace(array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

}