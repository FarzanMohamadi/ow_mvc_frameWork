<?php
$widgetService = Updater::getMobileWidgeteService();

try
{
    $widget = $widgetService->addWidget('PHOTO_MCMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}
