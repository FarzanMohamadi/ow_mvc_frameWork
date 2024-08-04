<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

$config = OW::getConfig();
if (!$config->configExists('frmgraph', 'server')) {
    $config->addConfig('frmgraph', 'server', 'http://localhost:3434/metrics');
}
if (!$config->configExists('frmgraph', 'question')) {
    $config->addConfig('frmgraph', 'question', '');
}

$authorization = OW::getAuthorization();
$groupName = 'frmgraph';
$authorization->addGroup($groupName);

$authorization->addAction($groupName, 'graphshow');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgraph_graph`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgraph_graph` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adjacency_list` LONGTEXT NULL DEFAULT NULL,
  `cluster_coe_avg` float,
  `component_distr` longtext,
  `degree_distr` longtext,
  `average_distance` float,
  `degree_average` float,
  `distance_distr` longtext,
  `edge_count` int(11),
  `node_count` int(11),
  `diameter` int(11),
  `contents_count` int(11) DEFAULT NULL,
  `pictures_count` int(11) DEFAULT NULL,
  `videos_count` int(11) DEFAULT NULL,
  `news_count` int(11) DEFAULT NULL,
  `users_interactions_count` int(11) DEFAULT NULL,
  `all_activities_count` int(11) DEFAULT NULL,
  `g_adjacency_list` LONGTEXT NULL DEFAULT NULL,
  `g_cluster_coe_avg` float NULL DEFAULT NULL,
  `g_component_distr` longtext NULL DEFAULT NULL,
  `g_degree_distr` longtext NULL DEFAULT NULL,
  `g_average_distance` float NULL DEFAULT NULL,
  `g_degree_average` float NULL DEFAULT NULL,
  `g_distance_distr` longtext NULL DEFAULT NULL,
  `g_edge_count` int(11) NULL DEFAULT NULL,
  `g_node_count` int(11) NULL DEFAULT NULL,
  `g_diameter` int(11) NULL DEFAULT NULL,
  `g_contents_count` int(11) DEFAULT NULL NULL DEFAULT NULL,
  `g_files_count` int(11)  NULL DEFAULT NULL,
  `g_users_interactions_count` int(11)  NULL DEFAULT NULL,
  `g_all_activities_count` int(11)  NULL DEFAULT NULL,
  `time` int(11),
  `groupId` int(11),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgraph_node`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgraph_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11),
  `hub` float,
  `authority` float,
  `cluster_coe` float,
  `eccentricity_cent` float,
  `degree_cent` float,
  `closeness_cent` float,
  `betweenness_cent` float,
  `page_rank` float,
  `contents_count` int(11) DEFAULT NULL,
  `pictures_count` int(11) DEFAULT NULL,
  `videos_count` int(11) DEFAULT NULL,
  `news_count` int(11) DEFAULT NULL,
  `user_all_likes_count` int(11) DEFAULT NULL,
  `user_all_comments_count` int(11) DEFAULT NULL,
  `all_done_likes_count` int(11) DEFAULT NULL,
  `all_done_comments_count` int(11) DEFAULT NULL,
  `all_contents_count` int(11) DEFAULT NULL,
  `all_activities_count` int(11) DEFAULT NULL,
  `all_done_activities_count` int(11) DEFAULT NULL,
  `time` int(11),
  `groupId` int(11),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmgraph_group`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgraph_group` (
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
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
