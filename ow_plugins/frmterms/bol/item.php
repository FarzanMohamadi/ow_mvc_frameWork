<?php
/**
 * FRM Terms
 */

/**
 * Data Transfer Object for `frmterms` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms.bol
 * @since 1.0
 */
class FRMTERMS_BOL_Item extends OW_Entity
{
    /**
     * @var integer
     */
    public $langId;

    /**
     * @var integer
     */
    public $use;

    /**
     * @var integer
     */
    public $notification;

    /**
     * @var integer
     */
    public $email;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var integer
     */
    public $sectionId;

    /**
     * @var string
     */
    public $header;

    /**
     * @var string
     */
    public $description;

}