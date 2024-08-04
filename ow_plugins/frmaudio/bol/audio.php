<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio.bol
 * @since 1.0
 */

class FRMAUDIO_BOL_Audio extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var integer
     */
    public $addDateTime;

    /**
     * @var integer
     */
    public $object_id;

    /**
     * @var char
     */
    public $object_type;

    /**
     * @var boolean
     */
    public $valid;


}