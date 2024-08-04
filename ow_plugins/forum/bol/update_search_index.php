<?php
/**
 * Data Transfer Object for `forum_update_search_index` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_UpdateSearchIndex extends OW_Entity
{
    /**
     * Type 
     * @var string
     */
    public $type;

    /**
     * Entity id
     * @var int
     */
    public $entityId;

    /**
     * Last entity id
     * @var int
     */
    public $lastEntityId;

    /**
     * Priority
     * @var integer
     */
    public $priority;
}