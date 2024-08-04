<?php
/**
 * frmhashtag
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmhashtag', 'max_count', 13, 'Hashtag Max Count');

$authorization = OW::getAuthorization();
$groupName = 'frmhashtag';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view_newsfeed', true);

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmhashtag_tag`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmhashtag_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(256) NOT NULL DEFAULT '',
  `count` int(7) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmhashtag_entity`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmhashtag_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagId` int(11) NOT NULL,
  `entityId` VARCHAR(100)  NOT NULL,
  `entityType` VARCHAR(100) NOT NULL,
  `context` VARCHAR(100),
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;");
