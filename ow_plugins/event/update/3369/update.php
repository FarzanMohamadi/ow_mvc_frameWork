<?php
$errors = array();

try
{
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "event_item` ADD  `image` BOOL NOT NULL DEFAULT  '0'");
}
catch( Exception $e )
{
    $errors[] = $e;
}

try
{
    Updater::getDbo()->query("UPDATE `" . OW_DB_PREFIX . "event_item` SET  `image`  = 1");
}
catch( Exception $e )
{
    $errors[] = $e;
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'event');


//printVar($errors);