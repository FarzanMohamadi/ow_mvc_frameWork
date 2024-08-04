<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmmainpage', 'frmmainpage.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmmainpage.index', 'frmmainpage', 'mobile_main_menu_list', OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmmainpage.settings', 'frmmainpage', 'settings', OW_Navigation::VISIBLE_FOR_MEMBER);