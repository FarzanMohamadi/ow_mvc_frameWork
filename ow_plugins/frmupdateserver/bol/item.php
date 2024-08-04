<?php
/**
 * FRM Update Server
 */

/**
 * Data Transfer Object for `frmupdateserver` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
class FRMUPDATESERVER_BOL_Item extends OW_Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    public $type;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var string
     */
    public $guidelineurl;

}