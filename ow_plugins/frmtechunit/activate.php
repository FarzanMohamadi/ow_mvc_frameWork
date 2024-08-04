<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmtechunit', 'frmtechunit.admin');
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmtechunit.units', 'frmtechunit', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmtechunit.units', 'frmtechunit', 'mobile_main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);