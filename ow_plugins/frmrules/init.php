<?php
/**
 * FRM Rules
 */
OW::getRouter()->addRoute(new OW_Route('frmrules.admin', 'frmrules/admin', 'FRMRULES_CTRL_Admin', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmrules.admin.delete-item', 'frmrules/admin/delete-item/:id', 'FRMRULES_CTRL_Admin', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.delete-category', 'frmrules/admin/delete-category/:id', 'FRMRULES_CTRL_Admin', 'deleteCategory'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.ajax-save-items-order', 'frmrules/admin/ajax-save-items-order', 'FRMRULES_CTRL_Admin', 'ajaxSaveItemsOrder'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.section-id', 'frmrules/admin/:sectionId', 'FRMRULES_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.add-category', 'frmrules/admin/add-category/:sectionId', 'FRMRULES_CTRL_Admin', 'addCategory'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.add-item', 'frmrules/admin/add-item/:sectionId', 'FRMRULES_CTRL_Admin', 'addItem'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.edit-item', 'frmrules/admin/edit-item/:id', 'FRMRULES_CTRL_Admin', 'editItem'));
OW::getRouter()->addRoute(new OW_Route('frmrules.admin.edit-category', 'frmrules/admin/edit-category/:id', 'FRMRULES_CTRL_Admin', 'editCategory'));
OW::getRouter()->addRoute(new OW_Route('frmrules.index.section-id', 'rules/:sectionId', 'FRMRULES_CTRL_Rules', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmrules.index', 'rules', 'FRMRULES_CTRL_Rules', 'index'));
$eventHandler = new FRMRULES_CLASS_EventHandler();
$eventHandler->init();