<?php
/**
 * Mobile console notifications page
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleNotificationsPage extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $sections = MBOL_ConsoleService::getInstance()->getPageSections('notifications');

        $tplSections = array();
        foreach ( $sections as $section )
        {
            $tplSections[] = $section['item'];
        }

        $this->assign('items', $tplSections);
    }
}