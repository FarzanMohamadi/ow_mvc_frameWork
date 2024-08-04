<?php
/**
 * 
 * All rights reserved.
 */
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.sso.bol
 * @since 1.0
 */
class SSO_CMP_ChangePassword extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        $this->assign("change_password_url", OW::getConfig()->getValue('sso', 'ssoUrl') . OW::getConfig()->getValue('sso', 'ssoChangePasswordUrl') . "?service=" . OW::getRouter()->getBaseUrl());
    }
}