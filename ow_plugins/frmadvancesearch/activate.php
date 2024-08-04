<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FRMADVANCESEARCH_MCMP_UsersSearchWidget', true);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);

$placeWidget = $widgetService->addWidgetToPlace($widget, 'frmadvancesearch');
$widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);


// To add friends search widget: remove  && false in below and also in desktop event listener
if(OW::getPluginManager()->isPluginActive('friends') && false) {
    $widget = $widgetService->addWidget('FRMADVANCESEARCH_MCMP_FriendsSearchWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
}

OW::getPluginManager()->addPluginSettingsRouteName('frmadvancesearch', 'frmadvancesearch.admin');

//---mobile
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmadvancesearch.search_users.ctrl', 'frmadvancesearch', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
