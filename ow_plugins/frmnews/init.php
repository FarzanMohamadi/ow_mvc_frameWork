<?php
$plugin = OW::getPluginManager()->getPlugin('frmnews');

OW::getAutoloader()->addClass('Entry', $plugin->getBolDir() . 'dto' . DS . 'entry.php');
OW::getAutoloader()->addClass('EntryDao', $plugin->getBolDir() . 'dao' . DS . 'entry_dao.php');
OW::getAutoloader()->addClass('EntryService', $plugin->getBolDir() . 'service' . DS . 'entry_service.php');

OW::getRouter()->addRoute(new OW_Route('frmnews-uninstall', 'admin/news/uninstall', 'FRMNEWS_CTRL_Admin', 'uninstall'));

OW::getRouter()->addRoute(new OW_Route('entry-save-new', 'news/entry/new', "FRMNEWS_CTRL_Save", 'create'));
OW::getRouter()->addRoute(new OW_Route('entry-save-edit', 'news/entry/edit/:id', "FRMNEWS_CTRL_Save", 'edit'));

OW::getRouter()->addRoute(new OW_Route('entry', 'news/entry/:id', "FRMNEWS_CTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('entry-approve', 'news/entry/approve/:id', "FRMNEWS_CTRL_View", 'approve'));

OW::getRouter()->addRoute(new OW_Route('entry-part', 'news/entry/:id/:part', "FRMNEWS_CTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('user-frmnews', 'news/user/:user', "FRMNEWS_CTRL_UserNews", 'index'));

OW::getRouter()->addRoute(new OW_Route('archive-frmnews', 'news/archive', "FRMNEWS_CTRL_ArchiveNews", 'index'));

OW::getRouter()->addRoute(new OW_Route('user-entry', 'news/:id', "FRMNEWS_CTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('frmnews', 'news', "FRMNEWS_CTRL_News", 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('frmnews.list', 'news/list/:list', "FRMNEWS_CTRL_News", 'index'));

OW::getRouter()->addRoute(new OW_Route('frmnews-manage-entrys', 'news/my-published-entrys', "FRMNEWS_CTRL_ManagementEntry", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmnews-manage-drafts', 'news/my-drafts', "FRMNEWS_CTRL_ManagementEntry", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmnews-manage-comments', 'news/my-incoming-comments', "FRMNEWS_CTRL_ManagementComment", 'index'));

OW::getRouter()->addRoute(new OW_Route('frmnews-admin', 'admin/news', "FRMNEWS_CTRL_Admin", 'index'));

$service = EntryService::getInstance();
$eventHandler = FRMNEWS_CLASS_EventHandler::getInstance();
$eventHandler->init();
FRMNEWS_CLASS_ContentProvider::getInstance()->init();

OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME,     array($service, 'onCollectAddNewContentItem'));
OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME,  array($service, 'onCollectQuickLinks'));
OW::getEventManager()->bind('frmadvancesearch.on_collect_search_items',  array($service, 'onCollectSearchItems'));

