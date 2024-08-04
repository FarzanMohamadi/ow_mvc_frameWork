<?php
/**
 * FRM Terms
 */

OW::getRouter()->addRoute(new OW_Route('frmterms.admin.delete-item', 'frmterms/admin/delete-item/:id', 'FRMTERMS_CTRL_Admin', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.deactivate-item', 'frmterms/admin/deactivate-item/:id', 'FRMTERMS_CTRL_Admin', 'deactivateItem'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.activate-item', 'frmterms/admin/activate-item/:id', 'FRMTERMS_CTRL_Admin', 'activateItem'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.deactivate-section', 'frmterms/admin/deactivate-section/:sectionId', 'FRMTERMS_CTRL_Admin', 'deactivateSection'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.activate-section', 'frmterms/admin/activate-section/:sectionId', 'FRMTERMS_CTRL_Admin', 'activateSection'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.ajax-save-order', 'frmterms/admin/ajax-save-order', 'FRMTERMS_CTRL_Admin', 'ajaxSaveOrder'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.section-id', 'frmterms/admin/:sectionId', 'FRMTERMS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.add-version', 'frmterms/admin/add-version/:sectionId', 'FRMTERMS_CTRL_Admin', 'addVersion'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.delete-version', 'frmterms/admin/delete-version/:sectionId/:version', 'FRMTERMS_CTRL_Admin', 'deleteVersion'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.add.item', 'frmterms/admin/add-item', 'FRMTERMS_CTRL_Admin', 'addItem'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.edit.item', 'frmterms/admin/edit-item', 'FRMTERMS_CTRL_Admin', 'editItem'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin', 'frmterms/admin', 'FRMTERMS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.activate-terms-on-join', 'frmterms/admin/activate-terms-on-join/:sectionId', 'FRMTERMS_CTRL_Admin', 'activateTermsOnJoin'));
OW::getRouter()->addRoute(new OW_Route('frmterms.admin.deactivate-terms-on-join', 'frmterms/admin/deactivate-terms-on-join/:sectionId', 'FRMTERMS_CTRL_Admin', 'deactivateTermsOnJoin'));

OW::getRouter()->addRoute(new OW_Route('frmterms.view-archives', 'terms/view-archives/:sectionId', 'FRMTERMS_CTRL_Terms', 'viewArchives'));
OW::getRouter()->addRoute(new OW_Route('frmterms.comparison-archive', 'terms/comparison-archive/:sectionId/:version', 'FRMTERMS_CTRL_Terms', 'comparisonArchive'));
OW::getRouter()->addRoute(new OW_Route('frmterms.index', 'terms', 'FRMTERMS_CTRL_Terms', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.index.section-id', 'terms/:sectionId', 'FRMTERMS_CTRL_Terms', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmterms.old.view-archives', 'frmterms/view-archives/:sectionId', 'FRMTERMS_CTRL_Terms', 'viewArchives'));
OW::getRouter()->addRoute(new OW_Route('frmterms.old.comparison-archive', 'frmterms/comparison-archive/:sectionId/:version', 'FRMTERMS_CTRL_Terms', 'comparisonArchive'));
OW::getRouter()->addRoute(new OW_Route('frmterms.old.index', 'frmterms', 'FRMTERMS_CTRL_Terms', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.old.index.section-id', 'frmterms/:sectionId', 'FRMTERMS_CTRL_Terms', 'index'));

FRMTERMS_BOL_Service::getInstance()->importingDefaultItems();
FRMTERMS_CLASS_EventHandler::getInstance()->genericInit();