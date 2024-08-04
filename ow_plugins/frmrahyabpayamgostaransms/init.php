<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

FRMRAHYABPAYAMGOSTARANSMS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmrahyabpayamgostaransms.admin', 'frmrahyabpayamgostaransms/admin', 'FRMRAHYABPAYAMGOSTARANSMS_CTRL_Admin', 'index'));
