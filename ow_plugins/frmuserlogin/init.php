<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmuserlogin.admin', 'frmuserlogin/admin', 'FRMUSERLOGIN_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.admin.currentSection', 'frmuserlogin/admin/:currentSection', 'FRMUSERLOGIN_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.index', 'frmuserlogin/default', 'FRMUSERLOGIN_CTRL_Iisuserlogin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.login', 'latest-logins', 'FRMUSERLOGIN_CTRL_Iisuserlogin', 'login'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.active', 'active-sessions', 'FRMUSERLOGIN_CTRL_Iisuserlogin', 'active'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.terminate_device', 'frmuserlogin/terminate-device', 'FRMUSERLOGIN_CTRL_Iisuserlogin', 'terminateDevice'));
FRMUSERLOGIN_CLASS_EventHandler::getInstance()->init();