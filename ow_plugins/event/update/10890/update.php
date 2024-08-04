<?php

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('EVENT_CMP_EventUsers', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'event');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);