<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
OW::getConfig()->saveConfig('frmtelegram', 'icon_type', '2', 'Type for Icon.'); //1=hidden, 2=link, 3=List
OW::getConfig()->saveConfig('frmtelegram', 'link', '', 'URL for Telegram channel/group.');
OW::getConfig()->saveConfig('frmtelegram', 'results_per_page', '30', 'Count for telegram list');
OW::getConfig()->saveConfig('frmtelegram', 'bot_api_key', '....:...-...', 'API KEY');
OW::getConfig()->saveConfig('frmtelegram', 'get_updates_offset', '-1', 'Offset');

//-----create table
OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtelegram_entry`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtelegram_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryId` bigint(20),
  `chatId` int(11),
  `authorName` VARCHAR(128),
  `entry` TEXT,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `isFile` TINYINT(1) NOT NULL DEFAULT '0',
  `fileCaption` VARCHAR(256) NOT NULL DEFAULT '',
  `isDeleted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;");
OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtelegram_chatrooms`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtelegram_chatrooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chatId` bigint(20),
  `title` VARCHAR(256) NOT NULL DEFAULT '',
  `type` VARCHAR(128) NOT NULL DEFAULT '',
  `visible` TINYINT(1) NOT NULL DEFAULT '0',
  `desc` TEXT,
  `orderN` int(5) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;");
