<?php
/**
 * Data Transfer Object for `newsfeed_action_feed` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_ActionFeed extends OW_Entity
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
    public $activityId;
}