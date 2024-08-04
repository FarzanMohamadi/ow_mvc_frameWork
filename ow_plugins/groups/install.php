<?php
// Add widgets
$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('GROUPS_CMP_BriefInfoWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('GROUPS_CMP_UserListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = $widgetService->addWidget('GROUPS_CMP_WallWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
//$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

$widget = $widgetService->addWidget('BASE_CMP_CustomHtmlWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');

$widget = $widgetService->addWidget('BASE_CMP_RssWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');

$authorization = OW::getAuthorization();
$groupName = 'groups';
$authorization->addGroup($groupName);

$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'create');
$authorization->addAction($groupName, 'view', true);


$config = OW::getConfig();
OW::getConfig()->saveConfig('groups', 'is_forum_connected', 0, 'Add Forum to Groups plugin');
OW::getConfig()->saveConfig('groups', 'enable_QRSearch', 0);

$dbPrefix = OW_DB_PREFIX;

OW::getDbo()->query("DROP TABLE IF EXISTS  `{$dbPrefix}groups_group`;");

$sql = array();
$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}groups_group` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `imageHash` varchar(32) default NULL,
  `timeStamp` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `privacy` varchar(100) NOT NULL default 'everybody',
  `whoCanView` varchar(100) NOT NULL default 'anyone',
  `whoCanInvite` varchar(100) NOT NULL default 'participant',
  `status` VARCHAR( 100 ) NOT NULL DEFAULT  'active',
  `lastActivityTimeStamp` int(11) DEFAULT '0',
  `isChannel` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY  (`id`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `whoCanView` (`whoCanView`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query("DROP TABLE IF EXISTS  `{$dbPrefix}groups_group_user`;");

$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}groups_group_user` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `privacy` varchar(100) NOT NULL,
  `last_seen_action` int(11) NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `groupId` (`groupId`,`userId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `groupId2` (`groupId`),
  KEY `last_seen_action` (`last_seen_action`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query("DROP TABLE IF EXISTS  `{$dbPrefix}groups_invite`;");

$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}groups_invite` (
  `id` int(11) NOT NULL auto_increment,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `inviterId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `inviteUniq` (`groupId`,`userId`,`inviterId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`),
  KEY `groupId` (`groupId`),
  KEY `viewed` (`viewed`)
) DEFAULT CHARSET=utf8;";

foreach ( $sql as $q )
{
    OW::getDbo()->query($q);
}
