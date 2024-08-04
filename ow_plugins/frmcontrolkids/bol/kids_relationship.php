<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontrolkids.bol
 * @since 1.0
 */
class FRMCONTROLKIDS_BOL_KidsRelationship extends OW_Entity
{

    public $time;
    public $kidUserId;
    public $parentUserId;
    public $parentEmail;
    
    public function getTime()
    {
        return (int)$this->time;
    }
    
    public function setTime( $value )
    {
        $this->time = (int)$value;
        
        return $this;
    }

    public function getParentUserId()
    {
        return $this->parentUserId;
    }

    public function setParentUserId( $value )
    {
        $this->parentUserId = $value;
        return $this;
    }

    public function getParentEmail()
    {
        return $this->parentEmail;
    }

    public function setParentEmail( $value )
    {
        $this->parentEmail = $value;
        return $this;
    }

    public function getKidUserId()
    {
        return $this->kidUserId;
    }

    public function setKidUserId( $value )
    {
        $this->kidUserId = $value;
        return $this;
    }

}
