<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmblockingip.admin', 'frmblockingip/admin', 'FRMBLOCKINGIP_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmblockingip.authenticate_fail', 'frmblockingip/lock', 'FRMBLOCKINGIP_CTRL_Iisblockingip', 'index'));

FRMBLOCKINGIP_CLASS_EventHandler::getInstance()->init();