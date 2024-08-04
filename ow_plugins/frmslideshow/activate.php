<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmslideshow', 'frmslideshow.admin');

$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    if(OW::getPluginManager()->isPluginActive('frmnews')) {
        $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_NewsWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
        $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
    }
    if(OW::getPluginManager()->isPluginActive('forum')) {
        $widget = $widgetService->addWidget('FRMSLIDESHOW_MCMP_ForumWidget', false);
        $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
        $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
    }


    $arr = OW::getDbo()->queryForList('SELECT * FROM `' . OW_DB_PREFIX . 'frmslideshow_album`');
    if(count($arr)>0) {
        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $service->createAllExtraWidgets();
    }
}
catch ( Exception $e )
{
    OW::getLogger()->addEntry(json_encode($e));
}
