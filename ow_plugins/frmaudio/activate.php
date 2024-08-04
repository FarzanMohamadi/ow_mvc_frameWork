<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmaudio', 'frmaudio-admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmaudio-audio', 'frmaudio', 'main_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmaudio-audio', 'frmaudio', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);