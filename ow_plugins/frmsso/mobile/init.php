<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->removeRoute('static_sign_in');
OW::getRouter()->addRoute(new OW_Route('static_sign_in', 'sign-in', 'FRMSSO_CTRL_Controller', 'signIn'));
OW::getRouter()->addRoute(new OW_Route('frmsso.sign_in_callback', 'after-login', 'FRMSSO_CTRL_Controller', 'signInCallBack'));
OW::getRouter()->removeRoute('base_sign_out');
OW::getRouter()->addRoute(new OW_Route('base_sign_out', 'sign-out', 'FRMSSO_CTRL_Controller', 'signOut'));
OW::getRouter()->addRoute(new OW_Route('frmsso.sign_out_callback', 'after-logout', 'FRMSSO_CTRL_Controller', 'signOutCallBack'));

FRMSSO_MCLASS_EventHandler::getInstance()->init();