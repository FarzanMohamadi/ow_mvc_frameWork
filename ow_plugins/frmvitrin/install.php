<?php
/**
 * frmvitrin
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvitrin
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmvitrin', 'description', '');

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmvitrin_item`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmvitrin_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `userId` int(11) NOT NULL,
  `order` int(5) NOT NULL,
  `logo` varchar(64),
  `businessModel` TEXT NOT NULL,
  `targetMarket` Text NOT NULL,
  `vendor` Text NOT NULL,
  `language` varchar(20) NOT NULL,
  `url` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
