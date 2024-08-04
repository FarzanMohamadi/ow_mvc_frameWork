<?php
/**
 * 
 * All rights reserved.
 */

FRMBLOCKINGIP_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmblockingip.authenticate_fail', 'frmblockingip/lock', 'FRMBLOCKINGIP_MCTRL_Iisblockingip', 'index'));
