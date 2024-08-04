<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */

FRMRAHYAB_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmrahyab_admin_setting', 'admin/plugins/frmrahyab', 'FRMRAHYAB_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmrahyab_admin_setting_section', 'admin/plugins/frmrahyab/:section', 'FRMRAHYAB_CTRL_Admin', 'index'));