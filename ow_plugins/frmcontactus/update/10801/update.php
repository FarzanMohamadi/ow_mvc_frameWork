<?php
if(BOL_NavigationService::getInstance()->findMenuItem('frmcontactus', 'mobile_bottom_menu_item')==null) {
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmcontactus.index', 'frmcontactus', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
}

