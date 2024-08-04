<?php
class NOTIFICATIONS_CLASS_ConsoleBridge
{
    /**
     * Class instance
     *
     * @var NOTIFICATIONS_CLASS_ConsoleBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_CLASS_ConsoleBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const CONSOLE_ITEM_KEY = 'notification';

    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $item = new NOTIFICATIONS_CMP_ConsoleItem();
        $event->addItem($item, 3);
        $item->setViewAll(OW::getLanguage()->text('notifications','view_all'), OW::getRouter()->urlForRoute('base.notifications'));
    }

    public function addNotification( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $cache = array();
        if (isset($data['cache'])) {
            $cache = $data['cache'];
        }

        if ( empty($params['entityType']) || empty($params['entityId']) || empty($params['userId']) || empty($params['pluginKey']) )
        {
            throw new InvalidArgumentException('`entityType`, `entityId`, `userId`, `pluginKey` are required');
        }

        if ( !$this->service->isNotificationPermited($params['userId'], $params['action'], $cache) )
        {
            return;
        }

        if (isset($cache['notificationsByUser'][$params['userId']][$params['entityType']]) && array_key_exists($params['entityId'], $cache['notificationsByUser'][$params['userId']][$params['entityType']])) {
            $notification = $cache['notificationsByUser'][$params['userId']][$params['entityType']][$params['entityId']];
        } else {
            $notification = $this->service->findNotification($params['entityType'], $params['entityId'], $params['userId']);
        }

        if ( $notification === null )
        {
            $notification = new NOTIFICATIONS_BOL_Notification();
            $notification->entityType = $params['entityType'];
            $notification->entityId = $params['entityId'];
            $notification->userId = $params['userId'];
            $notification->pluginKey = $params['pluginKey'];
            $notification->action = $params['action'];
        }
        else
        {
            $duplicateParams = array(
                'originalEvent' => $event,
                'notificationDto' => $notification,
                'oldData' => $notification->getData()
            );

            $duplicateParams = array_merge($params, $duplicateParams);

            $duplicateEvent = new OW_Event('notifications.on_duplicate', $duplicateParams, $data);
            OW::getEventManager()->trigger($duplicateEvent);

            $data = $duplicateEvent->getData();
            if(isset($data['cancel'])){
                return;
            }
        }

        $notification->viewed = 0;
        if(isset($data['disabled'] ) && $data['disabled'] ){
            $notification->viewed = 1;
        }

        $notification->timeStamp = empty($params['time']) ? time() : $params['time'];
        $notification->active = isset($params['active']) ? (bool)$params['active'] : true;
        $notification->setData($data);

        $notification = $this->service->saveNotification($notification, $cache);

        if ( $notification !== null )
        {
            $data['notification_id'] = $notification->id;
            $event = new OW_Event('notifications.on_add', $params, $data);
            OW::getEventManager()->trigger($event);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function addBatchNotification(OW_Event $event )
    {
        $notificationData = $event->getData();
        $params = $event->getParams();

        $sendData = array("params" => json_encode($params), "notificationData" => json_encode($notificationData));
        $valid = FRMSecurityProvider::sendUsingRabbitMQ($sendData, 'addBatchNotification');

        if (!$valid) {
            $this->directAddBatchNotification($params, $notificationData);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function onRabbitMQNotificationRelease(OW_Event $event) {
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }

        $data = $data->body;
        $data = (object) json_decode($data);

        if ( isset($data->itemType) && ($data->itemType == 'addBatchNotification') ) {
            $params = json_decode($data->params, true);
            $notificationData = json_decode($data->notificationData, true);
            $this->directAddBatchNotification($params, $notificationData);
        }
    }

    /***
     * @param $params
     * @param $notificationData
     */
    public function directAddBatchNotification($params, $data)
    {
        $userIds = $params['userIds'];
        $params = $params['params'];

        if (empty($userIds)){
            return;
        }

        $cache = [];
        $i = 0;
        $step = 50;
        while($i < count($userIds)){
            $partIds = array_slice($userIds, $i, $step);
            if ($i == 0) {
                $cache = NOTIFICATIONS_BOL_Service::getInstance()
                    ->prepareCacheUsersNotifications($partIds, $params['entityId'], $params['entityType']);
            } else {
                $tmp = NOTIFICATIONS_BOL_Service::getInstance()
                    ->prepareCacheUsersNotifications($partIds, $params['entityId'], $params['entityType']);
                foreach(array_keys($cache) as $key){
                    $cache[$key] = $cache[$key] + $tmp[$key];
                }
            }
            $i += $step;
        }


        // instead of using notifications.add for each user
        if ( empty($params['entityType']) || empty($params['entityId']) || empty($params['pluginKey']) )
        {
            throw new InvalidArgumentException('`entityType`, `entityId`, `userId`, `pluginKey` are required');
        }

        $this->service->deleteNotificationByUniqueKeys($params['entityType'],$params['entityId'],$userIds);
        $viewed = (isset($data['disabled']) && $data['disabled']) ? 1: 0;
        $timestamp = empty($params['time']) ? time() : $params['time'];
        $active = isset($params['active']) ? (bool)$params['active'] : true;

        $notifications = [];
        foreach ($userIds as $uid) {
            if (!$this->service->isNotificationPermited($uid, $params['action'], $cache)) {
                return;
            }

            $notification = new NOTIFICATIONS_BOL_Notification();
            $notification->entityType = $params['entityType'];
            $notification->entityId = $params['entityId'];
            $notification->userId = $uid;
            $notification->pluginKey = $params['pluginKey'];
            $notification->action = $params['action'];
            $notification->viewed = $viewed;
            $notification->timeStamp = $timestamp;
            $notification->active = $active;
            $notification->sent = false;
            $notification->setData($data);

            $notifications[] = $notification;

//            $notification = NOTIFICATIONS_BOL_NotificationDao::getInstance()->save($notification);

            OW::getEventManager()->trigger(new OW_Event(NOTIFICATIONS_BOL_Service::EVENT_AFTER_SAVE_NOTIFICATION, array('userId'=>$notification->userId)));
//            $data['notification_id'] = $notification->id;
//            OW::getEventManager()->trigger(new OW_Event('notifications.on_add', $params, $data));
        }

        NOTIFICATIONS_BOL_NotificationDao::getInstance()->batchSave($notifications);
    }

    public function directRemoveNotification($params = array()) {
        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            return;
        }

        $userId = empty($params['userId']) ? null : $params['userId'];
        $entityType = $params['entityType'];
        $entityId = $params['entityId'];

        if ( $userId !== null )
        {
            $this->service->deleteNotification($entityType, $entityId, $userId);
        }
        else
        {
            $this->service->deleteNotificationByEntity($entityType, $entityId);
        }
    }

    public function removeNotification( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            throw new InvalidArgumentException('`entityType` and `entityId` params are required');
        }

        $valid = FRMSecurityProvider::sendUsingRabbitMQ($params, 'remove_notification');
        if (!$valid) {
            $this->directRemoveNotification($params);
        }
    }

    public function changeBirthdayLikeNotification( OW_Event $event ){
        $params = $event->getParams();

        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            throw new InvalidArgumentException('`entityType` and `entityId` params are required');
        }

        $entityType = $params['entityType'];
        $entityId = $params['entityId'];
        $userId = $params['entityId'];
        $notification = $this->service->findNotification( $entityType, $entityId, $userId );
        if(isset($notification)){
            $event = OW::getEventManager()->trigger(new OW_Event('birthdays.like.notification.update', array('notification' => $notification)));
            if (isset($event->getData()['notification'])  && !isset($event->getData()['remove']) ) {
                $notification = $event->getData()['notification'];
                $this->service->saveNotification($notification);
            }
            else if ( isset($event->getData()['remove']) && $event->getData()['remove'] ){
                $this->service->deleteNotification($entityType, $entityId, $userId );
            }
        }
    }
    /* Console list */

    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        if(FRMSecurityProvider::isSocketEnable(true)){
            return;
        }

        $userId = OW::getUser()->getId();
        $data = $event->getItemData(self::CONSOLE_ITEM_KEY);

        $newNotificationCount = $this->service->findNotificationCount($userId, false);
        $allNotificationCount = $this->service->findNotificationCount($userId);

        $data['counter'] = array(
            'all' => $allNotificationCount,
            'new' => $newNotificationCount
        );

        $event->setItemData(self::CONSOLE_ITEM_KEY, $data);
    }

    public function fetchNotifications(OW_Event $event)
    {
        if(!FRMSecurityProvider::isSocketEnable()){
            return;
        }

        $userId = OW::getUser()->getId();
        $data = $this->prepareSocketDataForUser($userId);
        if((int)$data['params']['notification']['counter']['all'] > 0){
            OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $data, 'userId' => (int) $userId)));
        }
    }

    public function getEditedData($pluginKey,$entityId,$entityType,$notificationData, $cache = array())
    {
        $event = new OW_Event('notification.get_edited_data', array('cache' => $cache, 'pluginKey' => $pluginKey, 'entityId' => $entityId, 'entityType' =>$entityType ),$notificationData);
        OW::getEventManager()->trigger($event);
        $notificationData = $event->getData();
        return $notificationData;
    }

    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $userId = OW::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $loadItemsCount = 10;
        $notifications = $this->service->findNotificationList($userId, $params['console']['time'], $params['ids'], $loadItemsCount+1);
        $notificationIds = array();

        $data['listFull'] = count($notifications) <= $loadItemsCount;
        if (count($notifications) === $loadItemsCount+1){
            array_pop($notifications);
        }

        foreach ( $notifications as $notification )
        {
            $notificationData=$this->getEditedData($notification->pluginKey,$notification->entityId,$notification->entityType, $notification->getData());
            if(isset($notificationData["string"]["vars"]["status"]))
                $notificationData["string"]["vars"]["status"]=UTIL_String::truncate(UTIL_HtmlTag::stripTags($notificationData["string"]["vars"]["status"]), 200, '...');
                $itemEvent = new OW_Event('notifications.on_item_render', array(
                'key' => 'notification_' . $notification->id,
                'notificationId' => $notification->id,
                'entityType' => $notification->entityType,
                'entityId' => $notification->entityId,
                'pluginKey' => $notification->pluginKey,
                'userId' => $notification->userId,
                'timestamp' => $notification->timeStamp,
                'viewed' => (bool) $notification->viewed,
                'data' => $notificationData
            ), $notificationData);

            OW::getEventManager()->trigger($itemEvent);

            $item = $itemEvent->getData();

            if ( empty($item) )
            {
                continue;
            }
            
            $notificationIds[] = $notification->id;

            $event->addItem($item, $notification->id);
        }

        $event->setData($data);
        $this->service->markNotificationsViewedByIds($notificationIds);
    }

    public function sendNotificationCountUsingSocket(OW_Event $event){
        if(!FRMSecurityProvider::isSocketEnable()){
            return;
        }

        $params = $event->getParams();

        if (!isset($params['userId'])){
            return;
        }

        $userId = $params['userId'];
        $data = $this->prepareSocketDataForUser($userId);
        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $data, 'userId' => (int) $userId)));

    }

    private function prepareSocketDataForUser($userId){
        $data = array();
        $data['type'] = 'notification';
        $data['params']= array(
            'notification'=>array('counter'=>array(
                'all' => $this->service->findNotificationCount($userId),
                'new' => $this->service->findNotificationCount($userId, false))),
            'console'=>array('time'=>time()));
        return $data;
    }

    private function processDataInterface( $params, $data )
    {
        if ( empty($data['avatar']) )
        {
            return array();
        }

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');
        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                if($questionName == "username"){
                    $userName=BOL_UserService::getInstance()->getUserName($data['avatar']['userId']);
                }else{
                    $userName = BOL_UserService::getInstance()->getDisplayName($data['avatar']['userId']);
                }
                if ( $userName ){
                    $data['string']['vars']['userName'] = $userName;
                }
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                $data[$langProperty] = OW::getLanguage()->text($key[0], $key[1], $vars);
            }
        }

        if ( empty($data['string']) )
        {
            return array();
        }

        if ( !empty($data['contentImage']) )
        {
            $data['contentImage'] = is_string($data['contentImage'])
                ? array( 'src' => $data['contentImage'] )
                : $data['contentImage'];
        }
        else
        {
            $data['contentImage'] = null;
        }
        
        if ( !empty($data["avatar"]["userId"]) )
        {
            $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($data["avatar"]["userId"]));
            $data["avatar"] = $avatarData[$data["avatar"]["userId"]];
        }

        if ( !isset($params['notificationId']) )
        {
            return array();
        }else{
            $notificationId = $params['notificationId'];
        }
        
        $data['contentImage'] = empty($data['contentImage']) ? array() : $data['contentImage'];
        $data['toolbar'] = empty($data['toolbar']) ? array() : $data['toolbar'];
        $data['toolbar'][] =
            array(
                'type' => 'link',
                'label' => OW::getLanguage()->text('notifications','hide_notification'),
                'title' => OW::getLanguage()->text('notifications','hide_notification'),
                'onclick' => "hideNotification(event, '". OW::getRouter()->urlForRoute('notifications-hide', array("id" => $notificationId)) ."', $notificationId)",
                'class' => 'left_floated_button hide_notification_button'
            );
        if(isset($params['timestamp'])) {
            $data['toolbar'][] =
                array(
                    'type' => 'text',
                    'label' => UTIL_DateTime::formatDate((int)$params['timestamp'], true),
                    'class' => 'ow_console_invt_toolbar_date right_floated_button'
                );
        }
        $data['key'] = isset($data['key']) ? $data['key'] : $params['key'];
        $data['viewed'] = isset($params['viewed']) && !$params['viewed'];
        $data['url'] = isset($data['url']) ? $data['url'] : null;

        return $data;
    }

    public function renderItem( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if (is_string($data) )
        {
            return;
        }

        $interface = $this->processDataInterface($params, $data);

        if ( empty($interface) )
        {
            return;
        }

        $item = new NOTIFICATIONS_CMP_NotificationItem();
        $item->setAvatar($interface['avatar']);
        $item->setContent($interface['string']);
        $item->setKey($interface['key']);
        $item->setToolbar($interface['toolbar']);
        $item->setContentImage($interface['contentImage']);
        $item->setUrl($interface['url']);

        if ( isset($interface['disabled']) && $interface['disabled'])
        {
            $item->setUrl(null);
            $item->addClass('ow_console_disabled');
        }

        if ( $interface['viewed'] )
        {
            $item->addClass('ow_console_new_message');
        }

        $event->setData($item->render());
    }


    public function pluginActivate( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setNotificationStatusByPluginKey($pluginKey, true);
    }

    public function pluginDeactivate( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setNotificationStatusByPluginKey($pluginKey, false);
    }

    public function pluginUninstall( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->deleteNotificationByPluginKey($pluginKey);
    }

    public function afterInits()
    {
        OW::getEventManager()->bind('notifications.on_item_render', array($this, 'renderItem'));
    }
    
    public function genericAfterInits()
    {
        OW::getEventManager()->bind('notifications.remove', array($this, 'removeNotification'));
        OW::getEventManager()->bind('notifications.change.birthday.like', array($this, 'changeBirthdayLikeNotification'));
    }

    public function init()
    {
        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $this->genericInit();
        
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'afterInits'));

        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'pluginActivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'pluginDeactivate'));
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'pluginUninstall'));

        OW::getEventManager()->bind('console.load_list', array($this, 'loadList'));
        OW::getEventManager()->bind('console.ping', array($this, 'ping'));
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));
        OW::getEventManager()->bind('notifications.after_save_notifications', array($this, 'sendNotificationCountUsingSocket'));
        OW::getEventManager()->bind('console.fetch', array($this, 'fetchNotifications'));

        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQLogRelease"));
    }
    
    public function genericInit()
    {
        OW::getEventManager()->bind('notifications.add', array($this, 'addNotification'));
        OW::getEventManager()->bind('notifications.batch.add', array($this, 'addBatchNotification'));
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($this, "onRabbitMQNotificationRelease"));
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'genericAfterInits'));
    }
}