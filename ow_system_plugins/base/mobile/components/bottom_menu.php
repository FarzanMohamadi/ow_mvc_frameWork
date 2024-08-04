<?php
/**
 * Main menu component class. 
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_BottomMenu extends BASE_CMP_Menu
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'bottom_menu.html');
        $this->name = BOL_MobileNavigationService::MENU_TYPE_BOTTOM;
        $menuItems = BOL_NavigationService::getInstance()->findMenuItems(BOL_MobileNavigationService::MENU_TYPE_BOTTOM);
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($menuItems));
    }
}