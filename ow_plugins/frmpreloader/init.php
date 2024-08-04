<?php
/**
 * FRM Preloader
 */
OW::getRouter()->addRoute(new OW_Route('frmpreloader-admin', 'admin/frmpreloader/settings', "FRMPRELOADER_CTRL_Admin", 'settings'));

FRMPRELOADER_CLASS_EventHandler::getInstance()->init();
