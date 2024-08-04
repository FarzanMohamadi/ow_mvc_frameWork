<?php
$router = OW::getRouter();
$router->addRoute(new OW_Route('privacy_no_permission', 'privacy/:username/no-permission', 'PRIVACY_MCTRL_Privacy', 'noPermission'));
$router->addRoute(new OW_Route('privacy_index', 'profile/privacy', 'PRIVACY_MCTRL_Privacy', 'index'));
$handler = new PRIVACY_CLASS_EventHandler();
$handler->genericInit();