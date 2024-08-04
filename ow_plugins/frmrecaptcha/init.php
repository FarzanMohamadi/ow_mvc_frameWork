<?php
FRMRECAPTCHA_CLASS_EventHandler::getInstance()->init();

$router = OW::getRouter();
$router->addRoute(new OW_Route('frmrecaptcha.admin', 'admin/plugins/frmrecaptcha', "FRMRECAPTCHA_CTRL_Admin", 'index'));
