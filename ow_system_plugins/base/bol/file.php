<?php
/**
 * Data Transfer Object for `base_file` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 */
class BOL_File extends OW_Entity
{
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
    public $filename;
    /**
     * @var int
     */
    public $userId;

}
