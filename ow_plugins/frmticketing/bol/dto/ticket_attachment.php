<?php
/**
 * FRM Ticketing
 */

/**
 * Data Transfer Object for `frmticket_attachments` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketAttachment extends OW_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $entityId;
    /**
     * @var string
     */
    public $entityType;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $fileNameClean;
    /**
     * @var int
     */
    public $fileSize;
}