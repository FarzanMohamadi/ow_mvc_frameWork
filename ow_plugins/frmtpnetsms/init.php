<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

FRMTPNETSMS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmtpnetsms.admin', 'frmtpnetsms/admin', 'FRMTPNETSMS_CTRL_Admin', 'index'));
