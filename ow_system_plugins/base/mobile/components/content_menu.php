<?php
/**
 * Mobile content menu component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ContentMenu extends BASE_CMP_Menu
{
    public function __construct( $menuItems = null )
    {
        parent::__construct();

        $this->setMenuItems($menuItems);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir().'content_menu.html');
    }
}