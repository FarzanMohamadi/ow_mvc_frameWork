<?php
/**
 * Data Transfer Object for `newsfeed_action_set` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_ActionSet extends OW_Entity
{

    /**
     *
     * @var int
     */
    public $actionId;

    /**
     *
     * @var string
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $timestamp;
}