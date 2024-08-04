<?php
OW::getConfig()->saveConfig('frmsms', 'sms_provider', '');
OW::getConfig()->saveConfig('frmsms', 'username', '');
OW::getConfig()->saveConfig('frmsms', 'password', '');
OW::getConfig()->saveConfig('frmsms', 'number', '');
OW::getConfig()->saveConfig('frmsms', 'soap_client_url', '');
OW::getConfig()->saveConfig('frmsms', 'credit_threshold', 10000);
OW::getConfig()->saveConfig('frmsms', 'token_resend_interval', 1);
OW::getConfig()->saveConfig('frmsms', 'max_token_request', 10);
OW::getConfig()->saveConfig('frmsms', 'question_created', false);

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmsms_token`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmsms_token` (
  `id` int(11) NOT NULL auto_increment,
  `mobile` varchar(20),
  `token` varchar(64) NOT NULL,
  `time` int(11),
  `try` int(3),
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmsms_mobile_verify`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmsms_mobile_verify` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11),
  `mobile` varchar(20),
  `valid` int(1),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `userId` (`userId`),
  UNIQUE INDEX `mobile` (`mobile`)
) DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmsms_waitlist`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmsms_waitlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `text` varchar(500) NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");
