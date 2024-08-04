<?php
$errors = array();

try
{
    Updater::getDbo()->query("ALTER TABLE  `" . OW_DB_PREFIX . "event_item` CHANGE `image` `image` VARCHAR( 32 ) NULL DEFAULT NULL");
}
catch( Exception $e )
{
    $errors[] = $e;
}

try
{
    Updater::getDbo()->query("UPDATE  `" . OW_DB_PREFIX . "event_item` SET image = id WHERE image = 1 ");
}
catch( Exception $e )
{
    $errors[] = $e;
}