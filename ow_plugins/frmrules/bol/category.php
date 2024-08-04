<?php
/**
 * FRM Rules
 */

/**
 * Data Transfer Object for `frmrules` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules.bol
 * @since 1.0
 */
class FRMRULES_BOL_Category extends OW_Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var integer
     */
    public $sectionId;
}