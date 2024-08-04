<?php
/**
 * Data Transfer Object for `mailbox_attachment` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 *
 */
class MAILBOX_BOL_Attachment extends OW_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $messageId;
    /**
     * @var int
     */
    public $hash;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var int
     */
    public $fileSize;
    /**
     * @var string
     */
    public $thumbName;
}