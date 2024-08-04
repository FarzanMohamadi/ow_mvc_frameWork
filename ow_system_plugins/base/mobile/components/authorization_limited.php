<?php
/**
 * Authorization limited component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.7.5
 */
class BASE_MCMP_AuthorizationLimited extends OW_MobileComponent
{
    public function __construct( $message )
    {
        parent::__construct();

        $this->assign('message', $message);
    }
}
