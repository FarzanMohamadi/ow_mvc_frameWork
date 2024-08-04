<?php
/**
 * frmgroupsplus
 */
$plugin = OW::getPluginManager()->getPlugin('frmgroupsplus');
FRMGROUPSPLUS_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmgroupsplus.file-list', 'groups/:groupId/files', 'FRMGROUPSPLUS_MCTRL_Groups', 'fileList'));
OW::getRouter()->addRoute(new OW_Route('frmgroupsplus.addFile', 'groups/:groupId/addFile', 'FRMGROUPSPLUS_MCTRL_Groups', 'addFile'));
OW::getRouter()->addRoute(new OW_Route('frmgroupsplus.deleteFile', 'groups/:groupId/attachmentId/:attachmentId/deleteFile', 'FRMGROUPSPLUS_MCTRL_Groups', 'deleteFile'));
OW::getRouter()->addRoute(new OW_Route('frmgroupsplus.group-approve', 'groups/:groupId/approve', 'FRMGROUPSPLUS_MCTRL_Groups', 'approve'));