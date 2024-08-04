<?php
OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmmultilingualsupport_data`;");

OW::getDbo()->query("CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmmultilingualsupport_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `entityType` VARCHAR(256) NOT NULL DEFAULT '',
  `entityLanguage` VARCHAR(256) NOT NULL DEFAULT '',
  `entityData` longtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
