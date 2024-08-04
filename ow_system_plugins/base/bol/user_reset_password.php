<?php
/**
 * Data Transfer Object for `base_vote` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserResetPassword extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * @var string
     */
    public $code;

    /**
     * @var integer
     */
    public $expirationTimeStamp;

    /**
     * @var integer
     */
    public $updateTimeStamp;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode( $code )
    {
        $this->code = $code;
    }

    public function getExpirationTimeStamp()
    {
        return $this->expirationTimeStamp;
    }

    public function setExpirationTimeStamp( $expirationTimeStamp )
    {
        $this->expirationTimeStamp = (int) $expirationTimeStamp;
    }

    public function getUpdateTimeStamp()
    {
        return $this->updateTimeStamp;
    }

    public function setUpdateTimeStamp( $updateTimeStamp )
    {
        $this->updateTimeStamp = (int) $updateTimeStamp;
    }
}
