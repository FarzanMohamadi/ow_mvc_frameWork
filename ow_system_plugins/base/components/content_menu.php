<?php
/**
 * Content menu component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ContentMenu extends BASE_CMP_Menu
{
    public function __construct( $menuItems = null )
    {
        parent::__construct($menuItems);
        
        $this->setTemplate(OW::getPluginManager()
                ->getPlugin('base')->getCmpViewDir().'content_menu.html');
    }
}