<?php
/**
 * Data Transfer Object for `newsfeed_status` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_Status extends OW_Entity
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
    public $status;
    
    /**
     * 
     * @var int
     */
    public $timeStamp;
}