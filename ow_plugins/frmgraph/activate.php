<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmgraph', 'frmgraph.admin');
$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMGRAPH_CMP_CountupWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 2 );

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMGRAPH_CMP_TopUsersWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 2 );
