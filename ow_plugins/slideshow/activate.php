<?php 



$cmpService = BOL_ComponentAdminService::getInstance();
$widget = $cmpService->addWidget('SLIDESHOW_CMP_SlideshowWidget', true);
$cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
//$cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
//$cmpService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);