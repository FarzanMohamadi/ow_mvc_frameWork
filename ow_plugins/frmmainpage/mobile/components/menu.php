<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */
class FRMMAINPAGE_MCMP_Menu extends OW_MobileComponent
{

    public function __construct($item)
    {
        parent::__construct();

        $this->assign('menus', FRMMAINPAGE_BOL_Service::getInstance()->getMenu($item));

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmmainpage')->getMobileCmpViewDir().'menu.html');
    }

}