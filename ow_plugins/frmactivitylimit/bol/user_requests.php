<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

class FRMACTIVITYLIMIT_BOL_UserRequests extends OW_Entity
{
    public $userId;
    public $last_reset_timestamp;
    public $db_count;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getLastResetTimestamp()
    {
        return $this->last_reset_timestamp;
    }

    /**
     * @param mixed $last_reset_timestamp
     */
    public function setLastResetTimestamp($last_reset_timestamp)
    {
        $this->last_reset_timestamp = $last_reset_timestamp;
    }

    /**
     * @return mixed
     */
    public function getDbCount()
    {
        return $this->db_count;
    }

    /**
     * @param mixed $db_count
     */
    public function setDbCount($db_count)
    {
        $this->db_count = $db_count;
    }

    /***
     * @return bool
     */
    public function isLocked()
    {
        return (int)$this->db_count === -1;
    }
}
