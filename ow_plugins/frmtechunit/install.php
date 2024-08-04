<?php
OW::getConfig()->saveConfig('frmtechunit', 'db_initialized', false, 'Has DB initialized yet?');

$groupName = 'frmtechunit';
$authorization = OW::getAuthorization();
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add');
$authorization->addAction($groupName, 'view',true);

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtechunit_section`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtechunit_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128),
  `name` VARCHAR(128),
  `required` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtechunit_unit`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtechunit_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128),
  `image` VARCHAR(32) default NULL,
  `qr_code` VARCHAR(32) default NULL,
  `manager` VARCHAR(128),
  `address` VARCHAR(512),
  `phone` VARCHAR(15),
  `email` VARCHAR(256),
  `website` VARCHAR(256),
  `timestamp`  int(11) NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmtechunit_unit_section`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmtechunit_unit_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unitId` int(11) NOT NULL,
  `sectionId` int(11) NOT NULL,
  `content` TEXT,
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;");
