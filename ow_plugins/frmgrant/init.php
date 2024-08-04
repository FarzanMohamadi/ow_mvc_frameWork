<?php
FRMGRANT_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmgrant.admin-config', 'grant/admin', 'FRMGRANT_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmgrant.view', 'grant/:grantId', 'FRMGRANT_CTRL_Grants', 'view'));
OW::getRouter()->addRoute(new OW_Route('frmgrant.index', 'grant/grants', 'FRMGRANT_CTRL_Grants', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmgrant.add', 'grant/add', "FRMGRANT_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmgrant.edit', 'grant/edit/:grantId', "FRMGRANT_CTRL_Save", 'index'));
