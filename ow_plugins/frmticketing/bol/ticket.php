<?php
/**
 * FRM Ticketing
 */

/**
 * Data Transfer Object for `frmticket` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_Ticket extends OW_Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @var integer
     */
    public $timeStamp;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var integer
     */
    public $categoryId;

    /**
     * @var integer
     */
    public $orderId;

    /**
     * @var integer
     */
    public $networkId;

    /**
     * @var integer
     */
    public $locked =0;

    /**
     * @var mixed
     */
    public $addition;


}