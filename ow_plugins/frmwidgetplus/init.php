<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmwidgetplus
 * @since 1.0
 */

FRMWIDGETPLUS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmwidgetplus_admin_setting', 'admin/plugins/frmwidgetplus', 'FRMWIDGETPLUS_CTRL_Admin', 'index'));