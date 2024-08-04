<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmpasswordstrengthmeter.admin', 'frmpasswordstrengthmeter/admin', 'FRMPASSWORDSTRENGTHMETER_CTRL_Admin', 'index'));

FRMPASSWORDSTRENGTHMETER_CLASS_EventHandler::getInstance()->init();