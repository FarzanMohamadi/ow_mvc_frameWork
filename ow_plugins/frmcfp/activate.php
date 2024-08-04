<?php
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmcfp.main_menu_route', 'frmcfp', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmcfp.main_menu_route', 'frmcfp', 'event_mobile', OW_Navigation::VISIBLE_FOR_ALL);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMCFP_CMP_UpcomingEvents', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMCFP_CMP_ProfilePageWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('frmcfp', 'event_sitemap', 'frmcfp', array(
    'event_list',
    'frmcfp',
    'event_participants'
));
