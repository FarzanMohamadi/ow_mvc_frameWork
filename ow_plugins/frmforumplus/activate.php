<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmforumplus','frmforumplus_admin_config');

$widget = array();

//--
$widget['site'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMFORUMPLUS_CMP_TopicGroupWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['site'], BOL_ComponentAdminService::PLACE_INDEX);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );