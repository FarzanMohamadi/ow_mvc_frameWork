<?php
/**
 * 
 * All rights reserved.
 */

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmsecurefileurl_urls`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmsecurefileurl_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` longtext NOT NULL,
  `hash` longtext NOT NULL,
  `time` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `path` longtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
