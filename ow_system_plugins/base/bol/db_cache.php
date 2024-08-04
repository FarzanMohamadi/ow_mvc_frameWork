<?php
/**
 * Data Transfer Object for `base_db_cache` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_DbCache extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $value;
    /**
     * 
     * @var int
     */
    public $expireStamp;
}
