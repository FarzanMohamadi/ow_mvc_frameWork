<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordchangeinterval.bol
 * @since 1.0
 */
class FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidation extends OW_Entity
{
    public $userId;
    public $valid;
    public $token;
    public $tokenTime;
    public $passwordTime;

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    public function getValid()
    {
        return $this->valid;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setTokenTime($tokenTime)
    {
        $this->tokenTime = $tokenTime;
    }

    public function getTokenTime()
    {
        return $this->tokenTime;
    }

    public function setPasswordTime($passwordTime)
    {
        $this->passwordTime = $passwordTime;
    }

    public function getPasswordTime()
    {
        return $this->passwordTime;
    }
}
