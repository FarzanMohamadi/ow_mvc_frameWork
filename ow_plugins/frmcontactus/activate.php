<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmcontactus', 'frmcontactus.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, 'frmcontactus.index', 'frmcontactus', 'bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmcontactus.index', 'frmcontactus', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);