<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy
 * @since 1.0
 */
$plugin = OW::getPluginManager()->getPlugin('privacy');

$router = OW::getRouter();
$router->addRoute(new OW_Route('privacy_index', 'profile/privacy', 'PRIVACY_CTRL_Privacy', 'index'));
$router->addRoute(new OW_Route('privacy_no_permission', 'privacy/:username/no-permission', 'PRIVACY_CTRL_Privacy', 'noPermission'));

$handler = new PRIVACY_CLASS_EventHandler();
$handler->init();
$handler->genericInit();
