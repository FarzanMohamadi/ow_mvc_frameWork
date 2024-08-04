<?php
$plugin = OW::getPluginManager()->getPlugin('frmnews');

OW::getAutoloader()->addClass('Entry', $plugin->getBolDir() . 'dto' . DS . 'entry.php');
OW::getAutoloader()->addClass('EntryDao', $plugin->getBolDir() . 'dao' . DS . 'entry_dao.php');
OW::getAutoloader()->addClass('EntryService', $plugin->getBolDir() . 'service' . DS . 'entry_service.php');
OW::getRouter()->addRoute(new OW_Route('event.user_list', 'event/:eventId/users/:list', 'EVENT_CTRL_Base', 'eventUserLists'));
OW::getRouter()->addRoute(new OW_Route('frmnews', 'news', "FRMNEWS_MCTRL_News", 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('frmnews-default', 'news', 'FRMNEWS_MCTRL_News', 'index'));
OW::getRouter()->addRoute(new OW_Route('user-entry', 'news/:id', "FRMNEWS_MCTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmnews.list', 'news/list/:list', "FRMNEWS_MCTRL_News", 'index'));
OW::getRouter()->addRoute(new OW_Route('entry', 'news/entry/:id', "FRMNEWS_MCTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('entry-part', 'news/entry/:id/:part', "FRMNEWS_MCTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('entry-save-new', 'news/entry/new', "FRMNEWS_MCTRL_Save", 'create'));
OW::getRouter()->addRoute(new OW_Route('entry-save-edit', 'news/entry/edit/:id', "FRMNEWS_MCTRL_Save", 'edit'));
OW::getRouter()->addRoute(new OW_Route('news-manage-drafts', 'news/my-drafts', "FRMNEWS_MCTRL_ManagementEntry", 'index'));
OW::getRouter()->addRoute(new OW_Route('news-manage-entrys', 'news/my-published-entrys', "FRMNEWS_MCTRL_ManagementEntry", 'index'));
OW::getRouter()->addRoute(new OW_Route('news-manage-comments', 'news/my-incoming-comments', "FRMNEWS_MCTRL_ManagementComment", 'index'));

$eventHandler = FRMNEWS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

$mobileEventHandler = FRMNEWS_MCLASS_EventHandler::getInstance();
$mobileEventHandler->init();

FRMNEWS_CLASS_ContentProvider::getInstance()->init();
