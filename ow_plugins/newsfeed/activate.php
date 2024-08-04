<?php
OW::getPluginManager()->addPluginSettingsRouteName('newsfeed', 'newsfeed_admin_settings');



$widgetService = BOL_ComponentAdminService::getInstance();
$mobileWidgetService = BOL_MobileWidgetService::getInstance();

$widget = $widgetService->addWidget('NEWSFEED_CMP_MyFeedWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);

$widget = $widgetService->addWidget('NEWSFEED_CMP_UserFeedWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT, 0);

$widget = $widgetService->addWidget('NEWSFEED_CMP_SiteFeedWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT, 0);

$widget = $mobileWidgetService->addWidget("NEWSFEED_MCMP_MyFeedWidget", false);
$place = $mobileWidgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
$mobileWidgetService->addWidgetToPosition($place, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);

$event = new BASE_CLASS_EventCollector('feed.collect_widgets');
OW::getEventManager()->trigger($event);

foreach( $event->getData() as $widgetInfo )
{
    try
    {
        $widget = $widgetService->addWidget('NEWSFEED_CMP_EntityFeedWidget', false);
        $widgetPlace = $widgetService->addWidgetToPlace($widget, $widgetInfo['place']);
        $widgetService->addWidgetToPosition($widgetPlace, $widgetInfo['section'], $widgetInfo['order']);
    }
    catch ( Exception $e )
    {

    }
}


// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'newsfeed_view_feed', 'newsfeed', 'newsfeed_feed', OW_Navigation::VISIBLE_FOR_ALL);