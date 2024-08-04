<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('sso.admin', 'sso/admin', 'SSO_CTRL_Admin', 'index'));
OW::getRouter()->removeRoute('static_sign_in');
//OW::getRouter()->addRoute(new OW_Route('static_sign_in', 'sign-in', 'SSO_CTRL_Controller', 'signIn'));
OW::getRouter()->addRoute(new OW_Route('sso.sign_in_callback', 'after-login', 'SSO_CTRL_Controller', 'signInCallBack'));
OW::getRouter()->removeRoute('base_sign_out');
OW::getRouter()->addRoute(new OW_Route('base_sign_out', 'sign-out', 'SSO_CTRL_Controller', 'signOut'));
OW::getRouter()->addRoute(new OW_Route('sso.sign_out_callback', 'after-logout', 'SSO_CTRL_Controller', 'signOutCallBack'));


SSO_CLASS_EventHandler::getInstance()->init();
