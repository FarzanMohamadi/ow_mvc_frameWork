<?php
/**
 * frmcompetition
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcompetition
 * @since 1.0
 */
OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmcompetition_competition`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcompetition_competition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT,
  `image` varchar(100),
  `type` varchar(100) NOT NULL,
  `active` int(1) NOT NULL,
  `startDate` int(11) NOT NULL,
  `endDate` int(11) NOT NULL,
  `dateCreated` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmcompetition_competition_user`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcompetition_competition_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competitionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmcompetition_competition_group`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmcompetition_competition_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competitionId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

