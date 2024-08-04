<?php
/**
 * Data Transfer Object for `base_file_temporary` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 */
class BOL_FileTemporary extends OW_Entity
{
    /**
     * @var string
     */
    public $filename;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $addDatetime;
    /**
     * @var int
     */
    public $order;
}
