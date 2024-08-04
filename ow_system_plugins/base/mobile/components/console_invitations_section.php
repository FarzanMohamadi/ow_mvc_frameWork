<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleInvitationsSection extends OW_MobileComponent
{
    /**
     * @var BOL_InvitationService
     */
    private $service;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = BOL_InvitationService::getInstance();

        $allInvitationCount = $this->service->findInvitationCount(OW::getUser()->getId());
        if ( !$allInvitationCount )
        {
            $this->setVisible(false);
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $limit = MBOL_ConsoleService::SECTION_ITEMS_LIMIT;
        $this->addComponent('itemsCmp', new BASE_MCMP_ConsoleInvitations($limit));
        $this->assign('loadMore', $this->service->findInvitationCount(OW::getUser()->getId()) > $limit);

        $params = array(
            'cmdUrl' => OW::getRouter()->urlFor('BASE_MCTRL_Invitations', 'command')
        );

        $script = 'var invitationsConsole = new OWM_InvitationsConsole(' . json_encode($params) . ');';

        OW::getDocument()->addOnloadScript($script);
    }
}