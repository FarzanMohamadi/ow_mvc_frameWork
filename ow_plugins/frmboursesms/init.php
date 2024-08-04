<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

FRMBOURSESMS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmboursesms.admin', 'frmboursesms/admin', 'FRMBOURSESMS_CTRL_Admin', 'index'));
