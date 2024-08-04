<?php
/**
 * frmvideoplus
 */
$service = FRMVIDEOPLUS_BOL_Service::getInstance();
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_VIDEO_RENDERED, array($service, 'onAfterVideoRendered'));

FRMVIDEOPLUS_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmvideoplus_uninstall', 'frmvideoplus/admin/uninstall', 'FRMVIDEOPLUS_CTRL_Admin', 'uninstall'));
