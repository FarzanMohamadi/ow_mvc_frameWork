<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */
OW::getPluginManager()->addPluginSettingsRouteName('frmfilemanager', 'frmfilemanager.admin');

try {
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('FRMFILEMANAGER_CMP_MainWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 0);

    $widget = $widgetService->addWidget('FRMFILEMANAGER_CMP_MainWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 2);
} catch(Exception $e){}

