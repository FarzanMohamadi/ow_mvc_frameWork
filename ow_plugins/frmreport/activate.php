<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmreport', 'frmreport.admin');

try {
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('FRMREPORT_CMP_ReportsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
}catch(Exception $e){}
