<?php

class FRIENDS_CLASS_RequestEventHandler
{
    /**
     * Class instance
     *
     * @var FRIENDS_CLASS_RequestEventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRIENDS_CLASS_RequestEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const CONSOLE_ITEM_KEY = 'friend_requests';

    /**
     *
     * @var FRIENDS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = FRIENDS_BOL_Service::getInstance();
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if (OW::getUser()->isAuthenticated())
        {
            $item = new FRIENDS_CMP_ConsoleFriendRequests();
            $count = $this->service->countFriendRequests();
            if ( $count == 0 )
            {
                $item->setIsHidden(true);
            }

            $event->addItem($item, 5);
        }
    }

    /* Console list */
    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        $isWebservice = false;
        $mobileSupportEvent = OW::getEventManager()->trigger(new OW_Event('check.url.webservice', array()));
        if (isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService']) {
            $isWebservice = true;
        }

        if(FRMSecurityProvider::isSocketEnable(true) && $isWebservice){
            return;
        }

        $userId = OW::getUser()->getId();
        $data = $event->getItemData(self::CONSOLE_ITEM_KEY);

        $allInvitationCount = $this->service->countFriendRequests();
        $newInvitationCount = $this->service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING, null, false);

        $data['counter'] = array(
            'all' => $allInvitationCount,
            'new' => $newInvitationCount
        );

        $event->setItemData('friend_requests', $data);
    }

    public function fetchRequests(OW_Event $event)
    {
        if(!FRMSecurityProvider::isSocketEnable()){
            return;
        }
        $userId = OW::getUser()->getId();
        $data = $this->prepareSocketDataForUser($userId);
        if((int)$data['params']['friend_requests']['counter']['all'] > 0){
            OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $data, 'userId' => (int) $userId)));
        }
    }

    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $userId = OW::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $requests = $this->service->findRequestList($userId, $params['console']['time'], $params['offset'], 10);

        $requestIds = array();

        foreach ( $requests as $request )
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($request->userId), true, true, true, false );
            $avatar = $avatar[$request->userId];

            $userUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>BOL_UserService::getInstance()->getUserName($request->userId)));
            $displayName = BOL_UserService::getInstance()->getDisplayName($request->userId);
            $string = OW::getLanguage()->text('friends', 'console_request_item', array( 'userUrl'=> $userUrl, 'displayName'=>$displayName ));

            $acceptCode='';
            $ignoreCode='';
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'accept_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                $acceptCode = (string)$frmSecuritymanagerEvent->getData()['code'];
            }
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'ignore_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                $ignoreCode =(string)$frmSecuritymanagerEvent->getData()['code'];
            }
            $item = new FRIENDS_CMP_RequestItem();
            $item->setAvatar($avatar);
            $item->setContent($string);
            $item->setToolbar(array(
                array(
                    'label' => OW::getLanguage()->text('friends', 'accept_request'),
                    'id' => 'friend_request_accept_'.$request->userId
                ),
                array(
                    'label' => OW::getLanguage()->text('friends', 'ignore_request'),
                    'id' => 'friend_request_ignore_'.$request->userId
                )
            ));

            if (!$request->viewed)
            {
                $item->addClass('ow_console_new_message');
            }


            $js = UTIL_JsGenerator::newInstance();

            $js->jQueryEvent('#friend_request_accept_'.$request->userId, 'click', <<<EOT
OW.FriendRequest.accept('{$item->getKey()}', {$request->userId},'{$acceptCode}' );
EOT
);

            $js->jQueryEvent('#friend_request_ignore_'.$request->userId, 'click', <<<EOT
OW.FriendRequest.ignore('{$item->getKey()}', {$request->userId},'{$ignoreCode}');
EOT
);

            OW::getDocument()->addOnloadScript($js->generateJs());

            $requestIds[] = $request->id;

            $event->addItem($item->render());
        }

        $this->service->markViewedByIds($requestIds);
    }

    public function sendFriendRequestsCountUsingSocket(OW_Event $event){

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
        $data['type'] = 'friend_requests';
        $data['params']= array(
            'friend_requests'=>array('counter'=>array(
                'all' => $this->service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING),
                'new' => $this->service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING, null, false))),
            'console'=>array('time'=>time()));

        return $data;
    }

    public function init()
    {
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));
        OW::getEventManager()->bind('console.ping', array($this, 'ping'));
        OW::getEventManager()->bind('console.load_list', array($this, 'loadList'));
        OW::getEventManager()->bind('friends.after_request', array($this, 'sendFriendRequestsCountUsingSocket'));
        OW::getEventManager()->bind('console.fetch', array($this, 'fetchRequests'));

    }
}