<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'mailbox');

$errors = array();

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_attachment` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `messageId` int(11) NOT NULL,
      `hash` varchar(64) NOT NULL,
      `fileName` varchar(255) NOT NULL,
      `fileSize` int(10) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `messageId` (`messageId`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
    $errors[] = $ex;
}

try
{
    $sql = " CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_file_upload` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `entityId` varchar(32) NOT NULL,
      `filePath` varchar(2048) NOT NULL,
      `fileName` varchar(255) NOT NULL,
      `fileSize` int(10) NOT NULL DEFAULT '0',
      `timestamp` int(10) NOT NULL DEFAULT '0',
      `userId` int(11) NOT NULL DEFAULT '0',
      `hash` varchar(32) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `hash` (`hash`,`userId`),
      KEY `entityId` (`entityId`),
      KEY `timestamp` (`timestamp`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

    Updater::getDbo()->query($sql);

}
catch ( Exception $ex )
{
    $errors[] = $ex;
}

if ( !empty($errors) )
{
    printVar($errors);
}

