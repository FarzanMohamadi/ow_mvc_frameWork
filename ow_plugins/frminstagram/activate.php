<?php
/**
 * frminstagram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */



OW::getPluginManager()->addPluginSettingsRouteName('frminstagram', 'frminstagram.admin');

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMINSTAGRAM_CMP_FeedWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 2 );

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

$widgetPlace = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

if ( OW::getConfig()->configExists('groups', 'is_instagram_connected') && OW::getConfig()->getValue('groups', 'is_instagram_connected') ) {
    try {
        $widgetService = BOL_ComponentAdminService::getInstance();
        $widget = $widgetService->addWidget('FRMINSTAGRAM_CMP_FeedWidget', false);
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