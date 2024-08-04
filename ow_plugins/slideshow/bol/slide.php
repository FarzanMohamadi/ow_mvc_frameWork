<?php
/**
 * Data Transfer Object for `slideshow_slide` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.bol
 * @since 1.4.0
 */
class SLIDESHOW_BOL_Slide extends OW_Entity
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var tring
     */
    public $widgetId;
    /**
     * @var string;
     */
    public $label;
    /**
     * @var string
     */
    public $url;
    /**
     * @var integer
     */
    public $order;
    /**
     * @var integer
     */
    public $width;
    /**
     * @var integer
     */
    public $height;
    /**
     * @var integer
     */
    public $addStamp;
    /**
     * @var string
     */
    public $status;
}            