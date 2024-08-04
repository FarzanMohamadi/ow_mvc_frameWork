<?php

OW::getConfig()->saveConfig('notifications', 'delete_days_for_viewed', 7);
OW::getConfig()->saveConfig('notifications', 'delete_days_for_not_viewed', 60);

$dbPrefix = OW_DB_PREFIX;

$sql =
<<<EOT
DROP TABLE IF EXISTS  `{$dbPrefix}notifications_notification`;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` varchar(64) NOT NULL,
  `action` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL DEFAULT '0',
  `sent` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `data` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`userId`, `entityId`, `entityType`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `viewed` (`viewed`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS  `{$dbPrefix}notifications_rule`;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_rule` (
  `id` int(11) NOT NULL auto_increment,
  `action` varchar(255) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_userId` (`action`,`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS  `{$dbPrefix}notifications_unsubscribe`;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_unsubscribe` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS  `{$dbPrefix}notifications_send_queue`;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_send_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS  `{$dbPrefix}notifications_schedule`;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `schedule` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8;

EOT;

OW::getDbo()->query($sql);
