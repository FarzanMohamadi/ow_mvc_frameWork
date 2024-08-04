<?php
/***
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

// 1. create new table for verified users
Updater::getDbo()->query("
DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmsms_mobile_verify`;"."
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmsms_mobile_verify` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11),
  `mobile` varchar(20),
  `valid` int(1),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `userId` (`userId`),
  UNIQUE INDEX `mobile` (`mobile`)
) DEFAULT CHARSET=utf8;");

// 2. insert verified users to the new table
Updater::getDbo()->query("
INSERT IGNORE INTO `" . OW_DB_PREFIX . "frmsms_mobile_verify`(userId, mobile, valid)
SELECT userId, mobile, valid FROM `" . OW_DB_PREFIX . "frmsms_token`;");

// 3. remove verified users from the old table
Updater::getDbo()->query("DELETE FROM `" . OW_DB_PREFIX . "frmsms_token` WHERE valid=1;");

// 4. remove extra columns from the old table
Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmsms_token` DROP valid;");
Updater::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmsms_token` DROP userId;");