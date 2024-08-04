<?php
$config = OW::getConfig();

if ( !$config->configExists('forum', 'enable_attachments') )
{
    $config->addConfig('forum', 'enable_attachments', 1, 'Enable file attachments');
}

if ( !$config->configExists('forum', 'uninstall_inprogress') )
{
    $config->addConfig('forum', 'uninstall_inprogress', 0, 'Plugin is being uninstalled');
}

if ( !$config->configExists('forum', 'uninstall_cron_busy') )
{
    $config->addConfig('forum', 'uninstall_cron_busy', 0, 'Uninstall queue is busy');
}

if ( !$config->configExists('forum', 'delete_search_index_cron') )
{
    $config->addConfig('forum', 'delete_search_index_cron', 0, 'Delete search index');
}

if ( !$config->configExists('forum', 'maintenance_mode_state') )
{
    $state = (int) $config->getValue('base', 'maintenance');
    $config->addConfig('forum', 'maintenance_mode_state', $state, 'Stores site maintenance mode config before plugin uninstallation');
}

$dbPref = OW_DB_PREFIX;

$authorization = OW::getAuthorization();
$groupName = 'forum';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'edit');
$authorization->addAction($groupName, 'delete');
$authorization->addAction($groupName, 'subscribe');
$authorization->addAction($groupName, 'add_comment');


OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_edit_post`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_edit_post` (
  `id` int(11) NOT NULL auto_increment,
  `postId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `editStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `postId` (`postId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_group`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_group` (
  `id` int(11) NOT NULL auto_increment,
  `sectionId` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `order` int(11) NOT NULL,
  `entityId` int(11) default NULL,
  `isPrivate` TINYINT(1) NULL DEFAULT '0',
  `roles` TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `sectionId` (`sectionId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_post`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_post` (
  `id` int(11) NOT NULL auto_increment,
  `topicId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `text` text NOT NULL,
  `createStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `topicId` (`topicId`),
  KEY `createStamp` (`createStamp`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_post_attachment`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_post_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `postId` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileNameClean` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `fileSize` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_read_topic`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_read_topic` (
  `id` int(11) NOT NULL auto_increment,
  `topicId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `topicId` (`topicId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_section`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_section` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `order` int(11) NOT NULL,
  `entity` varchar(255) default NULL,
  `isHidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_topic`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_topic` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `title` text NOT NULL,
  `locked` tinyint(1) NOT NULL default '0',
  `sticky` tinyint(1) NOT NULL default '0',
  `temp` TINYINT(1) NOT NULL DEFAULT '0',
  `viewCount` int(11) NOT NULL default '0',
  `lastPostId` int(11) NOT NULL default '0',
  `closeTime` int(11) default NULL,
  `conclusionPostId` int(11) default NULL,
  `status` enum('approval','approved') NOT NULL DEFAULT 'approved',
  PRIMARY KEY  (`id`),
  KEY `groupId` (`groupId`),
  KEY `lastPostId` (`lastPostId`),
  KEY `status` (`status`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_subscription`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_subscription` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `topicId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`,`topicId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);

// Add default section
$sql = "INSERT INTO `".$dbPref."forum_section` 
    (`name`, `order`, `entity`, `isHidden`)
    VALUES ('عمومی', 1, NULL, 0);";

$sectionId = OW::getDbo()->insert($sql);

if ( $sectionId )
{
    // Default group
    $sql = "INSERT INTO `".$dbPref."forum_group` 
        (`sectionId`, `name`, `description`, `order`, `entityId`)
        VALUES (".$sectionId.", 'گپ عمومی', 'بحث در مورد تمامی موضوعات', 1, NULL);";

    $groupId = OW::getDbo()->insert($sql);
}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPref . "forum_update_search_index`;");

$sql = "CREATE TABLE IF NOT EXISTS `".$dbPref."forum_update_search_index` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `type` varchar(50) NOT NULL,
        `entityId` int(10) unsigned NOT NULL,
        `lastEntityId` int(10) unsigned DEFAULT NULL,
        `priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
