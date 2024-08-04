<?php
OW::getRouter()->addRoute(new OW_Route('frmtelegramimport.import', 'frmtelegramimport/import', "FRMTELEGRAMIMPORT_CTRL_Admin", 'import'));
OW::getRouter()->addRoute(new OW_Route('frmtelegramimport.upload', 'frmtelegramimport/upload', "FRMTELEGRAMIMPORT_CTRL_Admin", 'upload'));
OW::getRouter()->addRoute(new OW_Route('frmtelegramimport.admin.help', 'frmtelegramimport/help', "FRMTELEGRAMIMPORT_CTRL_Admin", 'help'));
OW::getRouter()->addRoute(new OW_Route('frmtelegramimport.uploadToGroup', 'frmtelegramimport/upload/:groupId', "FRMTELEGRAMIMPORT_CTRL_Channel", 'uploadToGroup'));
OW::getRouter()->addRoute(new OW_Route('frmtelegramimport.importToGroup', 'frmtelegramimport/import/:groupId', "FRMTELEGRAMIMPORT_CTRL_Channel", 'importToGroup'));

FRMTELEGRAMIMPORT_CLASS_EventHandler::getInstance()->init();
