<?php
/**
 * FRM Invite
 */
FRMINVITE_CLASS_EventHandler::getInstance()->init();

$router = OW::getRouter();
$router->addRoute(new OW_Route('invite_index', 'invite', 'FRMINVITE_CTRL_Invite', 'index'));
$router->addRoute(new OW_Route('frminvite.admin', 'frminvite/admin', 'FRMINVITE_CTRL_Admin', 'index'));
$router->addRoute(new OW_Route('frminvite.admin.section-id', 'frminvite/admin/:sectionId', 'FRMINVITE_CTRL_Admin', 'index'));
$router->addRoute(new OW_Route('frminvite.admin.link', 'frminvite/link', 'FRMINVITE_CTRL_Admin', 'createInvitationLink'));