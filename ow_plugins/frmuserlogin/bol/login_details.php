<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmuserlogin.bol
 * @since 1.0
 */
class FRMUSERLOGIN_BOL_LoginDetails extends OW_Entity
{

    public $ip;
    public $time;
    public $browser;
    public $userId;
    
    public function getIp()
    {
        return $this->ip;
    }
    
    public function setIp( $value )
    {
        $this->ip = $value;
        return $this;
    }
    
    public function getTime()
    {
        return (int)$this->time;
    }
    
    public function setTime( $value )
    {
        $this->time = (int)$value;
        
        return $this;
    }

    public function getBrowser()
    {
        return $this->browser;
    }

    public function setBrowser( $value )
    {
        $this->browser = $value;
        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $value )
    {
        $this->userId = $value;
        return $this;
    }

}
