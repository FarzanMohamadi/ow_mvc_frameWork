<?php
/**
 * frmmobileaccount
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobileaccount
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmmobileaccount', 'frmmobileaccount.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmmobileaccount.login', 'frmmobileaccount', 'mobile_menu_item', OW_Navigation::VISIBLE_FOR_GUEST);
