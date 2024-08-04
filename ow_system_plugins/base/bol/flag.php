<?php
/**
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Flag extends OW_Entity
{
    /**
     *
     * @var int
     */
    public $userId;
    
    /**
     *
     * @var string
     */
    public $entityType;
    
    /**
     *
     * @var int
     */
    public $entityId;
    
    /**
     *
     * @var string
     */
    public $reason;
    
    /**
     *
     * @var int
     */
    public $timeStamp;
}