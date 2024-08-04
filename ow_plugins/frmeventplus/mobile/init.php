<?php
/**
 * frmeventplus
 */
$plugin = OW::getPluginManager()->getPlugin('frmeventplus');
FRMEVENTPLUS_MCLASS_EventHandler::getInstance()->init();
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmeventplus.leave', 'frmeventplus/leave/:eventId', 'FRMEVENTPLUS_MCTRL_Base', 'leave'));
$router->addRoute(new OW_Route('frmeventplus.file-list', 'event/:eventId/files', 'FRMEVENTPLUS_MCTRL_Base', 'fileList'));
$router->addRoute(new OW_Route('frmeventplus.addFile', 'event/:eventId/addFile', 'FRMEVENTPLUS_MCTRL_Base', 'addFile'));
$router->addRoute(new OW_Route('frmeventplus.deleteFile', 'event/:eventId/attachmentId/:attachmentId/deleteFile', 'FRMEVENTPLUS_MCTRL_Base', 'deleteFile'));