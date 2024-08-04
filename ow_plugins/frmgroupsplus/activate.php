<?php
/**
 * frmgroupsplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmgroupsplus', 'frmgroupsplus.admin');

try {
    $widgetService = BOL_ComponentAdminService::getInstance();
    $widget = $widgetService->addWidget('FRMGROUPSPLUS_CMP_FileListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
} catch(Exception $e){}

if ( OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected') ) {
    try {
        $widgetService = BOL_ComponentAdminService::getInstance();
        $widget = $widgetService->addWidget('FRMGROUPSPLUS_CMP_PendingInvitation', false);
        $widgetUniqID = 'group' . '-' . $widget->className;

        //*remove if exists
        $widgets = $widgetService->findPlaceComponentList('group');
        foreach ($widgets as $w) {
            if ($w['uniqName'] == $widgetUniqID)
                $widgetService->deleteWidgetPlace($widgetUniqID);
        }
        //----------*/

        //add
        $placeWidget = $widgetService->addWidgetToPlace($widget, 'group', $widgetUniqID);
        $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, -1);
    } catch (Exception $e) {
    }
}