<?php
/**
 * Page Sidebar
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_SignInButtonList extends OW_Component
{
    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $cmp = new BASE_CMP_ConnectButtonList();

        $this->addComponent('cmp', $cmp);

        if( !$cmp->isVisible() )
        {
            $this->setVisible(false);
        }
    }
}