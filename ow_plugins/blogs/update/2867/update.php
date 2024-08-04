<?php
$groupId = Updater::getAuthorizationService()->findGroupIdByName('blogs');

if ( $groupId )
{

    $dbPrefix = OW_DB_PREFIX;

    $sql = "UPDATE `{$dbPrefix}base_authorization_action` SET `availableForGuest`=1 WHERE `groupId` = {$groupId} AND `name` = 'view'";

    Updater::getDbo()->query($sql);
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'blogs');