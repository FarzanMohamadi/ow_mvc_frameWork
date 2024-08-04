<?php
FRMCOMMENTPLUS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmcommentplus.admin', 'admin/plugins/frmcommentplus', "FRMCOMMENTPLUS_CTRL_Admin", 'index'));
