<?php
/**
 * Data Transfer Object for `notifications_unsubscribe` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_Unsubscribe extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $code;
    /**
     * 
     * @var int
     */
    public $timeStamp;

}
