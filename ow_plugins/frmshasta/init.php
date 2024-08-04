<?php
OW::getRouter()->addRoute(new OW_Route('frmshasta_admin', 'admin/frmshasta/index', "FRMSHASTA_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_files', 'all_files', 'FRMSHASTA_CTRL_Service', 'files'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_add_file', 'file/add', 'FRMSHASTA_CTRL_Service', 'addFile'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_edit_file', 'file/edit/:id', 'FRMSHASTA_CTRL_Service', 'addFile'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_add_company', 'company/add', 'FRMSHASTA_CTRL_Service', 'addCompany'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_edit_company', 'company/edit/:id', 'FRMSHASTA_CTRL_Service', 'addCompany'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_delete_company', 'company/delete/:id', 'FRMSHASTA_CTRL_Service', 'deleteCompany'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_delete_file', 'file/delete/:id', 'FRMSHASTA_CTRL_Service', 'deleteFile'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_add_category', 'category/add', 'FRMSHASTA_CTRL_Service', 'addCategory'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_edit_category', 'category/edit/:id', 'FRMSHASTA_CTRL_Service', 'addCategory'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_delete_category', 'category/delete/:id', 'FRMSHASTA_CTRL_Service', 'deleteCategory'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_file', 'file/:id', 'FRMSHASTA_CTRL_Service', 'file'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_customize_categories', 'customize/categories', 'FRMSHASTA_CTRL_Service', 'customizeCategories'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_customize_special_categories', 'customize/special/categories', 'FRMSHASTA_CTRL_Service', 'customizeSpecialCategories'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_view_all_my_files', 'my/all', 'FRMSHASTA_CTRL_Service', 'allMyFiles'));
OW::getRouter()->addRoute(new OW_Route('frmshasta_reports', 'reports', 'FRMSHASTA_CTRL_Service', 'reports'));

OW::getRouter()->addRoute(new OW_Route('frmshasta_download_file', 'file/download/:id', 'FRMSHASTA_CTRL_Service', 'downloadFile'));

FRMSHASTA_CLASS_EventHandler::getInstance()->init();
