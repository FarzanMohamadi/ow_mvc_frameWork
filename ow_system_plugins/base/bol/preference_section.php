<?php
/**
 * Data Transfer Object for `preference_section` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PreferenceSection extends OW_Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $sortOrder;
}
