<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();
if ( !$config->configExists('frmupdateserver', 'prefix_download_path') )
{
    $config->addConfig('frmupdateserver', 'prefix_download_path', '');
}

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_update_information`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_update_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `buildNumber` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `version` varchar(100),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_users_information`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_users_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `ip` varchar(60) NOT NULL,
  `key` varchar(100),
  `developerKey` varchar(100),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_items`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `description` longtext,
  `key` varchar(100) NOT NULL,
  `image` varchar(64) NOT NULL,
  `type` varchar(20) NOT NULL,
  `order` int(11) NOT NULL,
  `guidelineurl` longtext,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_download_file`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_download_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` longtext,
  `version` longtext,
  `time` int(11) NOT NULL,
  `ip` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_category`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmupdateserver_category` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`label` VARCHAR(200) NOT NULL,
	 UNIQUE KEY `label` (`label`),
	PRIMARY KEY (`id`)
	
)DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmupdateserver_plugin_information`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmupdateserver_plugin_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemId` int(11) NOT NULL,
  `categories` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
  
)DEFAULT CHARSET=utf8');
