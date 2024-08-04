<?php
try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."photo_album`
        ADD `entityType` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'user' AFTER `userId`;";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."photo_album`
        ADD `entityId` INT NULL DEFAULT NULL AFTER `entityType`;";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

try
{
    $sql = "UPDATE `".OW_DB_PREFIX."photo_album` SET `entityId` = `userId` WHERE `entityId` IS NULL";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'photo');

try {
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'photo_list_index', 'photo', 'mobile_photo', OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e ) { }