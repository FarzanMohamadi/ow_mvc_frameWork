<?php
/**
 * FRMTICKETING
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing
 * @since 1.0
 */


OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmticketing_tickets` (
  `id` int(11) NOT NULL auto_increment,
  `ticketTrackingNumber` varchar(64) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `title` VARCHAR(512) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `description` TEXT COLLATE utf8_general_ci NOT NULL,
  `categoryId` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL default '0',
  `addition` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");


OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_attachments` (
  `id` int(11) NOT NULL auto_increment,
  `entityId` int(11) NOT NULL,
  `entityType` varchar(64) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileNameClean` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `fileSize` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");


OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_orders` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` VARCHAR( 100 ) NOT NULL DEFAULT  'active',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

OW::getDbo()->query("
CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_categories` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` VARCHAR( 100 ) NOT NULL DEFAULT  'active',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_posts` (
  `id` int(11) NOT NULL auto_increment,
  `ticketId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `text` text NOT NULL,
  `createStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ticketId` (`ticketId`),
  KEY `createStamp` (`createStamp`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);


$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_edit_post` (
  `id` int(11) NOT NULL auto_increment,
  `postId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `editStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `postId` (`postId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmticketing_category_user` (
  `id` int(11) NOT NULL auto_increment,
  `categoryId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `categoryId` (`categoryId`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

$authorization = OW::getAuthorization();
$groupName = 'frmticketing';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view_tickets');