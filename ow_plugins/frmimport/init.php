<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmimport.admin', 'frmimport/admin', 'FRMIMPORT_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmimport.import.index', 'frmimport', 'FRMIMPORT_CTRL_Iisimport', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmimport.import.request', 'frmimport/request/:type', 'FRMIMPORT_CTRL_Iisimport', 'request'));
OW::getRouter()->addRoute(new OW_Route('frmimport.import.invitation', 'frmimport/invitation/:type', 'FRMIMPORT_CTRL_Iisimport', 'invitation'));
OW::getRouter()->addRoute(new OW_Route('frmimport.yahoo.callback', 'frmimport/yahooc', 'FRMIMPORT_CTRL_Iisimport', 'yahooCallBack'));
OW::getRouter()->addRoute(new OW_Route('frmimport.google.callback', 'frmimport/googlec', 'FRMIMPORT_CTRL_Iisimport', 'googleCallBack'));

FRMIMPORT_CLASS_EventHandler::getInstance()->init();