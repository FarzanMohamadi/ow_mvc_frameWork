<?php
OW::getRouter()->addRoute(new OW_Route('frmadvanceeditor.admin_config','frmadvanceeditor/admin','FRMADVANCEEDITOR_CTRL_Admin','index'));

FRMADVANCEEDITOR_CLASS_EventHandler::getInstance()->init();