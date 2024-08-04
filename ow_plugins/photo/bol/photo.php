<?php
/**
 * Data Transfer Object for `photo` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_Photo extends OW_Entity
{
    /**
     * @var integer
     */
    public $albumId;
    /**
     * @var string
     */
    public $description;
    /**
     * @var integer
     */
    public $addDatetime;
    /**
     * @var string
     */
    public $status;
    /**
     * 
     * @var integer
     */
    public $hasFullsize;
    /**
     * @var string
     */
    public $privacy;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var string
     */
    public $uploadKey;
    
    public $dimension;
    
    public function getDimension()
    {
        return $this->dimension;
    }

    public function setDimension( $value )
    {
        $this->dimension = $value;
        
        return $this;
    }
}
