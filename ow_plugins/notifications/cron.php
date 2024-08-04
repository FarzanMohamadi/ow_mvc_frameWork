<?php
/**
 * Notifications Cron
 *
 * @package ow_plugins.notifications
 * @since 1.0
 */
class NOTIFICATIONS_Cron extends OW_Cron
{
    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = NOTIFICATIONS_BOL_Service::getInstance();

        $this->addJob('expireUnsubscribe', 60 * 60);
        $this->addJob('deleteExpired', 24 * 60);

        $this->addJob('fillSendQueue', 10);
        $this->addJob('runBasic', 10);

    }

    public function run()
    {
        //ignore
    }

    public function expireUnsubscribe()
    {
        $this->service->deleteExpiredUnsubscribeCodeList();
    }

    public function deleteExpired()
    {
        $this->service->deleteExpiredNotification();
    }

    public function fillSendQueue()
    {
        if ( $this->service->getSendQueueLength() == 0 )
        {
            $this->service->fillSendQueue(24 * 3600);
            OW::getEventManager()->trigger(new OW_Event(NOTIFICATIONS_BOL_Service::EVENT_AFTER_SEND_QUEUE_FILLED));
        } else {
            OW::getEventManager()->trigger(new OW_Event(NOTIFICATIONS_BOL_Service::EVENT_FILL_REQUEST_IGNORED));
        }
    }

    public function runBasic()
    {
        $limit = 100;
        $users = $this->service->findUserIdListForSend($limit);

        if ( empty($users) )
        {
            return;
        }

        $listEvent = new BASE_CLASS_EventCollector('notifications.send_list', array(
            'userIdList' => $users
        ));

        OW::getEventManager()->trigger($listEvent);

        $notifications = array();
        foreach ( $listEvent->getData() as $notification )
        {
            $itemEvent = new OW_Event('notifications.on_item_send', $notification, $notification['data']);
            OW::getEventManager()->trigger($itemEvent);

            $notification['data'] = $itemEvent->getData();

            $notifications[$notification['userId']][] = $notification;
        }

        foreach ( $notifications as $userId => $notificationList )
        {
            $this->service->sendPermittedNotifications($userId, $notificationList);
        }

        if (count($users) == $limit) {
            OW::getEventManager()->trigger(new OW_Event(NOTIFICATIONS_BOL_Service::EVENT_SEND_NOTIFICATIONS_INCOMPLETE));
        }
    }
}