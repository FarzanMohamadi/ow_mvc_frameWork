<?php
$plugin = OW::getPluginManager()->getPlugin('event');
$router = OW::getRouter();
EVENT_MCLASS_EventHandler::getInstance()->init();
$router->addRoute(new OW_Route('event.add', 'event/add', 'EVENT_MCTRL_Base', 'add'));
$router->addRoute(new OW_Route('event.edit', 'event/edit/:eventId', 'EVENT_MCTRL_Base', 'edit'));
$router->addRoute(new OW_Route('event.delete', 'event/delete/:eventId', 'EVENT_MCTRL_Base', 'delete'));
$router->addRoute(new OW_Route('event.view', 'event/:eventId', 'EVENT_MCTRL_Base', 'view'));
$router->addRoute(new OW_Route('event.main_menu_route', 'events', 'EVENT_CTRL_Base', 'index'));
$router->addRoute(new OW_Route('event.view_event_list', 'events/:list', 'EVENT_MCTRL_Base', 'eventsList'));
$router->addRoute(new OW_Route('event.invite_accept', 'event/:eventId/:list/invite_accept', 'EVENT_MCTRL_Base', 'inviteListAccept'));
$router->addRoute(new OW_Route('event.invite_decline', 'event/:eventId/:list/invite_decline', 'EVENT_MCTRL_Base', 'inviteListDecline'));
//$router->addRoute(new OW_Route('event.private_event', 'event/:eventId/private', 'EVENT_MCTRL_Base', 'privateEvent'));
$eventHandler = new EVENT_CLASS_EventHandler();
$eventHandler->genericInit();