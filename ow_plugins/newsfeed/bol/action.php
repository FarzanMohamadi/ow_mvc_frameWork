<?php
/**
 * Data Transfer Object for `newsfeed_action` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Action extends OW_Entity
{
    /**
     *
     * @var int
     */
    public $entityId;

    /**
     *
     * @var string
     */
    public $entityType;

    /**
     *
     * @var string
     */
    public $pluginKey;

    /**
     *
     * @var string
     */
    public $data;
    
    /**
     *
     * @var string
     */
    public $format;
}