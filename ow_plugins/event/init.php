<?php
$plugin = OW::getPluginManager()->getPlugin('event');
$router = OW::getRouter();
$router->addRoute(new OW_Route('event.add', 'event/add', 'EVENT_CTRL_Base', 'add'));
$router->addRoute(new OW_Route('event.edit', 'event/edit/:eventId', 'EVENT_CTRL_Base', 'edit'));
$router->addRoute(new OW_Route('event.delete', 'event/delete/:eventId', 'EVENT_CTRL_Base', 'delete'));
$router->addRoute(new OW_Route('event.view', 'event/:eventId', 'EVENT_CTRL_Base', 'view'));
$router->addRoute(new OW_Route('event.main_menu_route', 'events', 'EVENT_CTRL_Base', 'index'));
$router->addRoute(new OW_Route('event.view_event_list', 'events/:list', 'EVENT_CTRL_Base', 'eventsList'));
$router->addRoute(new OW_Route('event.main_user_list', 'event/:eventId/users', 'EVENT_CTRL_Base', 'eventUserLists', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'yes'))));
$router->addRoute(new OW_Route('event.user_list', 'event/:eventId/users/:list', 'EVENT_CTRL_Base', 'eventUserLists'));
//$router->addRoute(new OW_Route('event.private_event', 'event/:eventId/private', 'EVENT_CTRL_Base', 'privateEvent'));
$router->addRoute(new OW_Route('event.invite_accept', 'event/:eventId/:list/invite_accept', 'EVENT_CTRL_Base', 'inviteListAccept'));
$router->addRoute(new OW_Route('event.invite_decline', 'event/:eventId/:list/invite_decline', 'EVENT_CTRL_Base', 'inviteListDecline'));
$router->addRoute(new OW_Route('event.approve', 'event/approve/:eventId', 'EVENT_CTRL_Base', 'approve'));
$router->addRoute(new OW_Route('event.admin', 'admin/plugins/event', "EVENT_CTRL_Admin", 'index'));


$provider = EVENT_CLASS_ContentProvider::getInstance();
$provider->init();

$eventHandler = new EVENT_CLASS_EventHandler();
$eventHandler->genericInit();
$eventHandler->init();
