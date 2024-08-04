<?php
try {
    $widgetService = BOL_ComponentAdminService::getInstance();

    $widget = $widgetService->addWidget('FRMFILEMANAGER_CMP_MainWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 2);
} catch(Exception $e){}