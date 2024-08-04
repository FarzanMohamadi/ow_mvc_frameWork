<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 4/11/18
 * Time: 9:18 AM
 */

OW::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmsms_token` ADD `mobile` varchar(20);");
$tokens = FRMSMS_BOL_TokenDao::getInstance()->findAll();
/** @var FRMSMS_BOL_Token $token */
foreach ($tokens as $token) {
    $mobile = FRMSMS_BOL_Service::getInstance()->getUserQuestionsMobile($token->userId);
    if (isset($mobile)) {
        FRMSMS_BOL_TokenDao::getInstance()->updateTokenMobile($token->userId,$mobile);
    }
}