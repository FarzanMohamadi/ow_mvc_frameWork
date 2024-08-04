<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmshasta', 'frmshasta_admin');

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMSHASTA_CMP_CategoriesWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 2 );

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMSHASTA_CMP_MyFilesWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 2 );

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMSHASTA_CMP_CompaniesWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 2 );
