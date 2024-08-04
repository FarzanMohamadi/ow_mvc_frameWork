<?php
/**
 * frmadvancedstyles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancedstyles
 * @since 1.0
 */
FRMADVANCEDSTYLES_CLASS_EventHandler::getInstance()->init();

$ROUTE_PREFIX = defined('OW_ADMIN_PREFIX')?OW_ADMIN_PREFIX:'admin';
OW::getRouter()->addRoute(new OW_Route('frmadvancedstyles-admin', $ROUTE_PREFIX.'/appearance/customize/css','ADMIN_CTRL_Theme', 'css'));
