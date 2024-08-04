<?php
/**
 * Data Transfer Object for `newsfeed_activity` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Activity extends OW_Entity
{
    /**
     * 
     * @var int
     */
    public $actionId;
    
    /**
     * 
     * @var int
     */
    public $userId;
    
    /**
     * 
     * @var string
     */
    public $activityType;
    
    /**
     * 
     * @var int
     */
    public $activityId;
    
    /**
     * 
     * @var string
     */
    public $data;
    
    /**
     * 
     * @var int
     */
    public $timeStamp;
    
     /**
     * 
     * @var int
     */
    public $visibility;
    
    /**
     * 
     * @var string
     */
    public $privacy;
    
    /**
     * 
     * @var string
     */
    public $status = NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE;
}