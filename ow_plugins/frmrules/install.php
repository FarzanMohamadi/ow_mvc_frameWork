<?php
/**
 * FRM Rules
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules
 * @since 1.0
 */

$config = OW::getConfig();
if (!$config->configExists('frmrules', 'frmrules_guidline')) {
    $config->addConfig('frmrules', 'frmrules_guidline', '');
}

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmrules_item`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmrules_items` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` TEXT,
  `icon` varchar(40),
  `tag` varchar(200),
  `order` int(5),
  `categoryId` int(11),
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmrules_category`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmrules_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `icon` varchar(40),
  `sectionId` int(5),
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
