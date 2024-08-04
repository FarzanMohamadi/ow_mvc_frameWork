<?php
OW::getRouter()->addRoute(new OW_Route('frmjalali_admin_config', 'frmjalali/admin', 'FRMJALALI_CTRL_Admin', 'index'));
FRMJALALI_CLASS_EventHandler::getInstance()->init();
