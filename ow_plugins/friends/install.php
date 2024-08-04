<?php
 OW::getAuthorization()->addGroup('friends', false);
OW::getAuthorization()->addAction('friends', 'add_friend');
$dbPrefix = OW_DB_PREFIX;

OW::getDbo()->query("
DROP TABLE IF EXISTS  `{$dbPrefix}friends_friendship`;");

$sql =
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}friends_friendship` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `friendId` int(11) NOT NULL,
  `status` enum('active','pending','ignored') NOT NULL default 'pending',
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL default '1',
  `notificationSent` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId_friendId` (`userId`,`friendId`),
  KEY `friendId` (`friendId`),
  KEY `userId` (`userId`)
) DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);
