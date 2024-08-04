<?php
$exArr = array();
$moduleName = 'friends';

$dbPrefix = OW_DB_PREFIX;

$sql = "ALTER TABLE `{$dbPrefix}friends_friendship` ADD COLUMN   `timeStamp` int(11) NOT NULL, ADD COLUMN `viewed` int(11) NOT NULL,  ADD COLUMN  `active` tinyint(4) NOT NULL default '1', ADD COLUMN  `notificationSent` tinyint(4) NOT NULL default '0';";

Updater::getDbo()->query($sql);




try
{
    OW::getStorage()->removeFile(OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'friend_request.js', true);
    OW::getStorage()->copyFile(OW_DIR_PLUGIN . $moduleName . DS . 'static' . DS . 'js' . DS . 'friend_request.js', OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'friend_request.js', true);
}
catch ( Exception $e )
{
    $exArr[] = $e;
}
