<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmsso.admin', 'frmsso/admin', 'FRMSSO_CTRL_Admin', 'index'));
OW::getRouter()->removeRoute('static_sign_in');
OW::getRouter()->addRoute(new OW_Route('static_sign_in', 'sign-in', 'FRMSSO_CTRL_Controller', 'signIn'));
OW::getRouter()->addRoute(new OW_Route('frmsso.sign_in_callback', 'after-login', 'FRMSSO_CTRL_Controller', 'signInCallBack'));
OW::getRouter()->removeRoute('base_sign_out');
OW::getRouter()->addRoute(new OW_Route('base_sign_out', 'sign-out', 'FRMSSO_CTRL_Controller', 'signOut'));
OW::getRouter()->addRoute(new OW_Route('frmsso.sign_out_callback', 'after-logout', 'FRMSSO_CTRL_Controller', 'signOutCallBack'));

//OW::getRouter()->removeRoute('base_join');
//$route = new OW_Route('base_join', 'sign-up', 'FRMSSO_CTRL_Controller', 'signUp');
//OW::getRouter()->addRoute($route);


FRMSSO_CLASS_EventHandler::getInstance()->init();
