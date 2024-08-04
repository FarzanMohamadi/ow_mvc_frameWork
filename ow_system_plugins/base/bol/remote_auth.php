<?php
/**
 * Data Transfer Object for `base_remote_auth` table.
 *
 * @package ow.base.bol
 * @since 1.0
 */

class BOL_RemoteAuth extends OW_Entity
{
    /**
     * @var string
     */
    public $remoteId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $type;
    
    /**
     * @var string
     */
    public $timeStamp;
    
    /**
     * @var string
     */
    public $custom;
}
