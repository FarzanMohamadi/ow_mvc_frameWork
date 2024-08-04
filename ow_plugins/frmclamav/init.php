<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmclamav
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmclamav.admin', 'frmclamav/admin', "FRMCLAMAV_CTRL_Admin", 'index'));
FRMCLAMAV_CLASS_EventHandler::getInstance()->init();
