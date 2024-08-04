<?php
/**
 * frmeventplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmeventplus', 'frmeventplus.admin');

try {
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('FRMEVENTPLUS_CMP_FileListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'event');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
} catch(Exception $e){}