<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.birthdays
 * @since 1.0
 */

OW::getConfig()->saveConfig('birthdays', 'users_birthday_event_ts', '0');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "birthdays_privacy`;");

OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "birthdays_privacy` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `privacy` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
