<?php
/**
 * FRM Ticketing
 */

/**
 * Data Transfer Object for `frmticket_categories` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketCategory extends OW_Entity
{
    const STATUS_ACTIVE = "active";
    const STATUS_DEACTIVATED = "deactivated";

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     *
     * @var string
     */
    public $status = self::STATUS_ACTIVE;
}