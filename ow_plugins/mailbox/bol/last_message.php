<?php
/**
 * Data Transfer Object for `mailbox_last_massage` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 *
 */
class MAILBOX_BOL_LastMessage extends OW_Entity
{
    /**
     * @var integer
     */
    public $conversationId;
    /**
     * @var integer
     */
    public $initiatorMessageId;
    /**
     * @var integer
     */
    public $interlocutorMessageId = 0;
}