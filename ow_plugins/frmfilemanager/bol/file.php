<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

class FRMFILEMANAGER_BOL_File extends OW_Entity
{
    /**
     * @var int
     */
    public $parent_id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $content;
    /**
     * @var int
     */
    public $size;
    /**
     * @var int
     */
    public $mtime;
    /**
     * @var string
     */
    public $mime;
    /**
     * @var int
     */
    public $read;
    /**
     * @var int
     */
    public $write;
    /**
     * @var int
     */
    public $locked;
    /**
     * @var int
     */
    public $hidden;
    /**
     * @var int
     */
    public $width;
    /**
     * @var int
     */
    public $height;

}
