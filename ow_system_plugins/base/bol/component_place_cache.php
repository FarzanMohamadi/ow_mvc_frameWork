<?php
/**
 * Data Transfer Object for `base_component_place_cache` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentPlaceCache extends OW_Entity
{
    /**
     * @var integer
     */
    public $placeId;
    /**
     * @var string
     */
    public $state;
    /**
     * @var integer
     */
    public $entityId;
}
