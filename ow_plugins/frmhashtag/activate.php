<?php
/**
 * frmhashtag
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmhashtag', 'frmhashtag.admin');
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmhashtag.page', 'frmhashtag', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmhashtag.page', 'frmhashtag', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);


//repopulate tags
OW::getConfig()->saveConfig('frmhashtag', 'should_be_repopulated', 'true', 'should_be_repopulated.');