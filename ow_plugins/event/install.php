<?php
$authorization = OW::getAuthorization();
$groupName = 'event';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_event');
$authorization->addAction($groupName, 'view_event', true);
$authorization->addAction($groupName, 'add_comment');

OW::getConfig()->saveConfig('event', 'showEventCreator', 1);

OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "event_invite` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "event_item` ");
OW::getDbo()->query("DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "event_user` ");

OW::getDbo()->query("
  CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "event_invite` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `displayInvitation` BOOL NOT NULL DEFAULT '1',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `inviteUnique` (`userId`,`inviterId`,`eventId`),
  KEY `userId` (`userId`),
  KEY `inviterId` (`inviterId`),
  KEY `eventId` (`eventId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "event_item` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `location` text NOT NULL,
  `createTimeStamp` int(11) NOT NULL,
  `startTimeStamp` int(11) NOT NULL,
  `endTimeStamp` int(11) default NULL,
  `userId` int(11) NOT NULL,
  `whoCanView` tinyint(4) NOT NULL,
  `whoCanInvite` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `image` VARCHAR(32) default NULL,
  `endDateFlag` BOOL NOT NULL DEFAULT '0',
  `startTimeDisabled` BOOL NOT NULL DEFAULT '0',
  `endTimeDisabled` BOOL NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

OW::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "event_user` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `eventUser` (`eventId`,`userId`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
