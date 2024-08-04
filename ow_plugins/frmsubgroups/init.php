<?php
/**
 * FRMSUBGROUPS
 */
FRMSUBGROUPS_CLASS_EventHandler::getInstance()->init();

$router = OW::getRouter();
$router->addRoute(new OW_Route('frmsubgroups.group-list', 'groups/:groupId/subgroups', 'FRMSUBGROUPS_CTRL_Subgroups', 'subgroupList'));
