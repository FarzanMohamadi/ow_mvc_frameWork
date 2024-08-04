<?php
/**
 * Data Transfer Object for `mailbox_massage` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 *
 */
class MAILBOX_BOL_Message extends OW_Entity
{
    /**
     * @var integer
     */
    public $conversationId;
    /**
     * @var integer
     */
    public $timeStamp;
    /**
     * @var integer
     */
    public $senderId;
    /**
     * @var integer
     */
    public $recipientId;
    /**
     * @var string
     */
    public $text;
    /**
     * @var integer
     */
    public $recipientRead = 0;
    /**
     * @var integer
     */
    public $isSystem = 0;
    /**
     * @var integer
     */
    public $wasAuthorized = 0;
    /**
     * @var int
     */
    public $replyId;
    /**
     * @var integer
     */
    public $changed = 0;
    /**
     * @var integer
     */
    public $isForwarded = 0;

    /**
     * @var Json
     */
    public $costumeFeatures = null;
}