<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */

class FRMMAINPAGE_BOL_Service
{

    static $item_count = 20;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
    /**
     * Singleton instance.
     *
     * @var FRMMAINPAGE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMMAINPAGE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getMenu($type){
        $menus = array();
        $imgSource = OW::getPluginManager()->getPlugin('frmmainpage')->getStaticUrl() . 'img/';

        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $orders = $service->getMenuOrder();

        $unreadGroupCount = (OW::getPluginManager()->isPluginActive('groups'))?GROUPS_BOL_Service::getInstance()->getUnreadGroupsCountForUser():0;
        $unreadChatCount = (OW::getPluginManager()->isPluginActive('mailbox'))?MAILBOX_BOL_ConversationService::getInstance()->getUnreadConversationsCount():0;

        $disables= $service->getDisabledList();

        foreach ($orders as $orderMenu) {
            if(($key = array_search($orderMenu, $disables)) !== false){
                continue;
            }
            if ($orderMenu =='dashboard' && OW::getPluginManager()->isPluginActive('newsfeed')) {
                $menu = array();
                $removeDashboardStatusForm = OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm');
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'dashboard.svg';
                $dashboardActiveClass = '';
                if (isset($removeDashboardStatusForm) && $removeDashboardStatusForm=="on") {
                    $menu['iconUrl'] = $imgSource . 'dashboard2.svg';
                }
                if ($type == 'dashboard') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'dashboard_select.svg';
                    if (isset($removeDashboardStatusForm) && $removeDashboardStatusForm=="on") {
                        $menu['iconUrl'] = $imgSource . 'dashboard2_select.svg';
                        $dashboardActiveClass = ' removeDashboardStatusForm';
                    }

                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_dashboard'.$dashboardActiveClass;
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.dashboard');
                $menus[] = $menu;
            }

            if ($orderMenu =='user-groups' && OW::getPluginManager()->isPluginActive('groups')) {
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'groups.svg';
                if ($type == 'user-groups') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'groups_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_user_groups';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.user.groups');
                $menu['badgeNumber'] = $unreadGroupCount;
                $menus[] = $menu;
            }

            if ($orderMenu =='friends' && OW::getPluginManager()->isPluginActive('friends')) {
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'friend.svg';
                if ($type == 'friends') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'friend_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_friends';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.friends');
                $menus[] = $menu;
            }

            if ($orderMenu =='mailbox' && OW::getPluginManager()->isPluginActive('mailbox')) {
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'chat.svg';
                $menu['class'] = 'menu_messages';
                if ($type == 'mailbox') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'chat_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_mailbox';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.mailbox');
                $menu['badgeNumber'] = $unreadChatCount;
                $menus[] = $menu;
            }

            if($orderMenu =='settings'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'Settings.svg';
                if ($type == 'settings') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'Settings_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_settings';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.settings');
                $menus[] = $menu;
           }

            if($orderMenu =='notifications'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'notifications.svg';
                if ($type == 'notifications') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'notifications_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_notifications';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.notifications');

                if(OW::getUser()->isAuthenticated()) {
                    $userId = OW::getUser()->getId();
                    $badgeNumber = BOL_InvitationService::getInstance()->findInvitationCount($userId);
                    if (OW::getPluginManager()->isPluginActive('notifications')) {
                        $badgeNumber += NOTIFICATIONS_BOL_Service::getInstance()->findNotificationCount($userId, false);
                    }
                    if (OW::getPluginManager()->isPluginActive('friends')) {
                        $badgeNumber += FRIENDS_BOL_Service::getInstance()->countRequestsForUser();
                    }
                    $menu['badgeNumber'] = $badgeNumber;
                }
                $menus[] = $menu;
            }

            if($orderMenu =='photos'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'photos.svg';
                if ($type == 'photos') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'photos_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_photos';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.photos');
                $menus[] = $menu;
            }

            if($orderMenu =='videos'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'videos.svg';
                if ($type == 'videos') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'videos_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_videos';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.videos');
                $menus[] = $menu;
            }

            if($orderMenu =='chatGroups'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                $menu['iconUrl'] = $imgSource . 'chatGroups.svg';
                if ($type == 'chatGroups') {
                    $menu['active'] = true;
                    $menu['iconUrl'] = $imgSource . 'chatGroups_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_chatGroups';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.chatGroups');
                $menu['badgeNumber'] = $unreadGroupCount + $unreadChatCount;
                $menus[] = $menu;
            }

            if($orderMenu =='distinctChatChanelGroup'){
                $menu = array();
                $menu['title'] = $this->getLableOfMenu($orderMenu);
                //TODO create specific iconUrl for distinctChatChanelGroup
                $menu['iconUrl'] = $imgSource . 'chatGroups.svg';
                if ($type == 'distinctChatChanelGroup') {
                    $menu['active'] = true;
                    //TODO create specific iconUrl for distinctChatChanelGroup
                    $menu['iconUrl'] = $imgSource . 'chatGroups_select.svg';
                } else {
                    $menu['active'] = false;
                }
                $menu['class'] = 'main_menu_distinctChatChanelGroup';
                $menu['url'] = OW::getRouter()->urlForRoute('frmmainpage.distinctChatChanelGroup',array('list' => 'all'));
                $menu['badgeNumber'] = $unreadGroupCount + $unreadChatCount;
                $menus[] = $menu;
            }
        }

        return $menus;
    }

    /***
     * @return array
     */
    public function getMenuOrder(){
        $orders = '';
        if(OW::getConfig()->configExists('frmmainpage', 'orders')) {
            $orders = OW::getConfig()->getValue('frmmainpage', 'orders');
        }
        $list = $orders!=''?json_decode(OW::getConfig()->getValue('frmmainpage', 'orders')):null;
        if($list == null || (is_array($list) && empty($list)) || !is_array($list)){
            $list = $this->getMenuByDefaultOrder();
        }
        $new_list = array_merge($list, $this->getMenuByDefaultOrder());
        $new_list = array_unique($new_list);

        if($list != $new_list) {
            $this->savePageOrdered($new_list);
        }
        return $new_list;
    }
    /***
     * @return array
     */
    public function getMenuByDefaultOrder(){
        $list = array();
        $list[] = 'notifications';
        $list[] = 'user-groups';
        $list[] = 'dashboard';
        $list[] = 'friends';
        $list[] = 'mailbox';
        $list[] = 'photos';
        $list[] = 'videos';
        $list[] = 'settings';
        $list[] = 'chatGroups';
        $list[] = 'distinctChatChanelGroup';
        return $list;
    }

    /***
     * @param $list
     */
    public function savePageOrdered($list){
        OW::getConfig()->saveConfig('frmmainpage', 'orders', json_encode($list));
    }

    public function getLableOfMenu($key){
        $languages = OW::getLanguage();
        if($key == 'dashboard'){
            return $languages->text('base', 'console_item_label_dashboard');
        }else if($key == 'user-groups'){
            return $languages->text('groups', 'group_list_menu_item_my');
        }else if($key == 'friends'){
            return $languages->text('friends', 'notification_section_label');
        }else if($key == 'mailbox'){
            return $languages->text('mailbox', 'messages_console_title');
        }else if($key == 'settings'){
            return $languages->text('frmmainpage', 'settings');
        }else if($key == 'notifications'){
            return $languages->text('base', 'notifications');
        }else if($key == 'photos'){
            return $languages->text('frmmainpage', 'public_photos');
        }else if($key == 'videos'){
            return $languages->text('video', 'video');
        }else if($key == 'chatGroups'){
            return $languages->text('frmmainpage', 'chatGroups');
        }else if($key == 'distinctChatChanelGroup'){
            return $languages->text('frmmainpage', 'distinctChatChanelGroup');
        }

        return '';
    }

    public function isPluginExist($key){
        if($key == 'dashboard'){
            return OW::getPluginManager()->isPluginActive('newsfeed');
        }else if($key == 'user-groups'){
            return OW::getPluginManager()->isPluginActive('groups');
        }else if($key == 'friends'){
            return OW::getPluginManager()->isPluginActive($key);
        }else if($key == 'mailbox'){
            return OW::getPluginManager()->isPluginActive($key);
        }else if($key == 'notifications'){
            return OW::getPluginManager()->isPluginActive($key);
        }else if($key == 'photos'){
            return OW::getPluginManager()->isPluginActive('photo');
        }else if($key == 'videos'){
            return OW::getPluginManager()->isPluginActive('video');
        }else if($key == 'chatGroups'){
            return OW::getPluginManager()->isPluginActive('mailbox') && OW::getPluginManager()->isPluginActive('groups');
        }else if($key == 'distinctChatChanelGroup'){
            return OW::getPluginManager()->isPluginActive('mailbox') && OW::getPluginManager()->isPluginActive('groups') && OW::getPluginManager()->isPluginActive('frmgroupsplus');
        }

        return true;
    }

    public function addToDisableList($id)
    {
        $config = OW::getConfig();
        $disables = array();
        if(!$config->configExists('frmmainpage', 'disables'))
        {
            $disables[]=$id;
            OW::getConfig()->saveConfig('frmmainpage', 'disables', json_encode($disables));
        }
        else {
            $disables = json_decode($config->getValue('frmmainpage', 'disables'),true);
            if ( !in_array($id,$disables) ){
                $disables[] = $id;
                $config->saveConfig('frmmainpage', 'disables', json_encode($disables));
            }
        }
    }

    public function addAsDefault($id)
    {
        $config = OW::getConfig();
        $config->saveConfig('frmmainpage', 'defaultPage', $id);
    }

    public function removeFromDisableList($id)
    {
        $config = OW::getConfig();
        if($config->configExists('frmmainpage', 'disables'))
        {
            $disables = json_decode($config->getValue('frmmainpage', 'disables'),true);
            if (($key = array_search($id, $disables)) !== false) {
                unset($disables[$key]);
            }
            if (sizeof($disables) == 0)
                $config->deleteConfig('frmmainpage', 'disables');
            else
                $config->saveConfig('frmmainpage', 'disables', json_encode($disables));
        }
    }

    public function getDisabledList(){
        $allItems = $this->getMenuByDefaultOrder();
        foreach ($allItems as $key){
            if(!$this->isPluginExist($key)){
                $this->addToDisableList($key);
            }
        }

        $disables = array();
        if(OW::getConfig()->configExists('frmmainpage', 'disables')){
            $disables =  json_decode(OW::getConfig()->getValue('frmmainpage', 'disables'),true);
            $disables = (is_array($disables))?$disables:[];
        }
        return $disables;
    }

    public function isDisabled($id){
        $disables = $this->getDisabledList();
        if (in_array($id,$disables)){
            return true;
        }
        return false;
    }

    public function findUserChats($userId,$first,$count=10,$q=null)
    {
        $chatResult = $this->findUserChatsQuery($userId, $q);
        $query = $chatResult["query"];
        $params = $chatResult["params"];
        $query = $query . "
                 ORDER BY lastActivityTimeStamp DESC
                 LIMIT {$first}, {$count};";
        $result = OW::getDbo()->queryForList($query, $params);
        return $result;
    }

    private function findUserChatsQuery($userId, $q=null)
    {
        if(isset($q))
        {
            $chatResult=MAILBOX_BOL_ConversationService::getInstance()->searchMessagesListQuery($userId, $q);
        }
        else {
            $chatResult = MAILBOX_BOL_ConversationDao::getInstance()->findConversationItemListByUserIdQuery($userId, array('chat'));
        }
        return $chatResult;
    }

    public function findUserChatsAndGroups($userId,$first,$count=10,$q=null)
    {
        $dataArr = array();
        $chatResult = $this->findUserChatsQuery($userId, $q);
        $query = $chatResult["query"];
        $params = $chatResult["params"];

        if(isset($q)) {
            $searchValue = NEWSFEED_BOL_Service::generateDataSearchStringForNewsFeed($q);
            $groupMessageData = NEWSFEED_BOL_ActionDao::getInstance()->findSiteFeedQuery(array(0, $first+$count), null, null, null, $searchValue, GROUPS_BOL_Service::GROUP_FEED_ENTITY_TYPE);
            $query .= "\n UNION \n" . $groupMessageData["query"];
            $params = array_merge($params , $groupMessageData["params"]);
        }


        $tplList = GROUPS_BOL_GroupDao::getInstance()->findByUserIdQuery($userId, null, $q);

        $query .= "\n UNION \n" . $tplList["query"];
        $params = array_merge($params , $tplList["params"]);



        $query = $query . "
                 ORDER BY lastActivityTimeStamp DESC
                 LIMIT {$first}, {$count};";
        $result = OW::getDbo()->queryForList($query, $params);
        $dataArr = $result;

        return $dataArr;
    }

    public function prepareChatGroupData($dataArr,$excludeData=array(),$q=null, $fetchLastGroupPost = true)
    {
        $tplList = array();
        $groupIds = array();
        $messageIds = array();
        $groupsStatus = array();

        foreach($dataArr as $key => $value) {
            $id = $value['id'];
            $type = $value['type'];
            if ($type == 'groups-status') {
                $groupsStatus[] = $id;
            } else if ($type == 'group') {
                $groupIds[] = $id;
            } else if ($type == 'chat') {
                $messageIds[] = $id;
            }
        }

        $groupsUnreadCount = array();
        $groupsUsersCountList = array();
        $groupValues = array();
        $messageValues = array();
        $conversationValues = array();
        $conversationIds = array();
        $groupsStatusValues = array();


        if (!empty($groupsStatus)) {
            $groupsStatusValues = NEWSFEED_BOL_ActionDao::getInstance()->findByIdList($groupsStatus);
            foreach ($groupsStatusValues as $groupStatusItem) {
                /* @var $groupStatusItem NEWSFEED_BOL_Action */
                $groupStatusItemData = json_decode($groupStatusItem->data);
                $groupId = $groupStatusItemData->contextFeedId;
                $groupIds[] = $groupId;
            }
        }
        if(isset($groupIds)) {
            $groupsUnreadCount = GROUPS_BOL_Service::getInstance()->getUnreadCountForEachGroupUser();
            $groupsUsersCountList = GROUPS_BOL_Service::getInstance()->findUserCountForList($groupIds);
            $groupValues = GROUPS_BOL_Service::getInstance()->findGroupsWithIds($groupIds);
        }



        if (!empty($messageIds)) {
            if(!isset($q)) {
                $conversationIds = $messageIds;
                $conversationValues = MAILBOX_BOL_ConversationDao::getInstance()->findByIdList($conversationIds);
            } else {
                $messageValues = MAILBOX_BOL_MessageDao::getInstance()->findByIdList($messageIds);
                if(!empty($messageValues)) {
                    $conversationIds = array_column($messageValues, 'conversationId');
                    $conversationIds = array_unique($conversationIds);
                    $conversationValues = MAILBOX_BOL_ConversationDao::getInstance()->findByIdList($conversationIds);
                }
            }

        }


        foreach($dataArr as $key => $value)
        {
            if($value['type'] == 'group') {

                $groupId = $value['id'];

                $groupValueId = array_search($groupId, array_column($groupValues, 'id'));
                /* @var $value GROUPS_BOL_Group*/
                $value = $groupValues[$groupValueId];
                $excludeData[]='group-'.$value->id;
                $userCount = 0;
                if (isset($groupsUsersCountList[$value->id])) {
                    $userCount = $groupsUsersCountList[$value->id];
                }
                $title = strip_tags($value->title);

                $toolbar = array(
                    array(
                        'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                            'count' => $userCount
                        ))
                    )
                );

                $groupLastPost = $value->description;
                if ($fetchLastGroupPost) {
                    $lastPost = NEWSFEED_BOL_ActionDao::getInstance()->findByFeed('groups', $value->id, array(0, 1, false), null, null, null, null);
                    if ($lastPost != null && isset($lastPost[0]) && isset($lastPost[0]->data)) {
                        $groupLastPost = property_exists(json_decode($lastPost[0]->data), 'status') ? json_decode($lastPost[0]->data)->status : null;
                    }
                }
                $groupStatus = $groupLastPost ? $groupLastPost : $value->description;
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $groupStatus)));
                if (isset($stringRenderer->getData()['string'])) {
                    $groupStatus = ($stringRenderer->getData()['string']);
                }

                $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['parentGroupId'=>isset($value->parentGroupId)? $value->parentGroupId : null]));
                $parentTitle=null;
                if(isset($eventPrepareGroup->getData()['parentData'])){
                    $parentTitle = $eventPrepareGroup->getData()['parentData'];
                }

                $groupImageSource = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($value);
                $unreadCount = 0;
                if (isset($groupsUnreadCount[$value->id])) {
                    $unreadCount = $groupsUnreadCount[$value->id];
                }
                $tplList[] = array(
                    'id' => $value->id,
                    'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $value->id)),
                    'title' => $title,
                    'imageTitle' => $title,
                    'content' => UTIL_String::truncate(strip_tags($groupStatus), 300, '...'),
                    'time' => UTIL_DateTime::formatDate($value->timeStamp),
                    'imageSrc' => $groupImageSource,
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $value->id, $groupImageSource),
                    'unreadCount' => $unreadCount,
                    'users' => $userCount,
                    'type' => 'group',
                    'lastActivityTimeStamp' => $value->lastActivityTimeStamp,
                    'toolbar' => $toolbar,
                    'parentTitle' => $parentTitle
                );
                continue;
            }

            if ($value['type'] == 'groups-status') {

                $excludeData[] = 'groupMessage-' . $value['id'];
                $tplList[] = $this->createTplListFromGroupMessage($value, $groupValues, $groupsUsersCountList, $groupsUnreadCount);

                continue;
            }

            if($value['type'] == 'chat') {


            $conversationItemList=array();
            if(!isset($q)) {
                $conversationId = $value['id'];
                $conversationValuesId = array_search($conversationId, array_column($conversationValues, 'id'));
                /* @var $conversationValue MAILBOX_BOL_Conversation */
                $conversationValue = $conversationValues[$conversationValuesId];

                $value = MAILBOX_BOL_MessageDao::getInstance()->findById($conversationValue->lastMessageId);

                $conversationItem = (array) $conversationValue;
                $excludeData[] = 'chat-' . $value->id;
                $conversationItem['timeStamp'] = (int)$value->timeStamp;
                $conversationItem['lastMessageSenderId'] = $value->senderId;
                $conversationItem['isSystem'] = $value->isSystem;
                $conversationItem['text'] = $value->text;

                $conversationItem['lastMessageId'] = $conversationValue->lastMessageId;
                $conversationItem['recipientRead'] = $value->recipientRead;
                $conversationItem['lastMessageRecipientId'] = $value->recipientId;
                $conversationItem['lastMessageWasAuthorized'] = $value->wasAuthorized;

                $conversationItemList[] = $conversationItem;
                $conversationData = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdListForApi( $conversationItemList );

            }else{
                $messageId = $value['id'];
                $messageValuesId = array_search($messageId, array_column($messageValues, 'id'));
                /* @var $value MAILBOX_BOL_Message */
                $value = $messageValues[$messageValuesId];
                $excludeData[] = 'chat-' . $value->id;
                $conversationItem = $this->prepareSearchedChatData((array)$value);
                $conversationItemList[] = $conversationItem;
                $conversationData=$conversationItemList;
            }
            $tplList=array_merge($tplList,$conversationData);
            }
        }

        return array('tplList'=>$tplList,'excludeData'=>$excludeData);
    }

    public function prepareSearchedChatData($item)
    {
        $avatarService = BOL_AvatarService::getInstance();
        $opponentId = $item['senderId'];
        if($opponentId == OW::getUser()->getId()){
            $opponentId = $item['recipientId'];
        }
        $convId = $item['conversationId'];
        $convIds[] = $convId;
        $item['opponentId']=$opponentId;
        $item['avatarUrl']= BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);
        $item['opponentUrl']= BOL_UserService::getInstance()->getUserUrl($opponentId);
        $item['opponentName']= BOL_UserService::getInstance()->getDisplayName($opponentId);
        $item['text'] = MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($item['text']);
        $item['timeString'] = UTIL_DateTime::formatDate((int)$item['timeStamp'], true);
        $item['mode'] = MAILBOX_BOL_ConversationService::getInstance()->getConversationMode((int)$convId);
        $item['unreadCount'] = MAILBOX_BOL_ConversationService::getInstance()->countUnreadMessagesForConversation((int)$convId, OW::getUser()->getId());
        if ($item['mode'] == 'chat') {
            $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
        }else {
            $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
        }
        return $item;
    }
    public function check_permission($action_name){
        $values = $this->getDisabledList();
        if (in_array($action_name, $values)){
            throw new Redirect404Exception();
        }
        return '';
    }

    public function getContentMenu()
    {
        $menuItems = array();

        //TODO set a good iconClass
        $listNames = array(
            'all' => array('iconClass' => 'ow_ic_bookmark'),
            'chat' => array('iconClass' => 'ow_ic_friends'),
            'group' => array('iconClass' => 'ow_ic_reply'),
            'chanel' => array('iconClass' => 'ow_ic_calendar')
        );

        foreach ( $listNames as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listKey);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmmainpage.distinctChatChanelGroup', array('list' => $listKey)));
            $menuItem->setLabel(OW::getLanguage()->text('frmmainpage', 'list_type_' . $listKey . '_label'));
            $menuItem->setIconClass($listArr['iconClass']);
            $menuItems[] = $menuItem;
        }
        return new BASE_MCMP_ContentMenu($menuItems);
    }


    /**
     * @param $value
     * @param array $groupValues
     * @param array $groupsUsersCountList
     * @param $groupsUnreadCount
     * @return array
     */
    private function createTplListFromGroupMessage($value, $groupValues, $groupsUsersCountList, $groupsUnreadCount)
    {

        $newsfeed_action_id = $value['id'];
        $newsfeed = NEWSFEED_BOL_ActionDao::getInstance()->findById($newsfeed_action_id);
        $value = $newsfeed;

        $valueData = json_decode($value->data);

        $context = $valueData->context;
        $title = $context->label;
        $content = $valueData->data->status;
        $timestamp = $valueData->time;
        $groupId = $valueData->contextFeedId;

        /* @var $group GROUPS_BOL_GROUP */
        $groupValueId = array_search($groupId, array_column($groupValues, 'id'));
        $group = $groupValues[$groupValueId];
        $groupImageSource = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);


        $userCount = 0;
        if (isset($groupsUsersCountList[$groupId])) {
            $userCount = $groupsUsersCountList[$groupId];
        }
        $toolbar = array(
            array(
                'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                    'count' => $userCount
                ))
            )
        );

        $unreadCount = 0;
        if (isset($groupsUnreadCount[$groupId])) {
            $unreadCount = $groupsUnreadCount[$groupId];
        }

        return array(
            'id' => $value->id,
            'url' => $context->url,
            'title' => $title,
            'imageTitle' => $title,
            'content' => UTIL_String::truncate(strip_tags($content), 300, '...'),
            'time' => UTIL_DateTime::formatDate($timestamp),
            'imageSrc' => $groupImageSource,
            'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int)$group->id, $groupImageSource),
            'unreadCount' => $unreadCount,
            'users' => $userCount,
            'type' => 'group',
            'lastActivityTimeStamp' => $valueData->time,
            'toolbar' => $toolbar,
            'groupId' => $groupId,
        );
    }

}