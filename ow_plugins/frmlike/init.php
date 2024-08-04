<?php

$router = OW::getRouter();
$router->addRoute(new OW_Route('frmlike.admin', 'admin/plugins/frmlike', "FRMLIKE_CTRL_Admin", 'index'));

$eventHandler = new FRMLIKE_CLASS_EventHandler();
$eventHandler->init();

