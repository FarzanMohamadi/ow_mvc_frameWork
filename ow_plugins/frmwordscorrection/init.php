<?php
OW::getRouter()->addRoute(new OW_Route('frmwordscorrection-admin', 'admin/frmwordscorrection/settings', "FRMWORDSCORRECTION_CTRL_Admin", 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmwordscorrection-admin-correct', 'admin/frmwordscorrection/correct', "FRMWORDSCORRECTION_CTRL_Admin", 'correct'));

FRMWORDSCORRECTION_CLASS_EventHandler::getInstance()->init();