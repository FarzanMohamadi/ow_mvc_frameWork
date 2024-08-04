<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

try {
    $tableName = OW_DB_PREFIX . 'frmgraph_graph';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
    $dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropRemoveTriggerOfTableDontNeedBackupQuery);
    $dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropUpdateTriggerOfTableDontNeedBackupQuery);
}catch (Exception $ex){}
try {
    $tableName = OW_DB_PREFIX . 'frmgraph_node';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
    $dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropRemoveTriggerOfTableDontNeedBackupQuery);
    $dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropUpdateTriggerOfTableDontNeedBackupQuery);
}catch (Exception $ex){}
try {
    $tableName = OW_DB_PREFIX . 'frmgraph_group';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
    $dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropRemoveTriggerOfTableDontNeedBackupQuery);
    $dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropUpdateTriggerOfTableDontNeedBackupQuery);
}catch (Exception $ex){}


try {
    $q = 'ALTER TABLE `'.OW_DB_PREFIX . 'frmgraph_graph`
        ADD COLUMN `adjacency_list` LONGTEXT NULL DEFAULT NULL AFTER `id`,
        ADD COLUMN `contents_count` INT(11) NULL DEFAULT NULL AFTER `diameter`,
        ADD COLUMN `pictures_count` INT(11) NULL DEFAULT NULL AFTER `contents_count`,
        ADD COLUMN `videos_count` INT(11) NULL DEFAULT NULL AFTER `pictures_count`,
        ADD COLUMN `news_count` INT(11) NULL DEFAULT NULL AFTER `videos_count`,
        ADD COLUMN `users_interactions_count` INT(11) NULL DEFAULT NULL AFTER `news_count`,
        ADD COLUMN `all_activities_count` INT(11) NULL DEFAULT NULL AFTER `users_interactions_count`,
        ADD COLUMN `g_adjacency_list` LONGTEXT NULL DEFAULT NULL AFTER `all_activities_count`,
        ADD COLUMN `g_cluster_coe_avg` float NULL DEFAULT NULL AFTER `g_adjacency_list`,
        ADD COLUMN `g_component_distr` longtext NULL DEFAULT NULL AFTER `g_cluster_coe_avg`,
        ADD COLUMN `g_degree_distr` longtext NULL DEFAULT NULL AFTER `g_component_distr`,
        ADD COLUMN `g_average_distance` float NULL DEFAULT NULL AFTER `g_degree_distr`,
        ADD COLUMN `g_degree_average` float NULL DEFAULT NULL AFTER `g_average_distance`,
        ADD COLUMN `g_distance_distr` longtext NULL DEFAULT NULL AFTER `g_degree_average`,
        ADD COLUMN `g_edge_count` int(11) NULL DEFAULT NULL AFTER `g_distance_distr`,
        ADD COLUMN `g_node_count` int(11) NULL DEFAULT NULL AFTER `g_edge_count`,
        ADD COLUMN `g_diameter` int(11) NULL DEFAULT NULL AFTER `g_node_count`,
        ADD COLUMN `g_contents_count` int(11) DEFAULT NULL NULL DEFAULT NULL AFTER `g_diameter`,
        ADD COLUMN `g_files_count` int(11)  NULL DEFAULT NULL AFTER `g_contents_count`,
        ADD COLUMN `g_users_interactions_count` int(11)  NULL DEFAULT NULL AFTER `g_files_count`,
        ADD COLUMN `g_all_activities_count` int(11)  NULL DEFAULT NULL AFTER `g_users_interactions_count`
        ;';
    Updater::getDbo()->query($q);
}catch (Exception $ex){}
try {
    $q = 'ALTER TABLE `'.OW_DB_PREFIX . 'frmgraph_node`
        ADD COLUMN `contents_count` INT(11) NULL DEFAULT NULL AFTER `page_rank`,
        ADD COLUMN `pictures_count` INT(11) NULL DEFAULT NULL AFTER `contents_count`,
        ADD COLUMN `videos_count` INT(11) NULL DEFAULT NULL AFTER `pictures_count`,
        ADD COLUMN `news_count` INT(11) NULL DEFAULT NULL AFTER `videos_count`,
        ADD COLUMN `all_contents_count` INT(11) NULL DEFAULT NULL AFTER `news_count`,
        ADD COLUMN `all_activities_count` INT(11) NULL DEFAULT NULL AFTER `all_contents_count`,
        ADD COLUMN `all_done_activities_count` INT(11) NULL DEFAULT NULL AFTER `all_activities_count`
        ;';
    Updater::getDbo()->query($q);
}catch (Exception $ex){}
try {
    $q = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgraph_group` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `gId` int(11),
      `hub` float,
      `authority` float,
      `cluster_coe` float,
      `eccentricity_cent` float,
      `degree_cent` float,
      `closeness_cent` float,
      `betweenness_cent` float,
      `page_rank` float,
      `users_count` int(11) DEFAULT NULL,
      `contents_count` int(11) DEFAULT NULL,
      `files_count` int(11) DEFAULT NULL,
      `users_interactions_count` int(11) DEFAULT NULL,
      `all_activities_count` int(11) DEFAULT NULL,
      `time` int(11),
      `groupId` int(11),
      PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
    Updater::getDbo()->query($q);
}catch (Exception $ex){}

