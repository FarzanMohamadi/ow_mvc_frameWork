<?php
OW::getRouter()->addRoute(new OW_Route('frmsms.mobile_code_form', 'join/check/mobile/code', 'FRMSMS_MCTRL_Manager', 'checkCode'));
OW::getRouter()->addRoute(new OW_Route('frmsms.mobile_sms_block', 'join/block', 'FRMSMS_MCTRL_Manager', 'block'));
OW::getRouter()->addRoute(new OW_Route('frmsms.resend_token', 'resend/token', 'FRMSMS_MCTRL_Manager', 'resendToken'));

OW::getRouter()->addRoute(new OW_Route('frmsms.remove_unverified_number', 'frmsms/remove/unverified', 'FRMSMS_MCTRL_Manager', 'removeUnverifiedNumber'));
FRMSMS_MCLASS_EventHandler::getInstance()->init();