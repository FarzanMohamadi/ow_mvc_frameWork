<?php
$errors = array();

try
{
    Updater::getDbo()->query("ALTER TABLE  `" . OW_DB_PREFIX . "event_user` ADD INDEX  `userId` ( `userId` )");
}
catch( Exception $e )
{
    $errors[] = $e;
}

try
{
    Updater::getDbo()->query("ALTER TABLE  `" . OW_DB_PREFIX . "event_invite` DROP INDEX  `userId`");
}
catch( Exception $e )
{
    $errors[] = $e;
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'event');