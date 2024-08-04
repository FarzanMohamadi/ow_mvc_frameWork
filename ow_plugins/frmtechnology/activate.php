<?php
//OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmtechnology.index', 'frmtechnology', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
//OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmtechnology.index', 'frmtechnology', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

OW::getPluginManager()->addPluginSettingsRouteName('frmtechnology','frmtechnology.admin-config');

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FRMTECHNOLOGY_CMP_ServicesEnterprise', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('FRMTECHNOLOGY_CMP_ServicesUniMembers', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('FRMTECHNOLOGY_CMP_ContactUs', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

