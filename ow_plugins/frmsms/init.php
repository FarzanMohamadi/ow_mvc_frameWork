<?php
OW::getRouter()->addRoute(new OW_Route('frmsms-admin', 'admin/frmsms/settings', "FRMSMS_CTRL_Admin", 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmsms-admin.section-id', 'admin/frmsms/settings/:sectionId', 'FRMSMS_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmsms.mobile_code_form', 'join/check/mobile/code', 'FRMSMS_CTRL_Manager', 'checkCode'));
OW::getRouter()->addRoute(new OW_Route('frmsms.mobile_sms_block', 'join/block', 'FRMSMS_CTRL_Manager', 'block'));
OW::getRouter()->addRoute(new OW_Route('frmsms.resend_token', 'resend/token', 'FRMSMS_CTRL_Manager', 'resendToken'));
OW::getRouter()->addRoute(new OW_Route('frmsms.remove_unverified_number', 'frmsms/remove/unverified', 'FRMSMS_CTRL_Manager', 'removeUnverifiedNumber'));
FRMSMS_CLASS_EventHandler::getInstance()->init();


