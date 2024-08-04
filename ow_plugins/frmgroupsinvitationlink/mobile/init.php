<?php

$router = OW::getRouter();
$router->addRoute(new OW_Route('frmgroupsinvitationlink.join-group', 'groups-invitation/join-group/:code', 'FRMGROUPSINVITATIONLINK_MCTRL_Link', 'joinLink'));

FRMGROUPSINVITATIONLINK_MCLASS_EventHandler::getInstance()->init();