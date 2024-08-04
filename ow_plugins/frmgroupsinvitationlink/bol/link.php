<?php

/**
 * Data Transfer Object for `frmgroupsinvitationlink_link` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.bol
 * @since 1.0
 */

class FRMGROUPSINVITATIONLINK_BOL_Link extends OW_Entity
{
    public $userId;
    public $groupId;
    public $hashLink;
    public $createDate;
    public $expireDate;
    public $isActive;

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

    public function getHashLink()
    {
        return $this->hashLink;
    }

    public function setHashLink($hashLink)
    {
        $this->hashLink = $hashLink;
    }

    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

}
