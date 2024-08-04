<?php
/**
 * 
 * All rights reserved.
 */
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin', 'frmupdateserver/admin', 'FRMUPDATESERVER_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.add.item', 'frmupdateserver/admin/add-item', 'FRMUPDATESERVER_CTRL_Admin', 'addItem'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.items', 'frmupdateserver/admin/items/:type', 'FRMUPDATESERVER_CTRL_Admin', 'items'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.delete.item', 'frmupdateserver/admin/delete/item/:id', 'FRMUPDATESERVER_CTRL_Admin', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.ajax.save.items.order', 'frmupdateserver/admin/ajax-save-item-order', 'FRMUPDATESERVER_CTRL_Admin', 'ajaxSaveItemsOrder'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.edit.item', 'frmupdateserver/admin/edit/item/:id', 'FRMUPDATESERVER_CTRL_Admin', 'editItem'));
OW::getRouter()->addRoute(new OW_Route('server', 'server', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'index'));
OW::getRouter()->addRoute(new OW_Route('server.get_item_info', 'server/get-item-info', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'getItemInfo'));
OW::getRouter()->addRoute(new OW_Route('server.get_ignore_themes', 'server/get-ignore-themes', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'getIgnoreThemes'));
OW::getRouter()->addRoute(new OW_Route('server.get_item', 'server/get-item', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'getItem'));
OW::getRouter()->addRoute(new OW_Route('server.platform_info', 'server/platform-info', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'platformInfo'));
OW::getRouter()->addRoute(new OW_Route('server.download_platform', 'server/download-platform', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'downloadUpdatePlatform'));
OW::getRouter()->addRoute(new OW_Route('server.download_full_platform', 'server/download-full-platform', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'downloadFullPlatform'));
OW::getRouter()->addRoute(new OW_Route('server.get_items_update_info', 'server/get-items-update-info', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'getItemsUpdateInfo'));
OW::getRouter()->addRoute(new OW_Route('server.update_static_files', 'server/update-static-files', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'updateStaticFiles'));
OW::getRouter()->addRoute(new OW_Route('server.check_all_for_update', 'server/check-all-for-update', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'checkAllForUpdate'));
OW::getRouter()->addRoute(new OW_Route('server.delete_all_versions', 'server/delete-all-versions', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'deleteAllVersions'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.index', 'download', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'viewDownloadPage'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.data-post-url', 'data-post-url', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'getDataPostUrl'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.download-file', 'download-file', 'FRMUPDATESERVER_CTRL_Iisupdateserver', 'downloadFile'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.delete.by.name.and.version', 'frmupdateserver/admin/delete-item', 'FRMUPDATESERVER_CTRL_Admin', 'deleteItemByNameAndBuildNumber'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.check.update.by.name', 'frmupdateserver/admin/check-item', 'FRMUPDATESERVER_CTRL_Admin', 'checkUpdateItemAvailableByName'));
OW::getRouter()->addRoute(new OW_Route('frmupdateserver.admin.categories', 'frmupdateserver/admin/itemCategories', 'FRMUPDATESERVER_CTRL_Admin', 'itemCategory'));
$eventHandler = new FRMUPDATESERVER_CLASS_EventHandler();
$eventHandler->init();
