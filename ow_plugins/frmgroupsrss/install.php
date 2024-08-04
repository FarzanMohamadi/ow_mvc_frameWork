<?php

$authorization = OW::getAuthorization();
$groupName = 'frmgroupsrss';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add',false,false);

$config = OW::getConfig();
$config->saveConfig('frmgroupsrss', 'update_interval', 60);
$config->saveConfig('frmgroupsrss', 'feeds_count', 5);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmgroupsrss_group_rss` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `groupId` INT(11) NOT NULL,
    `creatorId` INT(11) NOT NULL,
    `lastUpdateDate` int(11),
    `lastRssFeedDate` int(11),
    `rssLink` VARCHAR(200),
    PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);
