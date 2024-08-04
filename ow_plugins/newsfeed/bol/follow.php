<?php
/**
 * Data Transfer Object for `newsfeed_follow` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Follow extends OW_Entity
{
    /**
     * 
     * @var int
     */
    public $feedId;
    
    /**
     * 
     * @var string
     */
    public $feedType;
    
    /**
     * 
     * @var int
     */
    public $userId;
    
    /**
     * 
     * @var string
     */
    public $permission;
    
    /**
     * 
     * @var int
     */
    public $followTime;
}