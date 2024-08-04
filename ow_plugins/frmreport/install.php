<?php
BOL_LanguageService::getInstance()->addPrefix('frmreport','Six month report');

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMREPORT_CMP_ReportsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$authorization = OW::getAuthorization();
$groupName = 'frmreport';
$authorization->addGroup($groupName);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmreport_activity_type`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmreport_activity_type` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8
ROW_FORMAT=DEFAULT";
OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmreport_report`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmreport_report` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `groupId` INT(11) NOT NULL,
    `semester` INT(11) NOT NULL,        
    `year` INT(11) NOT NULL,        
    `creator` INT(11) NOT NULL,
    `createDate` INT(11) NOT NULL,
    `editor` INT(11) NOT NULL,
    `editDate` INT(11) NOT NULL,
    PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8
ROW_FORMAT=DEFAULT";
OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmreport_report_detail`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmreport_report_detail` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `reportId` INT(11) NOT NULL,
    `activityTypeId` INT(11) NOT NULL,
    `count` VARCHAR(4) NOT NULL,
    `description` VARCHAR(2000) NOT NULL,
    PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8
ROW_FORMAT=DEFAULT";
OW::getDbo()->query($sql);

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmreport_activation`;");

$sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmreport_activation` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `groupId` int(11) NOT NULL,
    PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8
ROW_FORMAT=DEFAULT";
OW::getDbo()->query($sql);
