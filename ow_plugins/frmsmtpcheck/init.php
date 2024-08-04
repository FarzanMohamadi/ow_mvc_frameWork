<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsmtpcheck
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmsmtpcheck.admin', 'frmsmtpcheck/admin', "FRMSMTPCHECK_CTRL_Admin", 'index'));
FRMSMTPCHECK_CLASS_EventHandler::getInstance()->init();
