<?php
/**
 * Data Transfer Object for `frmgroupsplus_group_managers` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.bol
 * @since 1.0
 */
class FRMGROUPSPLUS_BOL_GroupManagers extends OW_Entity
{
    /**
     * @var integer
     */
    public $groupId;

    /**
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string integer
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

   /**
     * @var integer
    */
    public $userId;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


}
