<?php
/**
 * 
 * All rights reserved.
 */
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmguidedtour_userGuide`;");

$sql =
    "CREATE TABLE IF NOT EXISTS `".OW_DB_PREFIX."frmguidedtour_userGuide` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `seenStatus` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8";

OW::getDbo()->query($sql);
