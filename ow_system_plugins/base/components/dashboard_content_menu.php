<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 *
 * TODO delete this class
 */
class BASE_CMP_DashboardContentMenu extends BASE_CMP_ContentMenu
{

    public function __construct()
    {
        $event = new BASE_CLASS_EventCollector('base.dashboard_menu_items');

        OW::getEventManager()->trigger($event);

        $menuItems = $event->getData();

        parent::__construct($menuItems);
    }
}
