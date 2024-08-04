<?php
$errors = array();

try
{
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "event_item` ADD `endDateFlag` BOOL NOT NULL DEFAULT '1'");
}
catch( Exception $e )
{
    $errors[] = $e;
}

try
{
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "event_item` ADD `startTimeDisabled` BOOL NOT NULL DEFAULT '0'");
}
catch( Exception $e )
{
    $errors[] = $e;
}

try
{
    Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "event_item` ADD `endTimeDisabled` BOOL NOT NULL DEFAULT '0'");
}
catch( Exception $e )
{
    $errors[] = $e;
}


$sql = "UPDATE `".OW_DB_PREFIX."base_comment_entity` SET `pluginKey` = :pluginKey WHERE `entityType` = :entityType";

try
{
    Updater::getDbo()->query($sql, array('pluginKey' => 'event', 'entityType' => 'event'));
}
catch( Exception $e )
{
    $errors[] = $e;
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'event');

OW::getStorage()->mkdir( OW_DIR_STATIC_PLUGIN . 'event' . DS, true);
OW::getStorage()->mkdir( OW_DIR_STATIC_PLUGIN . 'event' . DS . 'js' . DS, true  );

OW::getStorage()->copyFile( OW_DIR_PLUGIN . 'event' . DS . 'static' .DS . 'js' . DS . 'event.js', OW_DIR_STATIC_PLUGIN . 'event' . DS . 'js' . DS . 'event.js', true);

