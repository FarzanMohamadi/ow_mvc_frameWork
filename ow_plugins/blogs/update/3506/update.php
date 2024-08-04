<?php
$exArr = array();

try
{
    $groupId = Updater::getAuthorizationService()->findGroupIdByName('blogs');

    if ( $groupId )
    {
        Updater::getDbo()->update("UPDATE `".OW_DB_PREFIX."base_authorization_action` SET `availableForGuest`=1 WHERE `name`='view' AND `groupId`={$groupId}");
    }
}
catch ( Exception $e ) { $exArr[] = $e; }
