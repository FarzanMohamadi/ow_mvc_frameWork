<?php
if(BOL_NavigationService::getInstance()->findMenuItem('frmuserlogin', 'mobile_bottom_menu_item')==null) {
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmuserlogin.index', 'frmuserlogin', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
}

