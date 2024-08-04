<?php
/**
 * Console friends section items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.friends.mobile.components
 * @since 1.6.0
 */
class FRIENDS_MCMP_ConsoleNewItems extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct(  $timestamp )
    {
        parent::__construct();

        $service = FRIENDS_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();
        $requests = $service->findNewRequestList($userId, $timestamp);
        $items = FRIENDS_MCMP_ConsoleItems::prepareData($requests);

        $this->assign('items', $items);

        // Mark as viewed
        $service->markAllViewedByUserId($userId);

        $tpl = OW::getPluginManager()->getPlugin('friends')->getMobileCmpViewDir() . 'console_items.html';
        $this->setTemplate($tpl);
    }
}