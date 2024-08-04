<?php
FRMGROUPSRSS_CLASS_EventHandler::getInstance()->genericInit();
OW::getRouter()->addRoute(new OW_Route('frmgroupsrss.admin', 'frmgroupsrss/admin', 'FRMGROUPSRSS_CTRL_Admin', 'index'));