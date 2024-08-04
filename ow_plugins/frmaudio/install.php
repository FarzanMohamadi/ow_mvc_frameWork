<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmaudio', 'audio_dashbord', 1);
OW::getConfig()->saveConfig('frmaudio', 'audio_profile', 1);
OW::getConfig()->saveConfig('frmaudio', 'audio_forum', 1);

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frm_audio`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frm_audio` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `addDateTime` int(10) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` VARCHAR(40) NOT NULL,
  `valid` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
