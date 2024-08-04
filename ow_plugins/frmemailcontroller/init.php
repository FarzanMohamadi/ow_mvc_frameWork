<?php
OW::getRouter()->addRoute(new OW_Route('frmemailcontroller_admin_config', 'frmemailcontroller/admin', 'FRMEMAILCONTROLLER_CTRL_Admin', 'index'));
FRMEMAILCONTROLLER_CLASS_EventHandler::getInstance()->init();