<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

FRMGRAPH_CLASS_EventHandler::getInstance()->init();

/* Admin routs */

OW::getRouter()->addRoute(new OW_Route('frmgraph.admin', 'frmgraph/admin', "FRMGRAPH_CTRL_Admin", 'index'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.calculate', 'frmgraph/admin/calculate', 'FRMGRAPH_CTRL_Admin', 'calculateAllInformation'));

/* Frountend routs */

OW::getRouter()->addRoute(new OW_Route('frmgraph.graph', 'frmgraph/graph', "FRMGRAPH_CTRL_Graph", 'userAnalytics'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.graph_view.user', 'frmgraph/user-graph-view', "FRMGRAPH_CTRL_Graph", 'userView'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.graph_view.group', 'frmgraph/group-graph-view', "FRMGRAPH_CTRL_Graph", 'groupView'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.user.all_users', 'frmgraph/all-users', "FRMGRAPH_CTRL_Graph", 'allUsers'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.user.one_user', 'frmgraph/one-user', "FRMGRAPH_CTRL_Graph", 'oneUser'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.group.all_groups', 'frmgraph/all-groups', "FRMGRAPH_CTRL_Graph", 'allGroups'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.group.one_group', 'frmgraph/one-group', "FRMGRAPH_CTRL_Graph", 'oneGroup'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.graph_analytics.user', 'frmgraph/user-graph-analytics', "FRMGRAPH_CTRL_Graph", 'userAnalytics'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.graph_analytics.group', 'frmgraph/group-graph-analytics', "FRMGRAPH_CTRL_Graph", 'groupAnalytics'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.graph_statistics.user', 'frmgraph/all-users-statistics', "FRMGRAPH_CTRL_Graph", 'usersStatistics'));
OW::getRouter()->addRoute(new OW_Route('frmgraph.users_list', 'frmgraph/users_list', "FRMGRAPH_CTRL_Graph", 'usersList'));

OW::getRouter()->addRoute(new OW_Route('frmgraph.top_users', 'frmgraph/top_users', "FRMGRAPH_CTRL_Public", 'topUsers'));
