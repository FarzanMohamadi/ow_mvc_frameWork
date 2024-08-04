<?php
/**
 * Data Access Object for `frmgroupsplus_group_managers` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.bol
 * @since 1.0
 */
class FRMGROUPSPLUS_BOL_GroupManagersDao extends OW_BaseDao
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
     * @var FRMGROUPSPLUS_BOL_GroupManagersDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_GroupManagersDao
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
        return 'FRMGROUPSPLUS_BOL_GroupManagers';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsplus_group_managers';
    }

    public function getGroupManagersByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findListByExample($example);
    }

    public function getGroupManagersByGroupIds($groupIds)
    {
        if (!is_array($groupIds) || empty($groupIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('groupId', $groupIds);
        $list = $this->findListByExample($example);
        $groupsInformation = array();
        foreach ($list as $item) {
            if (!isset($groupsInformation[$item->groupId])) {
                $groupsInformation[$item->groupId] = array();
            }
            $groupsInformation[$item->groupId][] = $item->userId;
        }
        return $groupsInformation;
    }

    public function getGroupManagerByUidAndGid($groupId,$userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    public function deleteGroupManagerByUidAndGid($groupId, $userIds)
    {
        $example = new OW_Example();
        $example->andFieldInArray('userId', $userIds);
        $example->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($example);
    }

    public function deleteGroupManagerByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($example);
    }
    public function deleteGroupManagerByUserId($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->deleteByExample($example);
    }

    public function addUserAsManager($groupId, $userId)
    {
        $groupManagers = new FRMGROUPSPLUS_BOL_GroupManagers();
        $groupManagers->setGroupId($groupId);
        $groupManagers->setUserId($userId);
        $this->save($groupManagers);
    }

}