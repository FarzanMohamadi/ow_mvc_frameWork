<?php
/**
 * FRM Reveal
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmreveal
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMSUBGROUPS_CMP_SubgroupListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);


$widget = $widgetService->addWidget('FRMSUBGROUPS_CMP_GroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);