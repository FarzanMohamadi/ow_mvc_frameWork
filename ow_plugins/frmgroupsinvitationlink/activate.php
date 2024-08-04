<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmgroupsinvitationlink', 'frmgroupsinvitationlink-admin');

$widgetService = BOL_ComponentAdminService::getInstance();
try {
    $widget = $widgetService->addWidget('FRMGROUPSINVITATIONLINK_CMP_InvitationLinkWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
} catch(Exception $e){}
