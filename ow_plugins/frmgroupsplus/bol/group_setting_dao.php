<?php
class FRMGROUPSPLUS_BOL_GroupSettingDao extends OW_BaseDao
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
     * @var FRMGROUPSPLUS_BOL_GroupSettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_GroupSettingDao
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
        return 'FRMGROUPSPLUS_BOL_GroupSetting';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsplus_group_setting';
    }

    public function addSetting($groupId,$whoCanUploadFile,$whoCanCreateTopic)
    {
        $groupSetting=$this->findByGroupId($groupId);
        if(!isset($groupSetting)) {
            $groupSetting = new FRMGROUPSPLUS_BOL_GroupSetting();
            $groupSetting->setGroupId($groupId);
        }
        $groupSetting->setWhoCanUploadFile($whoCanUploadFile);
        $groupSetting->setWhoCanCreateTopic($whoCanCreateTopic);
        $this->save($groupSetting);

    }
    public function deleteByGroupId( $groupId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($ex);
    }

    /**
     * @param $groupId
     * @return FRMGROUPSPLUS_BOL_GroupSetting
     */
    public function findByGroupId($groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        return $this->findObjectByExample($ex);
    }

    /**
     * @param $groupIds
     * @return array
     */
    public function findByGroupIds($groupIds)
    {
        if (!is_array($groupIds) || empty($groupIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('groupId', $groupIds);
        $list = $this->findListByExample($ex);
        $groups = array();
        foreach ($list as $item) {
            $groups[$item->groupId] = $item;
        }
        return $groups;
    }


}