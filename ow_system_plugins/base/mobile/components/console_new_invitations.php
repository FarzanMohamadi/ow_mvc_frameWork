<?php
/**
 * Console invitations section items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleNewInvitations extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct( $timestamp )
    {
        parent::__construct();

        $service = BOL_InvitationService::getInstance();
        $userId = OW::getUser()->getId();

        $invitations = $service->findNewInvitationList($userId, $timestamp);
        $items = BASE_MCMP_ConsoleInvitations::prepareData($invitations);
        $this->assign('items', $items);

        // Mark as viewed
        $service->markViewedByUserId($userId);

        $tpl = OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'console_invitations.html';
        $this->setTemplate($tpl);
    }
}