<?php
/**
 * Mobile console component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_Console extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // console is not visible for guest users
        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);

            return;
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $pages = MBOL_ConsoleService::getInstance()->getPages();
        $this->assign('pages', $pages);
    }
}