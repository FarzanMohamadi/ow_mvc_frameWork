<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmnews', 'frmnews-admin');
OW::getPluginManager()->addUninstallRouteName('frmnews', 'frmnews-uninstall');

$widget = array();

//--
$widget['dashboard'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMNEWS_CMP_NewsWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['dashboard'], BOL_ComponentAdminService::PLACE_DASHBOARD);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

//--
$widget['site'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMNEWS_CMP_NewsWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['site'], BOL_ComponentAdminService::PLACE_INDEX);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

//--
$widget['tags'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMNEWS_CMP_TagsWidget', true);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['tags'], BOL_ComponentAdminService::PLACE_INDEX);

//BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT );

//--
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmnews', 'frmnews', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmnews-default', 'frmnews', 'frmnews_mobile', OW_Navigation::VISIBLE_FOR_ALL);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('frmnews', 'frmnews_sitemap', 'frmnews', array(
    'news'
));