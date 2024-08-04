<?php
$config = OW::getConfig();
$config->saveConfig('frmgroupsinvitationlink', 'link_expiration_time', 10, 'day(s) to expire group invitation links');
$config->saveConfig('frmgroupsinvitationlink', 'deep_link', 'apphost://host', 'deep link for native app redirection');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgroupsinvitationlink_link`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgroupsinvitationlink_link` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userId` int(11) NOT NULL,
	`groupId` int(11) NOT NULL,
	`hashLink` varchar(150) NOT NULL,
	`createDate` int(11) NOT NULL,
	`expireDate` int(11),
	`isActive` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `hashLink` (`hashLink`)
)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgroupsinvitationlink_link_user`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgroupsinvitationlink_link_user` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`groupId` int(11) NOT NULL,
	`userId` int(11) NOT NULL,
	`linkId` int(11) NOT NULL,
	`isJoined` tinyint(1) NOT NULL default '0',
	`visitDate` int(11),
	`joinDate` int(11),
	`leaveDate` int(11),
	PRIMARY KEY  (`id`)
)DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
OW::getDbo()->query($sql);
