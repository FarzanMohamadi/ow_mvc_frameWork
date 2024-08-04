<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */

FRMFTPMANAGER_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmftpmanager_admin_setting', 'admin/plugins/frmftpmanager', 'FRMFTPMANAGER_CTRL_Admin', 'index'));