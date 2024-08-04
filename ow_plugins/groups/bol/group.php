<?php
/**
 * Data Transfer Object for `groups_group` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */

class GROUPS_BOL_Group extends OW_Entity
{
    const STATUS_ACTIVE = "active";
    const STATUS_APPROVAL = "approval";
    const STATUS_SUSPENDED = "suspended";
    
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $timeStamp;

    /**
     *
     * @var string
     */
    public $imageHash;

    /**
     *
     * @var int
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $whoCanView;

    /**
     *
     * @var string
     */
    public $whoCanInvite;
    
    /**
     *
     * @var string 
     */
    public $status = self::STATUS_ACTIVE;

    /**
     * @var integer
     */
    public $lastActivityTimeStamp;

    /**
     * @var bool
     */
    public $isChannel;

}
