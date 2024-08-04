<?php
$list = FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->findAll();

foreach ($list as $item)
{
    $user = BOL_UserService::getInstance()->findUserById($item -> userId);
    if(!$user)
    {
        FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->deleteByUserId($item -> userId);
    }
}
