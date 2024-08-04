<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_BOL_Slide extends OW_Entity
{
    /**
     * @var integer
     */
    public $albumId;
    /**
     * @var string
     */
    public $description;
    /**
     * @var integer
     */
    public $order;
}
