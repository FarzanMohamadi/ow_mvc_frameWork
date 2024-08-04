<?php
/**
 * User console component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ConnectButtonList extends OW_Component
{
    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';

    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $event = new BASE_CLASS_EventCollector(self::HOOK_REMOTE_AUTH_BUTTON_LIST);
        OW::getEventManager()->trigger($event);
        $buttonList = $event->getData();

        if ( OW::getUser()->isAuthenticated() || empty($buttonList) )
        {
            $this->setVisible(false);

            return;
        }

        $markup = '';

        foreach ( $buttonList as $button )
        {
            $markup .= $button['markup'];
        }

        $this->assign('buttonList', $markup);
    }
}