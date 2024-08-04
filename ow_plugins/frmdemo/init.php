<?php
/**
 * FRM Demo
 */

FRMDEMO_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmdemo.change-theme', 'change_theme', 'FRMDEMO_CTRL_Demo', 'changeTheme'));
OW::getRouter()->addRoute(new OW_Route('update_static_files', 'update-static-files', 'FRMDEMO_CTRL_Demo', 'updateStaticFiles'));