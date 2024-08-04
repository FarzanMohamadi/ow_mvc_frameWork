<?php
class FRMGROUPSPLUS_BOL_ChannelDao extends OW_BaseDao
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
     * @var FRMGROUPSPLUS_BOL_ChannelDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_ChannelDao
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
        return 'FRMGROUPSPLUS_BOL_Channel';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsplus_channel';
    }

    public function addChannel($groupId)
    {
        $channel = new FRMGROUPSPLUS_BOL_Channel();
        $channel->setGroupId($groupId);
            $this->save($channel);

    }
    public function deleteByGroupId( $groupId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($ex);
    }

    public function findIsExistGroupId($groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        return $this->findObjectByExample($ex);
    }

    public function findIsExistGroupIds($groupIds)
    {
        if (!is_array($groupIds) || empty($groupIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('groupId', $groupIds);
        $result = $this->findListByExample($ex);
        $groupChannelIds = array();
        if (isset($result)) {
            foreach ($result as $item) {
                $groupChannelIds[] = $item->groupId;
            }
        }
        return $groupChannelIds;
    }
}