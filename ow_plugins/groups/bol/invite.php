<?php
/**
 * Data Transfer Object for `groups_invite` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_Invite extends OW_Entity
{
    /**
     * @var integer
     */
    public $groupId;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $inviterId;
    /**
     * @var integer
     */
    public $timeStamp;

    /**
     *
     * @var integer
     */
    public $viewed;
}

