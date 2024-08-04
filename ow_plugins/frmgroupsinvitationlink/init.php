<?php

$router = OW::getRouter();

$router->addRoute(new OW_Route('frmgroupsinvitationlink.join-group', 'groups-invitation/join-group/:code', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'joinLink'));
$router->addRoute(new OW_Route('frmgroupsinvitationlink.add-link', 'groups/add-link/:id', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'addLink'));
$router->addRoute(new OW_Route('frmgroupsinvitationlink.group-links', 'groups/group-links/:id', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'links'));
$router->addRoute(new OW_Route('frmgroupsinvitationlink.link-joins', 'groups/link-joins/:groupId/:linkId', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'linkJoins'));
$router->addRoute(new OW_Route('frmgroupsinvitationlink.link-joins-without-link-id', 'groups/link-joins/:groupId', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'linkJoins'));
$router->addRoute(new OW_Route('frmgroupsinvitationlink.deactivate-link', 'groups/deactivate-link/:linkId', 'FRMGROUPSINVITATIONLINK_CTRL_Link', 'deactivate'));

$router->addRoute(new OW_Route('frmgroupsinvitationlink-admin', 'admin/frmgroupsinvitationlink', "FRMGROUPSINVITATIONLINK_CTRL_Admin", 'index'));

FRMGROUPSINVITATIONLINK_CLASS_EventHandler::getInstance()->init();
