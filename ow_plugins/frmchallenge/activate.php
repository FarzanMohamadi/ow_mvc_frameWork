<?php
/**
 * FRM Challenge
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmchallenge', 'frmchallenge.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmchallenge.index', 'frmchallenge', 'main_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmchallenge.index', 'frmchallenge', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMCHALLENGE_CMP_ChallengeWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 1 );
