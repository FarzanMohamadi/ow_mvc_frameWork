<?php
$service = FRMVIDEOPLUS_BOL_Service::getInstance();
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_VIDEO_RENDERED, array($service, 'onAfterVideoRendered'));

FRMVIDEOPLUS_MCLASS_EventHandler::getInstance()->init();