<?php
$plugin = OW::getPluginManager()->getPlugin('groups');

//Admin Routs
OW::getRouter()->addRoute(new OW_Route('groups-admin-widget-panel', 'admin/plugins/groups', 'GROUPS_CTRL_Admin', 'panel'));
OW::getRouter()->addRoute(new OW_Route('groups-admin-additional-features', 'admin/plugins/groups/additional', 'GROUPS_CTRL_Admin', 'additional'));
OW::getRouter()->addRoute(new OW_Route('groups-admin-uninstall', 'admin/plugins/groups/uninstall', 'GROUPS_CTRL_Admin', 'uninstall'));

//Frontend Routs
OW::getRouter()->addRoute(new OW_Route('groups-create', 'groups/create', 'GROUPS_CTRL_Groups', 'create'));
OW::getRouter()->addRoute(new OW_Route('groups-edit', 'groups/:groupId/edit', 'GROUPS_CTRL_Groups', 'edit'));
OW::getRouter()->addRoute(new OW_Route('groups-view', 'groups/:groupId', 'GROUPS_CTRL_Groups', 'view'));
OW::getRouter()->addRoute(new OW_Route('groups-join', 'groups/:groupId/join', 'GROUPS_CTRL_Groups', 'join'));
OW::getRouter()->addRoute(new OW_Route('groups-customize', 'groups/:groupId/customize', 'GROUPS_CTRL_Groups', 'customize'));
OW::getRouter()->addRoute(new OW_Route('groups-most-popular', 'groups/most-popular', 'GROUPS_CTRL_Groups', 'mostPopularList'));
OW::getRouter()->addRoute(new OW_Route('groups-latest', 'groups/latest', 'GROUPS_CTRL_Groups', 'latestList'));
OW::getRouter()->addRoute(new OW_Route('groups-invite-list', 'groups/invitations', 'GROUPS_CTRL_Groups', 'inviteList'));
OW::getRouter()->addRoute(new OW_Route('groups-my-list', 'groups/my', 'GROUPS_CTRL_Groups', 'myGroupList'));
OW::getRouter()->addRoute(new OW_Route('groups-my-list-channels', 'channels/my', 'GROUPS_CTRL_Groups', 'myChannelList'));

OW::getRouter()->addRoute(new OW_Route('groups-index', 'groups', 'GROUPS_CTRL_Groups', 'index'));
OW::getRouter()->addRoute(new OW_Route('groups-user-groups', 'users/:user/groups', 'GROUPS_CTRL_Groups', 'userGroupList'));
OW::getRouter()->addRoute(new OW_Route('groups-leave', 'groups/:groupId/leave', 'GROUPS_CTRL_Groups', 'leave'));

OW::getRouter()->addRoute(new OW_Route('groups-user-list', 'groups/:groupId/users', 'GROUPS_CTRL_Groups', 'userList'));
//OW::getRouter()->addRoute(new OW_Route('groups-private-group', 'groups/:groupId/private', 'GROUPS_CTRL_Groups', 'privateGroup'));

OW::getRegistry()->addToArray(BASE_CMP_AddNewContent::REGISTRY_DATA_KEY,
    array(
        BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
        BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-create'),
        BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'add_new_label')
));

$eventHandler = GROUPS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

OW::getEventManager()->bind('forum.activate_plugin', array($eventHandler, "onForumActivate"));
OW::getEventManager()->bind('forum.find_forum_caption', array($eventHandler, "onForumFindCaption"));
OW::getEventManager()->bind('forum.uninstall_plugin', array($eventHandler, "onForumUninstall"));
OW::getEventManager()->bind('forum.collect_widget_places', array($eventHandler, "onForumCollectWidgetPlaces"));

OW::getEventManager()->bind('feed.collect_widgets', array($eventHandler, "onFeedCollectWidgets"));
OW::getEventManager()->bind('feed.on_widget_construct', array($eventHandler, "onFeedWidgetConstruct"));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRender"));

OW::getEventManager()->bind('admin.add_admin_notification', array($eventHandler, "onCollectAdminNotifications"));
OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($eventHandler, 'onCollectQuickLinks'));
OW::getEventManager()->bind("base.collect_seo_meta_data", array($eventHandler, 'onCollectMetaData'));

OW::getEventManager()->bind('frmadvancesearch.on_collect_search_items',  array($eventHandler, 'onCollectSearchItems'));
OW::getEventManager()->bind('newsfeed.update_status.form', array($eventHandler, 'newsfeedUpdateStatusFrom'));

GROUPS_CLASS_ConsoleBridge::getInstance()->init();
GROUPS_CLASS_ContentProvider::getInstance()->init();

if (!OW_PluginManager::getInstance()->isPluginActive('frmtelegram') && OW::getConfig()->configExists('groups', 'is_telegram_connected')) {
    OW::getConfig()->deleteConfig('groups', 'is_telegram_connected');
}
if (!OW_PluginManager::getInstance()->isPluginActive('frminstagram') && OW::getConfig()->configExists('groups', 'is_instagram_connected')) {
    OW::getConfig()->deleteConfig('groups', 'is_instagram_connected');
}
if (!OW_PluginManager::getInstance()->isPluginActive('frmgroupsplus') && OW::getConfig()->configExists('groups', 'isFRMGroupsPlusConnected')) {
    OW::getConfig()->deleteConfig('groups', 'isFRMGroupsPlusConnected');
}