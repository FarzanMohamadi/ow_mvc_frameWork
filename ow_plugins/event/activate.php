<?php
OW::getPluginManager()->addPluginSettingsRouteName('event', 'event.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'event.main_menu_route', 'event', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'event.main_menu_route', 'event', 'event_mobile', OW_Navigation::VISIBLE_FOR_ALL);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('EVENT_CMP_EventDetails', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, 'event');
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('EVENT_CMP_UpcomingEvents', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('EVENT_CMP_ProfilePageWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('EVENT_CMP_EventUsers', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, 'event');
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('event', 'event_sitemap', 'event', array(
    'event_list',
    'event',
    'event_participants'
));
