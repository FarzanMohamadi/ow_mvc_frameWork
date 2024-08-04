<?php
/**
 * Data Transfer Object for `newsfeed_like` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Like extends OW_Entity
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
     * @var int
     */
    public $userId;
    
    /**
     * 
     * @var int
     */
    public $timeStamp;
}