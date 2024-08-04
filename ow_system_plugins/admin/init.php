<?php
$plugin = OW::getPluginManager()->getPlugin('admin');

$ROUTE_PREFIX = defined('OW_ADMIN_PREFIX')?OW_ADMIN_PREFIX:'admin';

OW::getRouter()->addRoute(new OW_Route('admin_default', $ROUTE_PREFIX, 'ADMIN_CTRL_Base', 'dashboard'));

OW::getRouter()->addRoute(new OW_Route('admin_dashboard', $ROUTE_PREFIX, 'ADMIN_CTRL_Base', 'dashboard'));
OW::getRouter()->addRoute(new OW_Route('admin_dashboard_customize', $ROUTE_PREFIX.'/dashboard/customize', 'ADMIN_CTRL_Base', 'dashboard', array(
    'mode' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
))));

OW::getRouter()->addRoute(new OW_Route('admin_settings_language', $ROUTE_PREFIX.'/settings/languages', 'ADMIN_CTRL_Languages', 'index'));

OW::getRouter()->addRoute(new OW_Route('admin_settings_language_mod', $ROUTE_PREFIX.'/settings/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

OW::getRouter()->addRoute(new OW_Route('admin_developer_tools_language', $ROUTE_PREFIX.'/settings/dev-tools/languages', 'ADMIN_CTRL_Languages', 'index'));
OW::getRouter()->addRoute(new OW_Route('admin_developer_tools_language_mod', $ROUTE_PREFIX.'/settings/dev-tools/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

OW::getAutoloader()->addClass('ColorField', $plugin->getClassesDir() . 'form_fields.php');
OW::getAutoloader()->addClass('ADMIN_UserListParams', $plugin->getCmpDir() . 'user_list.php');

$router = OW::getRouter();

$router->addRoute(new OW_Route('base.sitemap_generate', $ROUTE_PREFIX.'/generate-sitemap', 'ADMIN_CTRL_Base', 'generateSitemap'));

$router->addRoute(new OW_Route('admin_permissions_moderators', $ROUTE_PREFIX.'/users/moderators', 'ADMIN_CTRL_Permissions', 'moderators'));
$router->addRoute(new OW_Route('admin_user_roles', $ROUTE_PREFIX.'/users/roles', 'ADMIN_CTRL_Users', 'roles'));
$router->addRoute(new OW_Route('admin_users_browse_membership_owners', $ROUTE_PREFIX.'/users/role/:roleId', 'ADMIN_CTRL_Users', 'role'));

$router->addRoute(new OW_Route('questions_index', $ROUTE_PREFIX.'/users/profile-questions', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new OW_Route('questions_account_types', $ROUTE_PREFIX.'/users/profile-questions', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new OW_Route('questions_properties', $ROUTE_PREFIX.'/users/profile-questions/pages', 'ADMIN_CTRL_Questions', 'pages'));

$router->addRoute(new OW_Route('admin_themes_edit', $ROUTE_PREFIX.'/appearance/customize', 'ADMIN_CTRL_Theme', 'settings'));
$router->addRoute(new OW_Route('admin_themes_choose', $ROUTE_PREFIX.'/appearance', 'ADMIN_CTRL_Themes', 'chooseTheme'));

$router->addRoute(new OW_Route('admin_pages_edit_external', $ROUTE_PREFIX.'/pages/edit-external/id/:id', 'ADMIN_CTRL_PagesEditExternal', 'index'));
$router->addRoute(new OW_Route('admin_pages_edit_local', $ROUTE_PREFIX.'/pages/edit-local/id/:id', 'ADMIN_CTRL_PagesEditLocal', 'index'));
$router->addRoute(new OW_Route('admin_pages_edit_plugin', $ROUTE_PREFIX.'/pages/edit-plugin/id/:id', 'ADMIN_CTRL_PagesEditPlugin', 'index'));

$router->addRoute(new OW_Route('admin_pages_add', $ROUTE_PREFIX.'/pages/add/type/:type', 'ADMIN_CTRL_Pages', 'index'));
$router->addRoute(new OW_Route('admin_pages_main', $ROUTE_PREFIX.'/pages', 'ADMIN_CTRL_Pages', 'manage'));
$router->addRoute(new OW_Route('admin_pages_maintenance', $ROUTE_PREFIX.'/pages/special-pages', 'ADMIN_CTRL_Pages', 'maintenance'));

$router->addRoute(new OW_Route('admin_pages_user_dashboard', $ROUTE_PREFIX.'/pages/user-dashboard', 'ADMIN_CTRL_Components', 'dashboard'));
$router->addRoute(new OW_Route('admin_pages_user_profile', $ROUTE_PREFIX.'/pages/user-profile', 'ADMIN_CTRL_Components', 'profile'));

$router->addRoute(new OW_Route('admin_pages_user_settings', $ROUTE_PREFIX.'/user-settings', 'ADMIN_CTRL_UserSettings', 'index'));

$router->addRoute(new OW_Route('admin_plugins_installed', $ROUTE_PREFIX.'/plugins', 'ADMIN_CTRL_Plugins', 'index'));
$router->addRoute(new OW_Route('admin_plugins_available', $ROUTE_PREFIX.'/plugins/available', 'ADMIN_CTRL_Plugins', 'available'));
$router->addRoute(new OW_Route('admin_plugins_add', $ROUTE_PREFIX.'/plugins/add-new', 'ADMIN_CTRL_Plugins', 'add'));

$router->addRoute(new OW_Route('admin_delete_roles', $ROUTE_PREFIX.'/users/delete-roles', 'ADMIN_CTRL_Users', 'deleteRoles'));
$router->addRoute(new OW_Route('admin.roles.reorder', $ROUTE_PREFIX.'/users/ajax-reorder', 'ADMIN_CTRL_Users', 'ajaxReorder'));
$router->addRoute(new OW_Route('admin.roles.edit-role', $ROUTE_PREFIX.'/users/ajax-edit-role', 'ADMIN_CTRL_Users', 'ajaxEditRole'));
$router->addRoute(new OW_Route('admin.add.user', $ROUTE_PREFIX.'/users/add-user-responder', 'ADMIN_CTRL_Users', 'addUserResponder'));

$router->addRoute(new OW_Route('admin_users_browse', $ROUTE_PREFIX.'/users/:list', 'ADMIN_CTRL_Users', 'index', array('list' => array('default' => ''))));

$router->addRoute(new OW_Route('admin_settings_main', $ROUTE_PREFIX.'/settings', 'ADMIN_CTRL_Settings', 'index'));
$router->addRoute(new OW_Route('admin_settings_user', $ROUTE_PREFIX.'/settings/user', 'ADMIN_CTRL_Settings', 'user'));
$router->addRoute(new OW_Route('admin_settings_mail', $ROUTE_PREFIX.'/settings/smtp', 'ADMIN_CTRL_Settings', 'mail'));
$router->addRoute(new OW_Route('admin_settings_page', $ROUTE_PREFIX.'/settings/page', 'ADMIN_CTRL_Settings', 'page'));
$router->addRoute(new OW_Route('admin_settings_user_input', $ROUTE_PREFIX.'/settings/content', 'ADMIN_CTRL_Settings', 'userInput'));
$router->addRoute(new OW_Route('admin_settings_log', $ROUTE_PREFIX.'/settings/log', 'ADMIN_CTRL_Settings', 'log'));
$router->addRoute(new OW_Route('admin_settings_seo', $ROUTE_PREFIX.'/settings/seo', 'ADMIN_CTRL_Seo', 'index'));
$router->addRoute(new OW_Route('admin_settings_seo_sitemap', $ROUTE_PREFIX.'/settings/seo/sitemap', 'ADMIN_CTRL_Seo', 'sitemap'));
$router->addRoute(new OW_Route('admin_settings_seo_social_meta', $ROUTE_PREFIX.'/settings/seo/social-meta', 'ADMIN_CTRL_Seo', 'socialMeta'));

$router->addRoute(new OW_Route('admin_massmailing', $ROUTE_PREFIX.'/users/mass-mailing', 'ADMIN_CTRL_MassMailing', 'index'));
$router->addRoute(new OW_Route('admin_restrictedusernames', $ROUTE_PREFIX.'/users/restricted-usernames', 'ADMIN_CTRL_RestrictedUsernames', 'index'));

$router->addRoute(new OW_Route('admin_theme_css', $ROUTE_PREFIX.'/appearance/customize/css', 'ADMIN_CTRL_Theme', 'css'));
$router->addRoute(new OW_Route('admin_theme_settings', $ROUTE_PREFIX.'/appearance/customize', 'ADMIN_CTRL_Theme', 'settings'));
$router->addRoute(new OW_Route('admin_theme_graphics', $ROUTE_PREFIX.'/appearance/customize/graphics', 'ADMIN_CTRL_Theme', 'graphics'));
$router->addRoute(new OW_Route('admin_core_update_request', $ROUTE_PREFIX.'/platform-update', 'ADMIN_CTRL_Storage', 'platformUpdateRequest'));
$router->addRoute(new OW_Route('admin_core_update_request_manually', $ROUTE_PREFIX.'/platform-update-manually', 'ADMIN_CTRL_Storage', 'platformUpdateRequestManually'));

$router->addRoute(new OW_Route('admin_theme_graphics_bulk_options', $ROUTE_PREFIX.'/theme/graphics/bulk-options', 'ADMIN_CTRL_Theme', 'bulkOptions'));

$router->addRoute(new OW_Route('admin.ajax_upload', $ROUTE_PREFIX.'/ajax-upload', 'ADMIN_CTRL_AjaxUpload', 'upload'));
$router->addRoute(new OW_Route('admin.ajax_upload_delete', $ROUTE_PREFIX.'/ajax-upload-delete', 'ADMIN_CTRL_AjaxUpload', 'delete'));
$router->addRoute(new OW_Route('admin.ajax_upload_submit', $ROUTE_PREFIX.'/ajax-upload-submit', 'ADMIN_CTRL_AjaxUpload', 'ajaxSubmitPhotos'));
$router->addRoute(new OW_Route('admin.ajax_responder', $ROUTE_PREFIX.'/ajax-responder', 'ADMIN_CTRL_Theme', 'ajaxResponder'));
$router->addRoute(new OW_Route('admin.bulk_plugins_manual_update', $ROUTE_PREFIX.'/plugins/manual-update-all', 'ADMIN_CTRL_Plugins', 'manualUpdateAll'));

// Mobile
$router->addRoute(new OW_Route('mobile.admin.navigation', $ROUTE_PREFIX.'/mobile', 'ADMIN_CTRL_MobileNavigation', 'index'));

$router->addRoute(new OW_Route('mobile.admin.pages.index', $ROUTE_PREFIX.'/mobile/pages/index', 'ADMIN_CTRL_MobileWidgetPanel', 'index'));
$router->addRoute(new OW_Route('mobile.admin.pages.dashboard', $ROUTE_PREFIX.'/mobile/pages/dashboard', 'ADMIN_CTRL_MobileWidgetPanel', 'dashboard'));
$router->addRoute(new OW_Route('mobile.admin.pages.profile', $ROUTE_PREFIX.'/mobile/pages/profile', 'ADMIN_CTRL_MobileWidgetPanel', 'profile'));
$router->addRoute(new OW_Route('mobile.admin_settings', $ROUTE_PREFIX.'/mobile/settings', 'ADMIN_CTRL_MobileSettings', 'index'));

function admin_on_application_finalize( OW_Event $event )
{
    OW::getLanguage()->addKeyForJs('admin', 'edit_language');
}
OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, 'admin_on_application_finalize');

function admin_add_auth_labels( BASE_CLASS_EventCollector $event )
{
    $language = OW::getLanguage();
    $event->add(
        array(
            'admin' => array(
                'label' => $language->text('admin', 'auth_group_label'),
                'actions' => array()
            )
        )
    );
}
OW::getEventManager()->bind('admin.add_auth_labels', 'admin_add_auth_labels');

$handler = new ADMIN_CLASS_EventHandler();
$handler->init();
