<?php
/**
 * FRMSUBGROUPS
 */
FRMSUBGROUPS_MCLASS_EventHandler::getInstance()->init();

$router = OW::getRouter();
$router->addRoute(new OW_Route('frmsubgroups.group-list', 'groups/:groupId/subgroups', 'FRMSUBGROUPS_MCTRL_Subgroups', 'subgroupList'));
