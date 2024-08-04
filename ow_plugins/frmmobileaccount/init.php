<?php
/**
 * frmmobileaccount
 */

OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.admin', 'frmmobileaccount/admin', 'FRMMOBILEACCOUNT_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.login', 'mobile/account/login', 'FRMMOBILEACCOUNT_CTRL_Account', 'login'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.login.mobile_number', 'mobile/account/login/mobile_number/:mobile_number', 'FRMMOBILEACCOUNT_CTRL_Account', 'login'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.login.username', 'mobile/account/login/username/:username', 'FRMMOBILEACCOUNT_CTRL_Account', 'login'));
//OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.join', 'mobile/account/join', 'FRMMOBILEACCOUNT_CTRL_Account', 'join'));
//OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.join.code', 'mobile/account/join/:code', 'FRMMOBILEACCOUNT_CTRL_Account', 'join'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.join.username.mobile_number', 'mobile/account/join_info/:mobile_number/:username', 'FRMMOBILEACCOUNT_CTRL_Account', 'join'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.join.username.mobile_number.email', 'mobile/account/join_info/:mobile_number/:username/:email', 'FRMMOBILEACCOUNT_CTRL_Account', 'join'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.code', 'mobile/account/code/:mobileNumber', 'FRMMOBILEACCOUNT_CTRL_Account', 'code'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.code.mobile_number.username', 'mobile/account/join_code_info/:mobileNumber/:code/:username', 'FRMMOBILEACCOUNT_CTRL_Account', 'code'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.code.mobile_number.username.email', 'mobile/account/join_code_info/:mobileNumber/:code/:username/:email', 'FRMMOBILEACCOUNT_CTRL_Account', 'code'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.mobile_number.username', 'mobile/account/join_code/:mobileNumber/:username', 'FRMMOBILEACCOUNT_CTRL_Account', 'code'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.mobile_number.username.email', 'mobile/account/join_code/:mobileNumber/:username/:email', 'FRMMOBILEACCOUNT_CTRL_Account', 'code'));
OW::getRouter()->addRoute(new OW_Route('frmmobileaccount.resend', 'mobile/account/resend/:mobileNumber', 'FRMMOBILEACCOUNT_CTRL_Account', 'resendCode'));
FRMMOBILEACCOUNT_CLASS_EventHandler::getInstance()->init();
