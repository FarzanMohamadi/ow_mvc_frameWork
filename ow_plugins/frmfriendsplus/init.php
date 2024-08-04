<?php
FRMFRIENDSPLUS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmfriendsplus_admin_config', 'frmfriendsplus/admin', 'FRMFRIENDSPLUS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmfriendsplus_admin_config_all_users', 'frmfriendsplus/admin/all_users', 'FRMFRIENDSPLUS_CTRL_Admin', 'allUsers'));
