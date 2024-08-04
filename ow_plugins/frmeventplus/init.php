<?php
/**
 * frmeventplus
 */
$plugin = OW::getPluginManager()->getPlugin('frmeventplus');
FRMEVENTPLUS_CLASS_EventHandler::getInstance()->init();
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmeventplus.leave', 'frmeventplus/leave/:eventId', 'FRMEVENTPLUS_CTRL_Base', 'leave'));
$router->addRoute(new OW_Route('frmeventplus.admin', 'admin/plugins/frmeventplus', "FRMEVENTPLUS_CTRL_Admin", 'eventCategory'));
$router->addRoute(new OW_Route('frmeventplus.admin.edit.item', 'frmeventplus/admin/edit-item', 'FRMEVENTPLUS_CTRL_Admin', 'editItem'));
$router->addRoute(new OW_Route('frmeventplus.file-list', 'event/:eventId/files', 'FRMEVENTPLUS_CTRL_Base', 'fileList'));
$router->addRoute(new OW_Route('frmeventplus.addFile', 'event/:eventId/addFile', 'FRMEVENTPLUS_CTRL_Base', 'addFile'));
$router->addRoute(new OW_Route('frmeventplus.deleteFile', 'event/:eventId/attachmentId/:attachmentId/deleteFile', 'FRMEVENTPLUS_CTRL_Base', 'deleteFile'));