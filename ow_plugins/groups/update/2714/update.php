<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'groups');

$widgetService = Updater::getWidgetService();

$widget = $widgetService->addWidget('GROUPS_CMP_LeaveButtonWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');

try 
{
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
}
catch ( LogicException $e ) {}

