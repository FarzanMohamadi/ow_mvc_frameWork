<?php
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.index', 'frmcontrolkids/index', 'FRMCONTROLKIDS_MCTRL_Iiscontrolkids', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.shadow_login_by_parent', 'frmcontrolkids/shadowLoginByParent/:kidUserId', 'FRMCONTROLKIDS_MCTRL_Iiscontrolkids', 'shadowLoginByParent'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.logout_from_shadow_login', 'frmcontrolkids/logoutFromShadowLogin', 'FRMCONTROLKIDS_MCTRL_Iiscontrolkids', 'logoutFromShadowLogin'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.enter_parent_email', 'frmcontrolkids/enterParentEmail', 'FRMCONTROLKIDS_MCTRL_Iiscontrolkids', 'enterParentEmail'));
FRMCONTROLKIDS_MCLASS_EventHandler::getInstance()->init();