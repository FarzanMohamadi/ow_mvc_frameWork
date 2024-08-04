<?php
$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMADVANCESEARCH_MCMP_UsersSearchWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'frmadvancesearch');
$widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);

OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmadvancesearch.search_users.ctrl', 'frmadvancesearch', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);



Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'frmadvancesearch');