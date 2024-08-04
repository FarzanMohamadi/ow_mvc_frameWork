<?php
$dbo = Updater::getDbo();

$dbo->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmusersimport_admin_verified`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmusersimport_admin_verified` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(128),
  `mobile` varchar(128),
  `verified` tinyint(1) NOT NULL default '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `mobile` (`mobile`),
  UNIQUE INDEX `email` (`email`)
) DEFAULT CHARSET=utf8;");
