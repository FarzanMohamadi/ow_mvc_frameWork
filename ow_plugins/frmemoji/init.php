<?php
/**
 * frmemoji
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemoji
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmemoji.admin', 'admin/plugins/frmemoji', "FRMEMOJI_CTRL_Admin", 'dept'));
FRMEMOJI_CLASS_EventHandler::getInstance()->init();

