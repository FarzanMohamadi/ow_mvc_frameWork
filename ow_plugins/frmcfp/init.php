<?php
$plugin = OW::getPluginManager()->getPlugin('frmcfp');
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmcfp.add', 'cfp/add', 'FRMCFP_CTRL_Base', 'add'));
$router->addRoute(new OW_Route('frmcfp.edit', 'cfp/edit/:eventId', 'FRMCFP_CTRL_Base', 'edit'));
$router->addRoute(new OW_Route('frmcfp.delete', 'cfp/delete/:eventId', 'FRMCFP_CTRL_Base', 'delete'));
$router->addRoute(new OW_Route('frmcfp.view', 'cfp/:eventId', 'FRMCFP_CTRL_Base', 'view'));
$router->addRoute(new OW_Route('frmcfp.main_menu_route', 'cfps', 'FRMCFP_CTRL_Base', 'index'));
$router->addRoute(new OW_Route('frmcfp.view_event_list', 'cfps/:list', 'FRMCFP_CTRL_Base', 'eventsList'));
//$router->addRoute(new OW_Route('frmcfp.private_event', 'cfp/:eventId/private', 'FRMCFP_CTRL_Base', 'privateEvent'));

$router->addRoute(new OW_Route('frmcfp.file-list', 'cfp/:eventId/files', 'FRMCFP_CTRL_Files', 'fileList'));
$router->addRoute(new OW_Route('frmcfp.addFile', 'cfp/:eventId/addFile', 'FRMCFP_CTRL_Files', 'addFile'));
$router->addRoute(new OW_Route('frmcfp.deleteFile', 'cfp/:eventId/attachmentId/:attachmentId/deleteFile', 'FRMCFP_CTRL_Files', 'deleteFile'));

$provider = FRMCFP_CLASS_ContentProvider::getInstance();
$provider->init();

$eventHandler = new FRMCFP_CLASS_EventHandler();
$eventHandler->genericInit();
$eventHandler->init();
