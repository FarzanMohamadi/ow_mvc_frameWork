<?php
$dbPrefix = OW_DB_PREFIX;

OW::getConfig()->saveConfig('blogs', 'results_per_page', 10, 'Post number per page');
OW::getConfig()->saveConfig('blogs', 'uninstall_inprogress', 0, '');
OW::getConfig()->saveConfig('blogs', 'uninstall_cron_busy', 0, '');

$authorization = OW::getAuthorization();
$groupName = 'blogs';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'add');
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'publish_notification');

OW::getDbo()->query("
DROP TABLE IF EXISTS `{$dbPrefix}blogs_post`;");

$sql =
    <<<EOT

CREATE TABLE IF NOT EXISTS `{$dbPrefix}blogs_post` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `authorId` INTEGER(11) NOT NULL,
  `title` VARCHAR(512) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `post` TEXT COLLATE utf8_general_ci NOT NULL,
  `timestamp` INTEGER(11) NOT NULL,
  `isDraft` TINYINT(1) NOT NULL,
  `privacy` varchar(50) NOT NULL default 'everybody',
  `bundleId` varchar(128),
  PRIMARY KEY (`id`),
  KEY `authorId` (`authorId`)
)DEFAULT CHARSET=utf8;

EOT;

OW::getDbo()->query($sql);
