<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip.bol
 * @since 1.0
 */
class FRMBLOCKINGIP_BOL_BlockIp extends OW_Entity
{
    public $ip;
    
    public function getIp()
    {
        return $this->ip;
    }
    
    public function setIp( $value )
    {
        $this->ip = $value;
        return $this;
    }
    
    public $time;
    
    public function getTime()
    {
        return (int)$this->time;
    }
    
    public function setTime( $value )
    {
        $this->time = (int)$value;
        
        return $this;
    }

    public $count;

    public function getCount()
    {
        return (int)$this->count;
    }

    public function setCount( $value )
    {
        $this->count = (int)$value;

        return $this;
    }
}
