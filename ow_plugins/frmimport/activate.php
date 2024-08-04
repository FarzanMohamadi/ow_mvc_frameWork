<?php
/**
 * 
 * All rights reserved.
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmimport', 'frmimport.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, 'frmimport.import.index', 'frmimport', 'bottom_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);