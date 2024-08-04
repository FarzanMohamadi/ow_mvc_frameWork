<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */

FRMFARAPAYAMAK_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmfarapayamak_admin_setting', 'admin/plugins/frmfarapayamak', 'FRMFARAPAYAMAK_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmfarapayamak_admin_setting_section', 'admin/plugins/frmfarapayamak/:section', 'FRMFARAPAYAMAK_CTRL_Admin', 'index'));