<?php
/**
 * Created by PhpStorm.
 * User: HTahmooresi
 * Date: 2/28/2018
 * Time: 4:53 PM
 */
class FRMSSO_CMP_ChangePassword extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        $this->assign("change_password_url", OW::getConfig()->getValue('frmsso', 'ssoUrl') . OW::getConfig()->getValue('frmsso', 'ssoChangePasswordUrl') . "?service=" . OW::getRouter()->getBaseUrl());
    }
}