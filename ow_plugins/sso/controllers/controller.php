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
class SSO_CTRL_Controller extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        $this->service = SSO_BOL_Service::getInstance();
        parent::__construct();
    }

    public function signIn(array $params = array())
    {
        if (OW::getUser()->isAuthenticated()) {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }

        $redirectUrl = OW::getRouter()->getBaseUrl(). 'sign-in';
        if ( !empty($_GET['code']) )
        {
            SSO_BOL_Service::getInstance()->signInByAuthenticationCode($_GET['code'], $redirectUrl);

            /*$lastRequestPath = OW::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']);
            SSO_BOL_Service::getInstance()->setLastRequestPath($lastRequestPath);*/
        }
        $loginUrl = OW::getConfig()->getValue('sso', 'ssoUrl') .
            OW::getConfig()->getValue('sso', 'ssoLoginUrl') .
            "&redirect_uri=" . $redirectUrl . '&scope=openid&response_type=code&response_mode=query&nonce=avtt5u79xe4';
        $this->redirect($loginUrl);
    }

    public function signOut(array $params = array()){
        if (!SSO_BOL_Service::getInstance()->isLoggedInMember() && !SSO_BOL_Service::getInstance()->isSSOLoggedIn()) {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }
        SSO_BOL_Service::getInstance()->logout();
        $logoutUrl = OW::getConfig()->getValue('sso', 'ssoUrl') .
            OW::getConfig()->getValue('sso', 'ssoLogoutUrl') .
            "&redirect_uri=" . OW::getRouter()->getBaseUrl() . '&scope=openid&response_type=code&response_mode=query&nonce=avtt5u79xe4';
        $this->redirect($logoutUrl);
    }

    public function signInCallBack(array $params = array())
    {
        SSO_BOL_Service::getInstance()->checkUserAndAuthenticate($_REQUEST['ticket']);
        $redirectUrl = SSO_BOL_Service::getInstance()->getLastRequestPath();
        $this->redirect($redirectUrl);

    }
    public function signOutCallBack(array $params = array()){
        $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : null;
        if($ticket){
            SSO_BOL_Service::getInstance()->addLoggedoutTicket($ticket);
        }
        exit(json_encode(array('status' => "ok")));
    }
}