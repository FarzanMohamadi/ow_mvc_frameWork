<?php
/**
 * Data Transfer Object for `preference` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PreferenceData extends OW_Entity
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $value = '';
}
