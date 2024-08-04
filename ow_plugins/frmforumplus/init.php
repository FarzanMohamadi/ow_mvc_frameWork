<?php
/**
 * FRM Forum Plus
 */
FRMFORUMPLUS_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmforumplus_admin_config', 'frmforumplus/admin', 'FRMFORUMPLUS_CTRL_Admin', 'index'));