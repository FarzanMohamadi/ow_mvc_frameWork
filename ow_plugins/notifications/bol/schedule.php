<?php
/**
 * Data Transfer Object for `notifications_rule` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_Schedule extends OW_Entity
{
    /**
     * @var string
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $schedule;
}
