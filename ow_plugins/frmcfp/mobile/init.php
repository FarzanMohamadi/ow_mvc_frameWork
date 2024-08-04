<?php
$plugin = OW::getPluginManager()->getPlugin('frmcfp');
$router = OW::getRouter();
FRMCFP_MCLASS_EventHandler::getInstance()->init();
$router->addRoute(new OW_Route('frmcfp.add', 'cfp/add', 'FRMCFP_MCTRL_Base', 'add'));
$router->addRoute(new OW_Route('frmcfp.edit', 'cfp/edit/:eventId', 'FRMCFP_MCTRL_Base', 'edit'));
$router->addRoute(new OW_Route('frmcfp.delete', 'cfp/delete/:eventId', 'FRMCFP_MCTRL_Base', 'delete'));
$router->addRoute(new OW_Route('frmcfp.view', 'cfp/:eventId', 'FRMCFP_MCTRL_Base', 'view'));
$router->addRoute(new OW_Route('frmcfp.main_menu_route', 'cfps', 'FRMCFP_MCTRL_Base', 'index'));
$router->addRoute(new OW_Route('frmcfp.view_event_list', 'cfps/:list', 'FRMCFP_MCTRL_Base', 'eventsList'));

$router->addRoute(new OW_Route('frmcfp.file-list', 'cfp/:eventId/files', 'FRMCFP_CTRL_Files', 'fileList'));
$router->addRoute(new OW_Route('frmcfp.addFile', 'cfp/:eventId/addFile', 'FRMCFP_CTRL_Files', 'addFile'));
$router->addRoute(new OW_Route('frmcfp.deleteFile', 'cfp/:eventId/attachmentId/:attachmentId/deleteFile', 'FRMCFP_CTRL_Files', 'deleteFile'));

//$router->addRoute(new OW_Route('frmcfp.private_event', 'cfp/:eventId/private', 'FRMCFP_MCTRL_Base', 'privateEvent'));
$eventHandler = new FRMCFP_CLASS_EventHandler();
$eventHandler->genericInit();