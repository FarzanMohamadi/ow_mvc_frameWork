<?php
OW::getRouter()->addRoute(new OW_Route('frmadvancedscroll-admin', 'admin/frmadvancedscroll/settings', "FRMADVANCEDSCROLL_CTRL_Admin", 'settings'));

FRMADVANCEDSCROLL_CLASS_EventHandler::getInstance()->init();