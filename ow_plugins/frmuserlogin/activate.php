<?php
/**
 * 
 * All rights reserved.
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmuserlogin', 'frmuserlogin.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, 'frmuserlogin.index', 'frmuserlogin', 'bottom_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmuserlogin.index', 'frmuserlogin', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);