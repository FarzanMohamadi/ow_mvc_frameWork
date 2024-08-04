<?php
/**
 * Data Transfer Object for `mailbox_conversation` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 *
 */
class MAILBOX_BOL_Conversation extends OW_Entity
{
    /**
     * @var integer
     */
    public $initiatorId = 0;
    /**
     * @var integer
     */
    public $interlocutorId = 0;
    /**
     * @var string
     */
    public $subject;
    /**
     * @var integer
     */
    public $read = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
    /**
     * @var integer
     */
    public $deleted = MAILBOX_BOL_ConversationDao::DELETED_NONE;

    /**
     * @var integer
     */
    public $viewed = MAILBOX_BOL_ConversationDao::VIEW_NONE;

   /**
     * @var integer
     */
    public $notificationSent = 0;
    /**
     * @var integer
     */
    public $createStamp;

    public $initiatorDeletedTimestamp = 0;

    public $interlocutorDeletedTimestamp = 0;

    public $lastMessageId = 0;

    public $lastMessageTimestamp = 0;

    public $muted = 0;
}