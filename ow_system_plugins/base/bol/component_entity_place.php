<?php
/**
 * Data Transfer Object for `base_component_entity_place` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentEntityPlace extends OW_Entity
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
     * @var integer
     */
    public $entityId;
    /**
     * @var string
     */
    public $uniqName;
}
