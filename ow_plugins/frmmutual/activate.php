<?php
/**
 * frmmutual
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmutual
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmmutual', 'frmmutual.admin');

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMMUTUAL_CMP_UserIisMutualWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 0 );
