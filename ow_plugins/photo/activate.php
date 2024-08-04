<?php
OW::getPluginManager()->addPluginSettingsRouteName('photo', 'photo_admin_config');
OW::getPluginManager()->addUninstallRouteName('photo', 'photo_uninstall');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'view_photo_list', 'photo', 'photo', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'photo_list_index', 'photo', 'mobile_photo', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_UserPhotoAlbumsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('PHOTO_MCMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('photo', 'photo_sitemap', 'photos', array(
    'photo_list',
    'photos',
    'photos_latest',
    'photos_toprated',
    'photos_most_discussed',
    'photo_albums',
    'photo_tags',
    'photo_user_albums',
    'photo_users'
));
