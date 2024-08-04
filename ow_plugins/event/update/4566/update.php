<?php
$errors = array();

try
{
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "event_invite` ADD `displayInvitation` BOOL NOT NULL DEFAULT '1'");
}
catch( Exception $e )
{
    $errors[] = $e;
}