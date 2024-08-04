<?php
OW::getDbo()->query("
ALTER TABLE `" . OW_DB_PREFIX . "frmtelegram_entry`
    CHANGE COLUMN `timestamp` `timestamp`  int(11) NOT NULL DEFAULT '0' AFTER `entry`,
    CHANGE COLUMN `isFile` `isFile` TINYINT(1) NOT NULL DEFAULT '0' AFTER `timestamp`,
	CHANGE COLUMN `fileCaption` `fileCaption` VARCHAR(256) NOT NULL DEFAULT '' AFTER `isFile`,
	CHANGE COLUMN `isDeleted` `isDeleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `fileCaption`;");

OW::getDbo()->query("
ALTER TABLE `" . OW_DB_PREFIX . "frmtelegram_chatrooms`
    CHANGE COLUMN `title` `title`  VARCHAR(256) NOT NULL DEFAULT '' AFTER `chatId`,
    CHANGE COLUMN `type` `type` VARCHAR(128) NOT NULL DEFAULT '' AFTER `title`,
	CHANGE COLUMN `visible` `visible` TINYINT(1) NOT NULL DEFAULT '0' AFTER `type`,
	CHANGE COLUMN `desc` `desc` TEXT NOT NULL DEFAULT '' AFTER `visible`,
	CHANGE COLUMN `orderN` `orderN` int(5) NOT NULL DEFAULT '10' AFTER `desc`;");