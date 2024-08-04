<?php
/**
 * Data Transfer Object for `base_component_place` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentPlace extends OW_Entity
{
    /**
     * @var integer
     */
    public $componentId;
    /**
     * @var integer
     */
    public $placeId;
    /**
     * @var integer
     */
    public $clone;
    /**
     * 
     * @var string
     */
    public $uniqName;
}
