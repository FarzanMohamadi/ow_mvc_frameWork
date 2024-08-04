<?php
$authorization = OW::getAuthorization();
$groupName = 'frmcfp';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_event');
$authorization->addAction($groupName, 'view_event', true);
$authorization->addAction($groupName, 'add_comment');

OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmcfp_item` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmcfp_user` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmcfp_event_files` ");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmcfp_item` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `createTimeStamp` int(11) NOT NULL,
  `startTimeStamp` int(11) NOT NULL,
  `endTimeStamp` int(11) default NULL,
  `userId` int(11) NOT NULL,
  `whoCanView` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `file` VARCHAR(32) default NULL,
  `image` VARCHAR(32) default NULL,
  `startTimeDisabled` BOOL NOT NULL DEFAULT '0',
  `endTimeDisabled` BOOL NOT NULL DEFAULT '0',
  `fileDisabled` TINYINT(1) NOT NULL DEFAULT 0,
  `fileNote` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmcfp_user` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `eventUser` (`eventId`,`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

OW::getDbo()->query('
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcfp_event_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `attachmentId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
