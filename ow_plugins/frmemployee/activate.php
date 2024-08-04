<?php
/**
 * FRM Employee
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FRMEMPLOYEE_CMP_ProfileWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

OW::getPluginManager()->addPluginSettingsRouteName('frmemployee', 'frmemployee.admin');
