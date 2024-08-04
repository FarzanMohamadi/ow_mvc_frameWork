<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmactivitylimit.admin', 'admin/plugins/frmactivitylimit', "FRMACTIVITYLIMIT_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmactivitylimit.blocked', 'activitylimit/blocked', "FRMACTIVITYLIMIT_CTRL_Block", 'index'));
FRMACTIVITYLIMIT_CLASS_EventHandler::getInstance()->init();

