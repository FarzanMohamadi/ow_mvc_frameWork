<?php

/**
 * Data Transfer Object for `frmgroupsinvitationlink_link_user` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.bol
 * @since 1.0
 */

class FRMGROUPSINVITATIONLINK_BOL_LinkUser extends OW_Entity
{
    public $userId;
    public $groupId;
    public $linkId;
    public $isJoined;
    public $visitDate;
    public $joinDate;
    public $leaveDate;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function setLinkId($linkId)
    {
        $this->linkId = $linkId;
    }

    public function getIsJoined()
    {
        return $this->isJoined;
    }

    public function setIsJoined($isJoined)
    {
        $this->isJoined = $isJoined;
    }

}
