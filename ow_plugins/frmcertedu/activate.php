<?php
/**
 * FRM Terms
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcertedu
 * @since 1.0
 */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FRMCERTEDU_CMP_Courses', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('FRMCERTEDU_CMP_Countdown', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('FRMCERTEDU_CMP_News', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);

$widget = $widgetService->addWidget('FRMCERTEDU_CMP_Faq', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

$widget = $widgetService->addWidget('FRMCERTEDU_CMP_ContactUs', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_BOTTOM);