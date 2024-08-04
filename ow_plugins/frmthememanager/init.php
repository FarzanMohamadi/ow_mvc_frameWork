<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

FRMTHEMEMANAGER_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmthememanager_admin_setting', 'admin/plugins/frmthememanager', 'FRMTHEMEMANAGER_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('create_new_theme_route', 'admin/plugins/frmthememanager/createNewTheme', 'FRMTHEMEMANAGER_CTRL_Admin', 'createNewTheme'));
OW::getRouter()->addRoute(new OW_Route('upload_theme_route', 'admin/plugins/frmthememanager/uploadTheme', 'FRMTHEMEMANAGER_CTRL_Admin', 'uploadTheme'));
OW::getRouter()->addRoute(new OW_Route('edit_theme_route', 'admin/plugins/frmthememanager/createNewTheme/:key', 'FRMTHEMEMANAGER_CTRL_Admin', 'createNewTheme', [$key = array()]));