<?php
/**
 * Data Transfer Object for `mailbox_massage` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 *
 */
class MAILBOX_BOL_DeletedMessage extends OW_Entity
{
    /**
     * @var integer
     */
    public $conversationId;
    /**
     * @var int
     */
    public $deletedId;
    /**
     * @var int
     */
    public $time;
}