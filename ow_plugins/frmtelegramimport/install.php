<?php
BOL_LanguageService::getInstance()->addPrefix('frmtelegramimport','Import telegram channel data');

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMTELEGRAMIMPORT_CMP_TelegramWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$authorization = OW::getAuthorization();
$groupName = 'frmtelegramimport';
$authorization->addGroup($groupName);
