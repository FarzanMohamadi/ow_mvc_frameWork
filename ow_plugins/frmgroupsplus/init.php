<?php
/**
 * frmgroupsplus
 */
$plugin = OW::getPluginManager()->getPlugin('frmgroupsplus');
FRMGROUPSPLUS_CLASS_EventHandler::getInstance()->init();
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmgroupsplus.admin', 'admin/plugins/frmgroupsplus', "FRMGROUPSPLUS_CTRL_Admin", 'groupCategory'));
$router->addRoute(new OW_Route('frmgroupsplus.admin.edit.item', 'frmgroupsplus/admin/edit-item', 'FRMGROUPSPLUS_CTRL_Admin', 'editItem'));
$router->addRoute(new OW_Route('frmgroupsplus.file-list', 'groups/:groupId/files', 'FRMGROUPSPLUS_CTRL_Groups', 'fileList'));
$router->addRoute(new OW_Route('frmgroupsplus.addFile', 'groups/:groupId/addFile', 'FRMGROUPSPLUS_CTRL_Groups', 'addFile'));
$router->addRoute(new OW_Route('frmgroupsplus.deleteFile', 'groups/:groupId/attachmentId/:attachmentId/deleteFile', 'FRMGROUPSPLUS_CTRL_Groups', 'deleteFile'));
$router->addRoute(new OW_Route('frmgroupsplus.group-approve', 'groups/:groupId/approve', 'FRMGROUPSPLUS_CTRL_Groups', 'approve'));
$router->addRoute(new OW_Route('frmgroupsplus.forced-groups', 'forced-groups', 'FRMGROUPSPLUS_CTRL_ForcedGroups', 'index'));
$router->addRoute(new OW_Route('frmgroupsplus.forced-group-edit', 'forced-group/:id/edit', 'FRMGROUPSPLUS_CTRL_ForcedGroups', 'edit'));
$router->addRoute(new OW_Route('frmgroupsplus.forced-group-delete', 'forced-group/delete', 'FRMGROUPSPLUS_CTRL_ForcedGroups', 'deleteItem'));
