<?php
OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword', FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_FORCE_WITH_NOTIF);

$ex = new OW_Example();
$ex->andFieldEqual('salt', '');
$users = BOL_UserDao::getInstance()->findIdListByExample($ex);
FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->setPasswordExpireForUserIds($users);

$ex = new OW_Example();
$ex->andFieldIsNull('salt');
$users = BOL_UserDao::getInstance()->findIdListByExample($ex);
FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->setPasswordExpireForUserIds($users);
