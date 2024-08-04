<?php
/**
 * frmcompetition
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcompetition
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmcompetition', 'frmcompetition.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmcompetition.index', 'frmcompetition', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmcompetition.index', 'frmcompetition', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
