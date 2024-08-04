<?php
/**
 * Console notifications section new items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.notifications.mobile.components
 * @since 1.6.0
 */
class NOTIFICATIONS_MCMP_ConsoleNewItems extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct( $timestamp )
    {
        parent::__construct();

        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();

        $notifications = $service->findNewNotificationList($userId, $timestamp);
        $items = NOTIFICATIONS_MCMP_ConsoleItems::prepareData($notifications);
        $this->assign('items', $items);

        // Mark as viewed
        $service->markNotificationsViewedByUserId($userId);

        $tpl = OW::getPluginManager()->getPlugin('notifications')->getMobileCmpViewDir() . 'console_items.html';
        $this->setTemplate($tpl);
    }
}