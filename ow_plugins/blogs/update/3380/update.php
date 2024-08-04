<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'blogs');

$exArr = array();

try
{
    $groupId = Updater::getAuthorizationService()->findGroupIdByName('blogs');

    if ( $groupId )
    {
        Updater::getDbo()->update("UPDATE `".OW_DB_PREFIX."base_authorization_action` SET `availableForGuest`=0 WHERE `name`='delete_comment_by_content_owner' AND `groupId`={$groupId}");
    }
}
catch ( Exception $e ) { $exArr[] = $e; }
