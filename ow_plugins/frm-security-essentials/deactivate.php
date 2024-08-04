<?php
/**
 * User: Hamed Tahmooresi
 * Date: 12/23/2015
 * Time: 11:00 AM
 */

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('BASE_CMP_ProfileWallWidget');
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);