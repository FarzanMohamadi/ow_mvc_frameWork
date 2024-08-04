<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'blogs');

$exArr = array();

try
{
    $groupId = Updater::getAuthorizationService()->findGroupIdByName('blogs');

    if ( $groupId )
    {
        Updater::getDbo()->update("ALTER TABLE `".OW_DB_PREFIX."blogs_post` ADD `privacy` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'everybody'");
    }
}
catch ( Exception $e ) { $exArr[] = $e; }
