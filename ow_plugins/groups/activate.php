<?php
OW::getPluginManager()->addPluginSettingsRouteName('groups', 'groups-admin-widget-panel');
OW::getPluginManager()->addUninstallRouteName('groups', 'groups-admin-uninstall');

$navigation = OW::getNavigation();

$navigation->addMenuItem(
    OW_Navigation::MAIN,
    'groups-index',
    'groups',
    'main_menu_list',
    OW_Navigation::VISIBLE_FOR_ALL);


OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'groups-index', 'groups', 'mobile_main_menu_list', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('GROUPS_CMP_UserGroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

/*$widget = $widgetService->addWidget('GROUPS_CMP_UserGroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT);*/

$widget = $widgetService->addWidget('GROUPS_CMP_GroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

$event = new OW_Event('feed.install_widget', array(
    'place' => 'group',
    'section' => BOL_ComponentService::SECTION_RIGHT,
    'order' => 0
));

OW::getEventManager()->trigger($event);

if ( OW::getConfig()->getValue('groups', 'is_forum_connected') )
{
    $event = new OW_Event('forum.install_widget', array(
        'place' => 'group',
        'section' => BOL_ComponentService::SECTION_RIGHT,
        'order' => 0
    ));
    OW::getEventManager()->trigger($event);

    if ( !OW::getConfig()->configExists('groups', 'restore_groups_forum') )
    {
        OW::getConfig()->saveConfig('groups', 'restore_groups_forum', 1);
    }

}

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('groups', 'groups_sitemap', 'groups', array(
    'groups_list',
    'groups',
    'groups_user_list',
    'groups_authors'
), 'groups_sitemap_desc');
