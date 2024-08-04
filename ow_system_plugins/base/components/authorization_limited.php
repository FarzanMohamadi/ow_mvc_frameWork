<?php
/**
 * Authorization limited component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.6.0
 */
class BASE_CMP_AuthorizationLimited extends OW_Component
{
    public function __construct( $message )
    {
        parent::__construct();

        $this->assign('message', $message);
    }
}
