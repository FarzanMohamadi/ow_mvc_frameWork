<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsso.controllers
 * @since 1.0
 */
class FRMSSO_CTRL_Controller extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        $this->service = FRMSSO_BOL_Service::getInstance();
        parent::__construct();
    }

    public function signIn(array $params = array())
    {
        if (OW::getUser()->isAuthenticated()) {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }
        if ( !empty($_GET['back-uri']) )
        {
            $lastRequestPath = OW::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']);
            FRMSSO_BOL_Service::getInstance()->setLastRequestPath($lastRequestPath);
        }
        $loginUrl = OW::getConfig()->getValue('frmsso', 'ssoUrl') .
            OW::getConfig()->getValue('frmsso', 'ssoLoginUrl') .
            "?service=" . OW::getRouter()->getBaseUrl();
        $this->redirect($loginUrl);
    }

    public function signOut(array $params = array()){
        if (!FRMSSO_BOL_Service::getInstance()->isLoggedInMember() && !FRMSSO_BOL_Service::getInstance()->isSSOLoggedIn()) {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }
        FRMSSO_BOL_Service::getInstance()->logout();
        $logoutUrl = OW::getConfig()->getValue('frmsso', 'ssoUrl') .
            OW::getConfig()->getValue('frmsso', 'ssoLogoutUrl') .
            "?service=" . OW::getRouter()->getBaseUrl();
        $this->redirect($logoutUrl);
    }

    public function signInCallBack(array $params = array())
    {
        FRMSSO_BOL_Service::getInstance()->checkUserAndAuthenticate($_REQUEST['ticket']);
        $redirectUrl = FRMSSO_BOL_Service::getInstance()->getLastRequestPath();
        $this->redirect($redirectUrl);

    }
    public function signOutCallBack(array $params = array()){
        $ticket = isset($_POST['ticket']) ? $_POST['ticket'] : null;
        if($ticket){
            FRMSSO_BOL_Service::getInstance()->addLoggedoutTicket($ticket);
        }
        exit(json_encode(array('status' => "ok")));
    }
}