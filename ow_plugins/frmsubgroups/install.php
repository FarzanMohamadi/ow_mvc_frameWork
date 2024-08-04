<?php
/**
 * FRMSUBGROUPS
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsubgroups
 * @since 1.0
 */

$dbPrefix = OW_DB_PREFIX;
$sql = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}frmsubgroups_groups` (
  `id` int(11) NOT NULL auto_increment,
  `subGroupId` int(11) NOT NULL,
  `parentGroupId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `subGroupId` (`subGroupId`)
) DEFAULT CHARSET=utf8;";

OW::getDbo()->query($sql);
