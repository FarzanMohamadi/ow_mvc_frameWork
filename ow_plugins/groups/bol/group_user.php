<?php
/**
 * Data Transfer Object for `groups_group_user` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_GroupUser extends OW_Entity
{
    /**
     * @var int
     */
    public $groupId;
    /**
     * @var int
     */
    public $userId;
    /**
     * 
     * @var string
     */
    public $timeStamp;
    
    public $privacy;

    public $last_seen_action;
    
    public function __construct()
    {
        $this->privacy = GROUPS_BOL_Service::PRIVACY_EVERYBODY;
    }
}
