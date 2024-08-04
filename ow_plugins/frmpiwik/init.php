<?php
/**
 * FRM Piwik
 */

OW::getRouter()->addRoute(new OW_Route('frmpiwik-admin', 'admin/frmpiwik/settings', "FRMPIWIK_CTRL_Admin", 'settings'));


FRMPIWIK_CLASS_EventHandler::getInstance()->init();