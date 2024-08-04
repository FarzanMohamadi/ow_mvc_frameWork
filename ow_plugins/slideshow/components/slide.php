<?php
/**
 * Slide component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.components
 * @since 1.4.0
 */
class SLIDESHOW_CMP_Slide extends OW_Component
{
    public function __construct( $slide )
    {
        parent::__construct();
       
        $this->assign('slide', $slide);
    }
}