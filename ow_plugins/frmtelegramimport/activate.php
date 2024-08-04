<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmtelegramimport', 'frmtelegramimport.upload');

try {
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('FRMTELEGRAMIMPORT_CMP_TelegramWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
}catch(Exception $e){}

