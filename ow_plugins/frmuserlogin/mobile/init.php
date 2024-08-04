<?php
FRMUSERLOGIN_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.index', 'frmuserlogin/default', 'FRMUSERLOGIN_MCTRL_Iisuserlogin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.login', 'latest-logins', 'FRMUSERLOGIN_MCTRL_Iisuserlogin', 'login'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.active', 'active-sessions', 'FRMUSERLOGIN_MCTRL_Iisuserlogin', 'active'));
OW::getRouter()->addRoute(new OW_Route('frmuserlogin.terminate_device', 'frmuserlogin/terminate-device', 'FRMUSERLOGIN_CTRL_Iisuserlogin', 'terminateDevice'));