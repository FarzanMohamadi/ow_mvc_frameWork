<?php

/***
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Class FRMGROUPSPLUS_BOL_ChannelDao
 */
class FRMGROUPSPLUS_BOL_ChannelService
{

    /**
     * Singleton instance.
     *
     * @var FRMGROUPSPLUS_BOL_ChannelService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_ChannelService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $groupId
     * @param bool $isChannel
     */
    public function setChannel($groupId, $isChannel)
    {
        $isChannel = ($isChannel)? 1:0;
        $group_table = OW_DB_PREFIX . 'groups_group';
        $sql = "UPDATE `" . $group_table."` 
            SET isChannel = {$isChannel}
            WHERE `id`={$groupId}";
        OW::getDbo()->query($sql);
    }

    /***
     * @param $groupId
     * @return bool
     */
    public function isChannel($groupId)
    {
        $group_table = OW_DB_PREFIX . 'groups_group';
        $sql = "SELECT isChannel FROM `" . $group_table."` 
            WHERE id={$groupId}";

        return boolval(OW::getDbo()->queryForColumn($sql));
    }

    /***
     * @param $groupIds
     * @return array
     */
    public function findChannelIds($groupIds)
    {
        if (!is_array($groupIds) || empty($groupIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('id', $groupIds);
        $ex->andFieldEqual('isChannel', 1);
        return GROUPS_BOL_GroupDao::getInstance()->findIdListByExample($ex);
    }
}