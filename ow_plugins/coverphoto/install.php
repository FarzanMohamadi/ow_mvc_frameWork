<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "cover_photo`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "cover_photo` (
  `id` int(11) NOT NULL auto_increment,
  `entityType` varchar(32) NOT NULL,
  `entityId` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `croppedHash` varchar(64),
  `addDateTime` int(10) NOT NULL,
  `isCurrent` int(2) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
