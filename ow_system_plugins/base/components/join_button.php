<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_JoinButton extends OW_Component
{
    public function __construct( $params = array() )
    {
        parent::__construct();

        if (OW::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
        }

        $this->assign('class', !empty($params['cssClass']) ? $params['cssClass'] : '' );
        $this->assign('url', OW::getRouter()->urlForRoute('base_join'));
    }
}