<?php
OW::getRouter()->addRoute(new OW_Route('frmadminnotification-admin', 'admin/frmadminnotification/settings', "FRMADMINNOTIFICATION_CTRL_Admin", 'settings'));

FRMADMINNOTIFICATION_CLASS_EventHandler::getInstance()->init();