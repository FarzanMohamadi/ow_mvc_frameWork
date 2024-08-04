<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.admin', 'frmcontrolkids/admin', 'FRMCONTROLKIDS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.index', 'frmcontrolkids/index', 'FRMCONTROLKIDS_CTRL_Iiscontrolkids', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.shadow_login_by_parent', 'frmcontrolkids/shadowLoginByParent/:kidUserId', 'FRMCONTROLKIDS_CTRL_Iiscontrolkids', 'shadowLoginByParent'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.logout_from_shadow_login', 'frmcontrolkids/logoutFromShadowLogin', 'FRMCONTROLKIDS_CTRL_Iiscontrolkids', 'logoutFromShadowLogin'));
OW::getRouter()->addRoute(new OW_Route('frmcontrolkids.enter_parent_email', 'frmcontrolkids/enterParentEmail', 'FRMCONTROLKIDS_CTRL_Iiscontrolkids', 'enterParentEmail'));

FRMCONTROLKIDS_CLASS_EventHandler::getInstance()->init();