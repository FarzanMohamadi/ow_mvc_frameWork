<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.friends.mobile.components
 * @since 1.7.6
 */

class FRIENDS_MCMP_Notification extends OW_MobileComponent
{
    public function __construct( $message )
    {
        parent::__construct();
        $this->assign('message', strip_tags($message));
    }
}