<?php
/**
 * frmgroupsplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMGROUPSPLUS_CMP_FileListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

if ( OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected') ) {
    try {
        $widgetService = BOL_ComponentAdminService::getInstance();
        $widget = $widgetService->addWidget('FRMGROUPSPLUS_CMP_PendingInvitation', false);
        $widgetUniqID = 'group' . '-' . $widget->className;

        //*remove if exists
        $widgets = $widgetService->findPlaceComponentList('group');
        foreach ($widgets as $w) {
            if ($w['uniqName'] == $widgetUniqID)
                $widgetService->deleteWidgetPlace($widgetUniqID);
        }
        //----------*/

        //add
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'group', $widgetUniqID);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, -1);
    } catch (Exception $e) {
    }
}

try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgroupsplus';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'all-search');
    $authorization->addAction($groupName, 'direct-add');
    $authorization->addAction($groupName, 'add-forced-groups');
    $authorization->addAction($groupName, 'create_group_without_approval_need',false,false);
}catch (Exception $e){}

$config = OW::getConfig();
$config->saveConfig('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed', '["fileFeed","joinFeed","leaveFeed"]');
$config->saveConfig('frmgroupsplus', 'showFileUploadSettings', 1);
$config->saveConfig('frmgroupsplus', 'showAddTopic', 1);
$config->saveConfig('frmgroupsplus', 'groupApproveStatus', 0);

$dbPrefix = OW_DB_PREFIX;

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPrefix . "frmgroupsplus_category`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgroupsplus_category` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`label` VARCHAR(200) NOT NULL,
	 UNIQUE KEY `label` (`label`),
	PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1;";
//installing database
OW::getDbo()->query($sql);

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPrefix . "frmgroupsplus_group_information`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgroupsplus_group_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPrefix . "frmgroupsplus_group_managers`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgroupsplus_group_managers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . $dbPrefix . "frmgroupsplus_group_files`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgroupsplus_group_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `attachmentId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgroupsplus_group_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `whoCanUploadFile` varchar(100) NOT NULL default "participant",
  `whoCanCreateTopic` varchar(100) NOT NULL default "participant",
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

$sql = "DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgroupsplus_forced_groups`;";
OW::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmgroupsplus_forced_groups` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `groupId` INT(11) NOT NULL,
        `canLeave` TINYINT(1) NOT NULL DEFAULT 1,
        `condition` TEXT NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    )
    CHARSET=utf8 AUTO_INCREMENT=1;";
OW::getDbo()->query($sql);