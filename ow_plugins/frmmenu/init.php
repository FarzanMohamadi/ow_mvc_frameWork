<?php
/**
 * FRM Menu
 */

OW::getRouter()->addRoute(new OW_Route('frmmenu-admin', 'admin/frmmenu/settings', "FRMMENU_CTRL_Admin", 'settings'));
FRMMENU_CLASS_EventHandler::getInstance()->init();
