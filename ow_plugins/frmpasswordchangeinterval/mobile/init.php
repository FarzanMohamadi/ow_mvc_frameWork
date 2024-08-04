<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.change-password', 'frmpasswordchangeinterval/changepassword', 'FRMPASSWORDCHANGEINTERVAL_MCTRL_Iispasswordchangeinterval', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.check-validate-password', 'frmpasswordchangeinterval/checkvalidatepassword/:token', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'checkValidatePassword'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.invalid-password', 'frmpasswordchangeinterval/invalidpassword/:userId', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'invalidPassword'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.resend-link', 'frmpasswordchangeinterval/resendlLink', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'resendlLink'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.resend-link-generate-token', 'frmpasswordchangeinterval/resendlLinkGenerateToken/:userId', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'resendlLinkGenerateToken'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.change-user-password', 'frmpasswordchangeinterval/changeuserpassword/:token', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'changeUserPassword'));
OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.change-user-password-with-userId', 'frmpasswordchangeinterval/changeuserpasswordwithuserid/:userId', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'changeUserPasswordWithUserId'));

OW::getRouter()->addRoute(new OW_Route('frmpasswordchangeinterval.forgot.password', 'frmpasswordchangeinterval/forgotPassword', 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'logoutAndGoToForgotPassword'));

FRMPASSWORDCHANGEINTERVAL_MCLASS_EventHandler::getInstance()->init();