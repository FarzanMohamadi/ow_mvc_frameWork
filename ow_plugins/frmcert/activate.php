<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcert
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FRMCERT_CMP_Widget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

