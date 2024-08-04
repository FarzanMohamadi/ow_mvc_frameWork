<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy
 * @since 1.0
 */
OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "privacy_action_data`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "privacy_action_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`key`),
  KEY `key` (`key`),
  KEY `pluginKey` (`pluginKey`)
) DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "privacy_cron`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "privacy_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `action` varchar(32) NOT NULL,
  `value` varchar(50) NOT NULL,
  `inProcess` tinyint(1) NOT NULL default '0',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`,`action`,`inProcess`),
  KEY `timeStamp` (`timeStamp`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
