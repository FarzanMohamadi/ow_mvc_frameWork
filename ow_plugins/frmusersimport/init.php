<?php
OW::getRouter()->addRoute(new OW_Route('frmusersimport-admin', 'admin/frmusersimport/index', "FRMUSERSIMPORT_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmusersimport-admin-sleepy', 'admin/frmusersimport/index/:key', "FRMUSERSIMPORT_CTRL_Admin", 'index'));
FRMUSERSIMPORT_CLASS_EventHandler::getInstance()->init();