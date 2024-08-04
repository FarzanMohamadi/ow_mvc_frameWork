<?php
if(BOL_NavigationService::getInstance()->findMenuItem('frmterms', 'mobile_bottom_menu_item')==null) {
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmterms.index', 'frmterms', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
}
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmterms');

