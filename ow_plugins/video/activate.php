<?php
OW::getPluginManager()->addPluginSettingsRouteName('video', 'video_admin_config');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'video_list_index', 'video', 'video', OW_Navigation::VISIBLE_FOR_ALL);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'video_list_index', 'video', 'video_mobile', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('VIDEO_CMP_VideoListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

$widget = $widgetService->addWidget('VIDEO_CMP_UserVideoListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('video', 'video_sitemap', 'video', array(
    'video_list',
    'video_tags',
    'video',
    'video_authors'
));
