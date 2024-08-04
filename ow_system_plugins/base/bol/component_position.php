<?php
/**
 * Data Transfer Object for `base_component_position` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentPosition extends OW_Entity
{
    /**
     * @var integer
     */
    public $componentPlaceUniqName;
    /**
     * @var string
     */
    public $section;
    /**
     * @var integer
     */
    public $order;
}
