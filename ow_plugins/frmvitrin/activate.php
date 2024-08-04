<?php
/**
 * frmvitrin
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvitrin
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmvitrin', 'frmvitrin.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmvitrin.index', 'frmvitrin', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmvitrin.index', 'frmvitrin', 'main_menu_mobile_item', OW_Navigation::VISIBLE_FOR_ALL);