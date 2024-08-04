<?php
/**
 * Data Transfer Object for `user_block` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserBlock extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * @var integer
     */
    public $blockedUserId;

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * @return string
     */
    public function getBlockedUserId()
    {
        return $this->blockedUserId;
    }


    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function setBlockedUserId( $blockedUserId )
    {
        $this->blockedUserId = (int) $blockedUserId;
    }
}

