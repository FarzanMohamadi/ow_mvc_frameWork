<?php
/**
 * FRM Terms
 */

/**
 * Data Transfer Object for `frmrules` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules.bol
 * @since 1.0
 */
class FRMRULES_BOL_Item extends OW_Entity
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
    public $icon;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var integer
     */
    public $order;

    /**
     * @var integer
     */
    public $categoryId;

}