<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_SuspendedUser extends BASE_CTRL_SuspendedUser
{
    public function index()
    {
        $this->setTemplate( OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'suspended_user_index.html' );
    }
}