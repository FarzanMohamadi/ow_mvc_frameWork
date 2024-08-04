<?php
/**
 * Data Transfer Object for `user_online` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserOnline extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $activityStamp;
    /**
     * @var integer
     */
    public $context;

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
    public function getActivityStamp()
    {
        return $this->activityStamp;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function setActivityStamp( $stamp )
    {
        $this->activityStamp = (int) $stamp;
    }

    public function getContext()
    {
        return (int)$this->context;
    }

    public function setContext( $context )
    {
        $this->context = (int)$context;
    }
}

