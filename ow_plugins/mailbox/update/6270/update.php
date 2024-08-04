<?php
try
{
    $sql = "ALTER TABLE `" . OW_DB_PREFIX . "mailbox_message` ADD `recipientRead` TINYINT NOT NULL DEFAULT '0';";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "UPDATE `" . OW_DB_PREFIX . "mailbox_message` SET `recipientRead` = 1;";
    $convList = Updater::getDbo()->query($sql);
}
catch ( Exception $e ) { }

try
{
    $sql = "SELECT `isActive` FROM `" . OW_DB_PREFIX . "base_plugin` WHERE `key` = 'usercredits';";
    $isActive = Updater::getDbo()->queryForColumn($sql);

    if ( $isActive )
    {
        $sql = "INSERT INTO `" . OW_DB_PREFIX . "usercredits_action`
            SET `pluginKey` = 'mailbox', `actionKey` = 'read_message', `isHidden` = 0, `active` = 1;";
        Updater::getDbo()->query($sql);
    }
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'mailbox');
