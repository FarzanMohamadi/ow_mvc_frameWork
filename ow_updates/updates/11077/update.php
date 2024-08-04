<?php
$findUsers = " select id FROM " . OW_DB_PREFIX . "base_user WHERE ";
$findUsers .= " `username` like '%۱%'";
$findUsers .= " or `username` like '%۲%'";
$findUsers .= " or `username` like '%۳%'";
$findUsers .= " or `username` like '%۴%'";
$findUsers .= " or `username` like '%۵%'";
$findUsers .= " or `username` like '%۶%'";
$findUsers .= " or `username` like '%۷%'";
$findUsers .= " or `username` like '%۸%'";
$findUsers .= " or `username` like '%۹%'";
$findUsers .= " or `username` like '%۰%'";
$findUsers .= " or `email` like '%۱%'";
$findUsers .= " or `email` like '%۲%'";
$findUsers .= " or `email` like '%۳%'";
$findUsers .= " or `email` like '%۴%'";
$findUsers .= " or `email` like '%۵%'";
$findUsers .= " or `email` like '%۶%'";
$findUsers .= " or `email` like '%۷%'";
$findUsers .= " or `email` like '%۸%'";
$findUsers .= " or `email` like '%۹%'";
$findUsers .= " or `email` like '%۰%'";
$userIds = Updater::getDbo()->queryForColumnList($findUsers);
if (sizeof($userIds) > 0) {
    $users = BOL_UserService::getInstance()->findUserListByIdList($userIds);
    foreach ($users as $user) {
        $user->username = UTIL_HtmlTag::convertPersianNumbers($user->username);
        $user->email = UTIL_HtmlTag::convertPersianNumbers($user->email);

        $userByUsername = BOL_UserService::getInstance()->findByUsername($user->username);
        if ($userByUsername != null && $userByUsername->id != $user->id) {
            $user->username = $user->username . $user->username;
        }

        $userByEmail = BOL_UserService::getInstance()->findByEmail($user->email);
        if ($userByEmail != null && $userByEmail->id != $user->id) {
            $user->email = $user->username . $user->email;
        }

        BOL_UserDao::getInstance()->save($user);
    }
}