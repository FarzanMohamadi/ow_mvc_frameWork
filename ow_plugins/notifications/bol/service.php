<?php
class NOTIFICATIONS_BOL_Service
{
    const SCHEDULE_IMMEDIATELY = 'immediately';
    const SCHEDULE_AUTO = 'auto';
    const SCHEDULE_NEVER = 'never';

    const EVENT_AFTER_SAVE_NOTIFICATION = 'notifications.after_save_notifications';
    const EVENT_FILL_REQUEST_IGNORED = 'notifications.fill_request_ignored';
    const EVENT_AFTER_SEND_QUEUE_FILLED = 'notifications.after_send_queue_filled';
    const EVENT_SEND_NOTIFICATIONS_INCOMPLETE = 'notifications.send_notifications_incomplete';

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var NOTIFICATIONS_BOL_RuleDao
     */
    private $ruleDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_UnsubscribeDao
     */
    private $unsubscribeDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_ScheduleDao
     */
    private $scheduleDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_SendQueueDao
     */
    private $sendQueueDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_NotificationDao
     */
    private $notificationDao;

    private $defaultRuleList = array();

    public function __construct()
    {
        $this->ruleDao = NOTIFICATIONS_BOL_RuleDao::getInstance();
        $this->unsubscribeDao = NOTIFICATIONS_BOL_UnsubscribeDao::getInstance();
        $this->notificationDao = NOTIFICATIONS_BOL_NotificationDao::getInstance();
        $this->scheduleDao = NOTIFICATIONS_BOL_ScheduleDao::getInstance();
        $this->sendQueueDao = NOTIFICATIONS_BOL_SendQueueDao::getInstance();
    }

    public function collectActionList()
    {
        if ( empty($this->defaultRuleList) )
        {
            $event = new BASE_CLASS_EventCollector('notifications.collect_actions');
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();
            foreach ( $eventData as $item )
            {
                $this->defaultRuleList[$item['action']] = $item;
            }
        }

        return $this->defaultRuleList;
    }

    public function saveNotificationsSetting($userId, $actions, $dtoList, $data) {
        $result = 0;

        if (!empty($data['schedule'])) {
            if (in_array($data['schedule'], array('immediately', 'auto', 'never'))) {
                $result += (int) $this->setSchedule($userId, $data['schedule']);
                unset($data['schedule']);
            }
        }

        foreach ( $actions as $action )
        {
            /* @var $dto NOTIFICATIONS_BOL_Rule */
            if ( empty($dtoList[$action]) )
            {
                $dto = new NOTIFICATIONS_BOL_Rule();
                $dto->userId = $userId;
                $dto->action = $action;
            }
            else
            {
                $dto = $dtoList[$action];
            }

            $checked = (int) !empty($data[$action]);

            if ( !empty($dto->id) && $dto->checked == $checked )
            {
                continue;
            }

            $dto->checked = $checked;
            $result++;

            $this->saveRule($dto);
        }

        return $result;
    }

    public function findRuleList( $userId, $cache = array())
    {
        $out = array();
        if (isset($cache['rulesNotificationsByUser']) && array_key_exists($userId, $cache['rulesNotificationsByUser'])) {
            $list = $cache['rulesNotificationsByUser'][$userId];
        } else {
            $list = $this->ruleDao->findRuleList($userId);
        }
        foreach ( $list as $item )
        {
            $out[$item->action] = $item;
        }

        return $out;
    }

    public function saveRule( NOTIFICATIONS_BOL_Rule $rule )
    {
        $this->ruleDao->save($rule);
    }

    public function findUserIdByUnsubscribeCode( $code )
    {
        $dto = $this->unsubscribeDao->findByCode($code);

        return  empty($dto) ? null : $dto->userId;
    }

    private function getUnsubscribeCodeLifeTime()
    {
        return 60 * 60 * 24 * 7;
    }

    public function deleteExpiredUnsubscribeCodeList()
    {
        $time = time() - $this->getUnsubscribeCodeLifeTime();
        $this->unsubscribeDao->deleteExpired($time);
    }

    public function generateUnsubscribeCode( BOL_User $user )
    {
        $code = md5($user->email);
        $dto = new NOTIFICATIONS_BOL_Unsubscribe();
        $dto->userId = $user->id;
        $dto->code = $code;
        $dto->timeStamp = time();

        $this->unsubscribeDao->save($dto);

        return $code;
    }

    
    public function isNotificationPermited( $userId, $action, $cache = array())
    {
        $schedule = NOTIFICATIONS_BOL_ScheduleDao::getInstance()->findByUserId($userId);
        if ($schedule && $schedule->schedule == NOTIFICATIONS_BOL_Service::SCHEDULE_NEVER) {
            return false;
        }
        $defaultRules = $this->collectActionList();
        $rules = $this->findRuleList($userId, $cache);

        if ( isset($rules[$action]) )
        {
            return (bool) $rules[$action]->checked;
        }
        
        return !empty($defaultRules[$action]['selected']);
    }
    
    public function sendPermittedNotifications( $userId, $notificationList )
    {
        $defaultRules = $this->collectActionList();
        $rules = $this->findRuleList($userId);
        $ignoreTypeArr = array('friendship');
        $listToSend = array();
        foreach ( $notificationList as $notification )
        {
            $action = $notification['action'];

            if ( isset($rules[$action]) )
            {
                if ( !$rules[$action]->checked )
                {
                    continue;
                }
            }
            else
            {
                if ( empty($defaultRules[$action]['selected']) )
                {
                    continue;
                }
            }

            if (in_array($notification["entityType"],$ignoreTypeArr )){
               continue;
            }

            $listToSend[] = $notification;
        }

        $this->sendNotifications($userId, $listToSend);
    }

    public function sendNotifications( $userId, $notifications )
    {
        if ( empty($notifications) )
        {
            return;
        }

        $cmp = new NOTIFICATIONS_CMP_Notification($userId);

        foreach ( $notifications as $item )
        {
            $data = $item['data'];
            $params = $item;
            $onEvent = new OW_Event('notifications.on_item_send', $params, $data);
            OW::getEventManager()->trigger($onEvent);

            $item['data'] = $onEvent->getData();

            $cmp->addItem($item);
        }

        $this->sendProcess($userId, $cmp);
    }

    private function sendProcess( $userId, NOTIFICATIONS_CMP_Notification $cmp )
    {
        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);

        if ( empty($user) )
        {
            return;
        }

        $email = $user->email;
        $unsubscribeCode = $this->generateUnsubscribeCode($user);

        $cmp->setUnsubscribeCode($unsubscribeCode);

        $txt = $cmp->getTxt();
        $html = $cmp->getHtml();

        $subject = $cmp->getSubject();

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($txt)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            //Skip invalid notification
        }
    }



    public function findNotificationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        return $this->notificationDao->findNotificationList($userId, $beforeStamp, $ignoreIds, $count);
    }

    public function findNewNotificationList( $userId, $afterStamp )
    {
        return $this->notificationDao->findNewNotificationList($userId, $afterStamp);
    }

    public function findNewNotificationCount( $userId, $afterStamp )
    {
        return $this->notificationDao->findNewNotificationCount($userId, $afterStamp);
    }

    public function findNotificationListForSend( $userIdList )
    {
        return $this->notificationDao->findNotificationListForSend($userIdList);
    }

    public function findNotificationCount( $userId, $viewed = null, $exclude = null )
    {
        return $this->notificationDao->findNotificationCount($userId, $viewed, $exclude);
    }

    public function findIgnoreNotifications()
    {
        return $this->notificationDao->findIgnoreNotifications();
    }

    public function saveNotification( NOTIFICATIONS_BOL_Notification $notification, $cache = array())
    {
        $notification = $this->notificationDao->saveNotification($notification, $cache);
        OW::getEventManager()->trigger(new OW_Event(self::EVENT_AFTER_SAVE_NOTIFICATION, array('userId'=>$notification->userId)));
        return $notification;
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return NOTIFICATIONS_BOL_Notification
     */
    public function findNotification( $entityType, $entityId, $userId )
    {
        return $this->notificationDao->findNotification($entityType, $entityId, $userId);
    }

    public function markNotificationsViewedByIds( $idList, $viewed = true )
    {
        $notificationList = $this->notificationDao->findByIdList($idList);
        $userId = -1;
        $notViewedIdList = [];
        foreach($notificationList as $notification){
            /* @var $notification NOTIFICATIONS_BOL_Notification */
            if(!$notification->viewed){
                $userId = $notification->userId;
                $notViewedIdList[] = $notification->id;
            }
        }
        if(empty($notViewedIdList)){
            return;
        }

        $this->notificationDao->markViewedByIds($notViewedIdList, $viewed);

        //viewed event. all ids are for the same person
        OW::getEventManager()->trigger(new OW_Event('notifications.after_items_viewed', array('userId' => $userId)));
    }

    public function hideNotification( $id )
    {
        if(!OW::getUser()->isAuthenticated()){
            return false;
        }

        $userId = OW::getUser()->getId();
        $notif = $this->notificationDao->findById($id);
        if($notif->userId != $userId){
            return false;
        }

        $this->notificationDao->hideNotificationById($id);
        return true;
    }

    public function markNotificationsViewedByUserId( $userId, $viewed = true )
    {
        $this->notificationDao->markViewedByUserId($userId, $viewed);

        //viewed event
        OW::getEventManager()->trigger(new OW_Event('notifications.after_items_viewed', array('userId' => $userId)));
    }

    public function markNotificationsSentByIds( $idList, $sent = true )
    {
        $this->notificationDao->markSentByIds($idList, $sent);
    }

    public function deleteNotification( $entityType, $entityId, $userId )
    {
        $this->notificationDao->deleteNotification($entityType, $entityId, $userId);
    }

    public function onRabbitMQLogRelease( OW_Event $event ){
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }

        $data = $data->body;
        $data = (array) json_decode($data);

        if (!isset($data['itemType']) || $data['itemType'] != 'remove_notification') {
            return;
        }
        NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->directRemoveNotification($data);
    }

    public function deleteExpiredNotification()
    {
        // delete expired notifications
        $this->notificationDao->deleteExpired();
    }

    public function deleteNotificationByEntity( $entityType, $entityId )
    {
        $this->notificationDao->deleteNotificationByEntity($entityType, $entityId);
    }

    /**
     * @param $entityType
     * @param $entityId
     * @param $userIds
     */
    public function deleteNotificationByUniqueKeys( $entityType, $entityId, $userIds )
    {
        $this->notificationDao->deleteNotificationByUniqueKeys($entityType, $entityId,$userIds);
    }


    public function deleteNotificationByEntityAndAction( $entityType, $entityId, $action )
    {
        $this->notificationDao->deleteNotificationByEntityAndAction($entityType, $entityId, $action);
    }

    public function deleteNotificationByPluginKey( $pluginKey )
    {
        $this->notificationDao->deleteNotificationByPluginKey($pluginKey);
    }
    public function updateNotification($entityType,$entityId,$userId,$data){
        $this->notificationDao->updateNotification($entityType,$entityId,$userId,$data);
    }
    public function setNotificationStatusByPluginKey( $pluginKey, $status )
    {
        $this->notificationDao->setNotificationStatusByPluginKey($pluginKey, $status);
    }

    public function getDefaultSchedule()
    {
        return self::SCHEDULE_IMMEDIATELY;
    }

    public function getSchedule( $userId )
    {
        $entity = $this->scheduleDao->findByUserId($userId);

        return $entity === null ? $this->getDefaultSchedule() : $entity->schedule;
    }

    public function setSchedule( $userId, $schedule )
    {
        $entity = $this->scheduleDao->findByUserId($userId);

        if ( $entity === null )
        {
            $entity = new NOTIFICATIONS_BOL_Schedule();
            $entity->userId = $userId;
        }
        else if ( $entity->schedule == $schedule )
        {
            return false;
        }

        $entity->schedule = $schedule;

        $this->scheduleDao->save($entity);

        return true;
    }

    public function fillSendQueue( $period = null )
    {
        $this->sendQueueDao->fillData($period, $this->getDefaultSchedule());
    }

    public function getSendQueueLength()
    {
        return $this->sendQueueDao->countAll();
    }

    public function findUserIdListForSend( $count )
    {
        $list = $this->sendQueueDao->findList($count);

        if ( empty($list) )
        {
            return array();
        }

        $userIds = array();
        $ids = array();
        foreach ( $list as $item )
        {
            $ids[] = $item->id;
            $userIds[] = $item->userId;
        }

        $this->sendQueueDao->deleteByIdList($ids);

        return $userIds;
    }

    public function prepareCacheUsersNotifications($userIds, $entityId, $entityType) {
        $cachedNotifications = array();
        $cachedUsersDevices = array();
        $cachedNotificationsList = $this->notificationDao->findNotificationsByUserIds($entityType, $entityId, $userIds);
        foreach ($cachedNotificationsList as $cachedNotificationData) {
            if(is_array($cachedNotificationData)){
                $cachedNotifications[$cachedNotificationData['userId']][$entityType][$entityId] = $cachedNotificationData;
            }else{
                $cachedNotifications[$cachedNotificationData->userId][$entityType][$entityId] = $cachedNotificationData;
            }
        }
        foreach ($userIds as $userId) {
            if (!isset($cachedNotifications[$userId])) {
                $cachedNotifications[$userId][$entityType][$entityId] = null;
            }
        }
        $cachedLastViewNotifications = $this->notificationDao->getLastViewedNotificationIdByUserIds($userIds);
        $cachedNotificationsRulesByUsers = $this->ruleDao->findRuleListByUserIds($userIds);
        if (FRMSecurityProvider::checkPluginActive('frmmobilesupport', true)) {
            $cachedUsersDevices = FRMMOBILESUPPORT_BOL_DeviceDao::getInstance()->getUsersDevices($userIds);
        }
        $cache = array();
        $cache['notificationsByUser'] = $cachedNotifications;
        $cache['lastViewedNotificationsByUser'] = $cachedLastViewNotifications;
        $cache['rulesNotificationsByUser'] = $cachedNotificationsRulesByUsers;
        $cache['usersDevices'] = $cachedUsersDevices;
        $cache['users'] = BOL_UserService::getInstance()->findUserListByIdList($userIds, true);
        return $cache;
    }

}
