<?php
$config = OW::getConfig();

if ( !$config->configExists('video', 'player_width') )
{
    $config->addConfig('video', 'player_width', 619, 'Main video player width');
}

if ( !$config->configExists('video', 'player_height') )
{
    $config->addConfig('video', 'player_height', 464, 'Main video player height');
}

if ( !$config->configExists('video', 'user_quota') )
{
    $config->addConfig('video', 'user_quota', 500, 'Maximum number of videos per user');
}

if ( !$config->configExists('video', 'videos_per_page') )
{
    $config->addConfig('video', 'videos_per_page', 20, 'Videos per page');
}

$authorization = OW::getAuthorization();
$groupName = 'video';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add');
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');

$dbPref = OW_DB_PREFIX;

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "video_clip`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."video_clip` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `code` text NOT NULL,
  `title` varchar(128) NOT NULL default '',
  `description` text,
  `addDatetime` int(11) NOT NULL default '0',
  `provider` varchar(32) NOT NULL default '',
  `status` enum('approval','approved','blocked') NOT NULL default 'approved',
  `privacy` varchar(50) NOT NULL default 'everybody',
  `thumbUrl` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `thumbCheckStamp` INT NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "video_clip_featured`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."video_clip_featured` (
  `id` int(11) NOT NULL auto_increment,
  `clipId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
