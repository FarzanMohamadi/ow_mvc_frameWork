<?php
/**
 * Data Transfer Object for `base_user_disapprove` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserDisapprove extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * @var bool
     */
    public $changeRequested;

    /**
     * @var string
     */
    public $notes;

    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
    	$this->userId = $userId;

    	return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
    	return $this->userId;
    }
}
