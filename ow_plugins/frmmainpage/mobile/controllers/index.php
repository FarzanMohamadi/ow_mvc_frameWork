<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */

class FRMMAINPAGE_MCTRL_Index extends OW_MobileActionController
{
    const MAX_COUNT=1000;
    public function index($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            $ru = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back_uri' => OW::getRequest()->getRequestUri()));
            OW::getApplication()->redirect($ru);
        }

        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $orders = OW::getConfig()->getValue('frmmainpage', 'orders');
        $first_menu = 'dashboard';
        if(OW::getConfig()->configExists('frmmainpage', 'defaultPage'))
        {
            $defaultPage= OW::getConfig()->getValue('frmmainpage', 'defaultPage');
            if(isset($defaultPage)  && !$service->isDisabled($defaultPage))
            {
                $first_menu= $defaultPage;
            }
        }
        else {
            if ($orders != '') {
                $orders = json_decode($orders, true);
                for ($i = 0; $i < sizeof($orders); $i++) {
                    $first_menu = $orders[$i];
                    if (!$service->isPluginExist($first_menu) || $service->isDisabled($first_menu)) {
                        continue;
                    }
                    $first_menu = $orders[$i];
                }
            }
        }

        if($first_menu=='dashboard'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.dashboard'));
        }else if($first_menu=='user-groups'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.user.groups'));
        }else if($first_menu=='friends'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.friends'));
        }else if($first_menu=='mailbox'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.mailbox'));
        }else if($first_menu=='settings'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.settings'));
        }else if($first_menu=='notifications'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.notifications'));
        }else if($first_menu=='photos'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.photos'));
        }else if($first_menu=='videos'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.videos'));
        }else if($first_menu=='chatGroups'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.chatGroups'));
        }else if($first_menu=='distinctChatChanelGroup'){
            $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.distinctChatChanelGroup',array('list' => 'all')));
        }

        $this->redirect(OW::getRouter()->urlForRoute('frmmainpage.dashboard'));
    }

    public function dashboard($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('dashboard');

        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('newsfeed')) {
            throw new Redirect404Exception();
        }
        $changeTitleEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.change.hear.name'));
        if(isset($changeTitleEvent->getData()['title'])){
            OW::getDocument()->setHeading($changeTitleEvent->getData()['title']);
        }else{
            OW::getDocument()->setHeading(OW::getLanguage()->text('base', 'dashboard_heading'));
        }
        $this->assign('userId', OW::getUser()->getId());

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('dashboard');
        $this->addComponent('menuCmp', $menuCmp);

        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form'));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $this->assign('otpForm',true);
        }
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function friends($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('friends');

        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('friends')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('friends', 'notification_section_label'));
        $friendsService = FRIENDS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();
        $count = FRMMAINPAGE_BOL_Service::$item_count;

        $data = $friendsService->findUserFriendsInList($userId, 0, $count);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');

        $cmp = new FRMMAINPAGE_MCMP_FriendList('latest', $data, true);
        $this->addComponent('list', $cmp);
        $this->assign('listType', 'latest');

        if(OW::getPluginManager()->isPluginActive('frmadvancesearch')){
            $this->assign('find_friends_url', OW::getRouter()->urlForRoute('frmadvancesearch.list.users', array('type'=>'new')));
        }

        OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                'component' => 'FRMMAINPAGE_MCMP_FriendList',
                'listType' => 'latest',
                'excludeList' => $data,
                'node' => '.owm_user_list',
                'showOnline' => true,
                'count' => $count,
                'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.friends_responder')
            )) . ");
        ", 50);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticJsUrl() . 'frmmainpage.js');

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('friends');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function friends_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('friends_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? FRMMAINPAGE_BOL_Service::$item_count : (int)$_POST['count'];
        $start = count($excludeList);

        $userId = OW::getUser()->getId();
        $userService = FRIENDS_BOL_Service::getInstance();
        $data = $userService->findUserFriendsInList($userId, $start, $count);

        echo json_encode($data);
        exit;
    }

    public function userGroups($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('userGroups');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('groups')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('groups', 'group_list_menu_item_my'));
        $groupService = GROUPS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();
        $count = FRMMAINPAGE_BOL_Service::$item_count;

        $tplList = $groupService->findMyGroups($userId, 0, $count);
        $data = array();
        $parentTitleArr=array();
        foreach ($tplList as $key => $item) {
            $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['parentGroupId'=>isset($item->parentGroupId)? $item->parentGroupId : null]));
            $parentTitle=null;
            if(isset($eventPrepareGroup->getData()['parentData'])){
                $parentTitle = $eventPrepareGroup->getData()['parentData'];
            }
            $data[] = $item->id;
            $parentTitleArr[$item->id]=$parentTitle;
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');

        $cmp = new FRMMAINPAGE_MCMP_GroupList('latest', $data, true,$parentTitleArr);
        $this->addComponent('list', $cmp);
        $this->assign('listType', 'latest');

        if(count($tplList) >= $count) {
            OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                    'component' => 'FRMMAINPAGE_MCMP_GroupList',
                    'listType' => 'latest',
                    'excludeList' => $data,
                    'node' => '.owm_group_list',
                    'showOnline' => true,
                    'count' => $count,
                    'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.user.groups_responder')
                )) . ");
            ", 50);
        }

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('user-groups');
        $this->addComponent('menuCmp', $menuCmp);

        if(OW::getUser()->isAuthenticated() && GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate()){
            $this->assign('groupAddLink', OW::getRouter()->urlForRoute('groups-create'));
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function userGroups_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('userGroups_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? FRMMAINPAGE_BOL_Service::$item_count : (int)$_POST['count'];
        $start = count($excludeList);

        $userId = OW::getUser()->getId();
        if(!empty($_GET['type']) && in_array($_GET['type'],['chanel','group']))
        {
            $data = GROUPS_BOL_GroupDao::getInstance()->findByUserId($userId, $start, $count,null,null,true,null,'active',$_GET['type']);
        }else {
            $groupService = GROUPS_BOL_Service::getInstance();
            $data = $groupService->findMyGroups($userId, $start, $count);
        }

        echo json_encode($data);
        exit;
    }

    public function mailbox($params){
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('mailbox');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('mailbox')) {
            throw new Redirect404Exception();
        }
       OW::getDocument()->setHeading(OW::getLanguage()->text('mailbox', 'messages_console_title'));
        //--JS for loading
//        $js = "$('.owm_sidebar_top_block').append('<div id=\"console_preloader\"></div>');";
//        $js .= 'OW.bind(\'mailbox.ready\', function(readyStatus){ if (readyStatus == 2) $(\'.frmmainpage #console_preloader\').hide()})';
//        OW::getDocument()->addOnloadScript($js);
        //--

//        $cmp = new MAILBOX_MCMP_ConsoleConversationsPage();
//        $this->addComponent('cmp', $cmp);
        $activeModes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $currentSubMenu = 'mail';
        if(isset($params['type'])){
            $currentSubMenu = $params['type'];
        }else{
            if(in_array('mail', $activeModes)) {
                $currentSubMenu = 'mail';
            }
            if(in_array('chat', $activeModes)) {
                $currentSubMenu = 'chat';
            }
        }
        $conversationItemList = array();
        $userId = OW::getUser()->getId();
        $validLists = array();
        if(in_array('mail', $activeModes) && 'mail' == $currentSubMenu) {
            $conversationItemList = MAILBOX_BOL_ConversationDao::getInstance()->findConversationItemListByUserId($userId, array('mail'), 0, 1000);
        }

        if(in_array('chat', $activeModes) && 'chat' == $currentSubMenu) {
            $conversationItemList = MAILBOX_BOL_ConversationDao::getInstance()->findConversationItemListByUserId($userId, array('chat'), 0, 1000);
        }

        if(in_array('chat', $activeModes)) {
            $validLists[] = 'chat';
        }
        if(in_array('mail', $activeModes)) {
            $validLists[] = 'mail';
        }


        foreach($conversationItemList as $i => $conversation)
        {
            $conversationItemList[$i]['timeStamp'] = (int)$conversation['initiatorMessageTimestamp'];
            $conversationItemList[$i]['lastMessageSenderId'] = $conversation['initiatorMessageSenderId'];
            $conversationItemList[$i]['isSystem'] = $conversation['initiatorMessageIsSystem'];
            $conversationItemList[$i]['text'] = $conversation['initiatorText'];

            $conversationItemList[$i]['lastMessageId'] = $conversation['initiatorLastMessageId'];
            $conversationItemList[$i]['recipientRead'] = $conversation['initiatorRecipientRead'];
            $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['initiatorMessageRecipientId'];
            $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['initiatorMessageWasAuthorized'];
        }

        $conversationData = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdListForApi( $conversationItemList );
        $this->assign('conversationData', $conversationData);

        if(count($validLists)>1) {
            $subMenuItems = array();
            $order = 0;
            foreach ($validLists as $type) {
                $item = new BASE_MenuItem();
                $item->setLabel(OW::getLanguage()->text('frmmainpage', $type));
                $item->setUrl(OW::getRouter()->urlForRoute('frmmainpage.mailbox.type', array('type' => $type)));
                $item->setKey($type);
                $item->setOrder($order);
                array_push($subMenuItems, $item);
                $order++;
            }

            $subMenu = new BASE_MCMP_ContentMenu($subMenuItems);
            $el = $subMenu->getElement($currentSubMenu);
            $el->setActive(true);
            $this->addComponent('subMenu', $subMenu);
        }

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('mailbox');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addOnloadScript("add_mailbox_search_events('".OW::getRouter()->urlForRoute('frmmainpage.mailbox_responder')."')");
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticJsUrl() . 'frmmainpage.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $params
     * @throws Redirect404Exception
     */
    public function mailbox_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('mailbox_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $q = empty($_POST['q']) ? array() : UTIL_HtmlTag::stripTagsAndJs($_POST['q']);
        $userId = OW::getUser()->getId();

        $result = [];
        $convIds = [];
        $messageResults = MAILBOX_BOL_ConversationService::getInstance()->searchMessagesList($userId, $q);
        $avatarService = BOL_AvatarService::getInstance();

        foreach ($messageResults as $item){
           $item = FRMMAINPAGE_BOL_Service::getInstance()->prepareSearchedChatData($item);
            array_push($result, $item);
        }
        $titleResults = MAILBOX_BOL_ConversationService::getInstance()->searchMailTopicList($userId, $q);
        foreach ($titleResults as $obj){
            $item = [];
            $opponentId = $obj->initiatorId;
            if($opponentId == $userId){
                $opponentId = $obj->interlocutorId;
            }
            $convId = $obj->id;
            if(in_array($convId, $convIds)){
                continue;
            }
            $item['opponentId']=$opponentId;
            $item['avatarUrl']= BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);
            $item['opponentUrl']= BOL_UserService::getInstance()->getUserUrl($opponentId);
            $item['opponentName']= BOL_UserService::getInstance()->getDisplayName($opponentId);
            $item['text'] = $obj->subject;
            $item['timeString'] = UTIL_DateTime::formatDate((int)$item['lastMessageTimestamp'], true);
            $item['mode'] = MAILBOX_BOL_ConversationService::getInstance()->getConversationMode((int)$convId);
            if ($item['mode'] == 'chat') {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }else {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
            }
            array_push($result, $item);
        }

        $list=array("result"=>"ok", "q"=>$q, "results" => $result);
        echo json_encode($list);
        exit;
    }

    public function settings($params){
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('settings');
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmmainpage', 'settings'));
        $cmp = new BASE_MCMP_ConsoleProfilePage();
        $this->addComponent('cmp', $cmp);

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('settings');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function notifications($params){
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('notifications');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('notifications')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('base', 'notifications'));

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('notifications');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');

        $cmp = new BASE_MCMP_ConsoleNotificationsPage();
        $this->addComponent('cmp', $cmp);
    }

    public function photos($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('photos');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('photo') ||
            FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('photos')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmmainpage', 'public_photos'));
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $count = FRMMAINPAGE_BOL_Service::$item_count;

        $photoList = $photoService->findPhotoList( 'latest',1, $count);
        $data = array();
        foreach ($photoList as $item) {
            $data[] = $item['id'];
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');

        $cmp = new FRMMAINPAGE_MCMP_PhotoList('latest', $photoList);
        $this->addComponent('list', $cmp);

        if(count($photoList) >= $count) {
            OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                    'component' => 'FRMMAINPAGE_MCMP_PhotoList',
                    'listType' => 'latest',
                    'excludeList' => $data,
                    'node' => '.owm_photo_list',
                    'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.photos_responder')
                )) . ");
            ", 50);
        }

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('photos');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function photos_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('photos_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $count = FRMMAINPAGE_BOL_Service::$item_count;
        $page = ceil(count($excludeList)/$count)+1;
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $data = $photoService->findPhotoList( 'latest',$page, $count);

        echo json_encode($data);
        exit;
    }

    public function videos($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('videos');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('video') ||
            FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('videos')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('video', 'video'));
        $clipService = VIDEO_BOL_ClipService::getInstance();
        $count = FRMMAINPAGE_BOL_Service::$item_count;

        $clipList = $clipService->findClipsList('latest', 1, $count);
        $data = array();
        foreach ($clipList as $item) {
            $data[] = $item['id'];
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');

        $cmp = new FRMMAINPAGE_MCMP_VideoList('latest', $clipList);
        $this->addComponent('list', $cmp);
        $this->assign('listType', 'latest');

        if(count($clipList) >= $count) {
            OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                    'component' => 'FRMMAINPAGE_MCMP_VideoList',
                    'listType' => 'latest',
                    'excludeList' => $data,
                    'node' => '.owm_video_list',
                    'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.videos_responder')
                )) . ");
            ", 50);
        }

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('videos');
        $this->addComponent('menuCmp', $menuCmp);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
    }

    public function videos_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('videos_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $count = FRMMAINPAGE_BOL_Service::$item_count;
        $page = ceil(count($excludeList)/$count)+1;
        $clipService = VIDEO_BOL_ClipService::getInstance();
        $data = $clipService->findClipsList( 'latest',$page, $count);

        echo json_encode($data);
        exit;
    }



    private function checkDistinctChatChanelGroupPermission()
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('distinctChatChanelGroup');
        if (!$service->isPluginExist('distinctChatChanelGroup')) {
            throw new Redirect404Exception();
        }
        $activeModes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        if(!in_array('chat', $activeModes)) {
            throw new Redirect404Exception();
        }
    }


    private function prepareGroupOrChanelData($type,$userId,$count)
    {
        $tplList = GROUPS_BOL_GroupDao::getInstance()->findByUserId($userId, 0, $count,null,null,true,null,'active',$type);
        $data = array();
        $parentTitleArr=array();
        foreach ($tplList as $key => $item) {
            $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['parentGroupId'=>isset($item->parentGroupId)? $item->parentGroupId : null]));
            $parentTitle=null;
            if(isset($eventPrepareGroup->getData()['parentData'])){
                $parentTitle = $eventPrepareGroup->getData()['parentData'];
            }
            $data[] = $item->id;
            $parentTitleArr[$item->id]=$parentTitle;
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');
        $cmp = new FRMMAINPAGE_MCMP_GroupList('latest', $data, true,$parentTitleArr);
        $this->addComponent($type.'List', $cmp);
        $this->assign('listType', 'latest');

        $responderUrl = OW::getRequest()->buildUrlQueryString(
            OW::getRouter()->urlForRoute('frmmainpage.user.groups_responder'),['type'=>$type]);
        if(count($tplList) >= $count) {
            OW::getDocument()->addOnloadScript(
                "
            window.mobileUserList = new OW_UserList(".json_encode(
                    array(
                        'component' => 'FRMMAINPAGE_MCMP_GroupList',
                        'listType' => 'latest',
                        'excludeList' => $data,
                        'node' => '.owm_group_list',
                        'showOnline' => true,
                        'count' => $count,
                        'responderUrl' =>$responderUrl
                    )
                ).");
            ",
                50
            );
        }
        if(OW::getUser()->isAuthenticated() && GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate()){
            $url = $this->getCreateGroupChannelUrl($type);
            $this->assign('groupAddLink', $url);
        }
    }

    public function distinctChatChanelGroup($params){
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'distinctChatChannelGroup.css');
        $this->checkDistinctChatChanelGroupPermission();
        if( $params['list'] == 'chat' ){
            $this->assign('otpForm',true);
        }
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmmainpage', 'distinct_chat__chanel_groups_console_title'));

        if ( empty($params['list']) )
        {
            throw new Redirect404Exception();
        }

        $contentMenu = $service->getContentMenu();

        $menuCmp = new FRMMAINPAGE_MCMP_Menu('distinctChatChanelGroup');
        $this->addComponent('menuCmp', $menuCmp);

        $excludeData=array();
        $userId = OW::getUser()->getId();
        $count = FRMMAINPAGE_BOL_Service::$item_count;
        $this->assign('viewType',trim($params['list']));
        switch ( trim($params['list']) ) {
            case 'chat':
                $contentMenu->setItemActive('chat');
                $this->setPageHeading(OW::getLanguage()->text('mailbox', 'messages_console_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $conversationItemList = MAILBOX_BOL_ConversationDao::getInstance()->findConversationItemListByUserId($userId, array('chat'), 0, 1000);
                foreach($conversationItemList as $i => $conversation)
                {
                    $conversationItemList[$i]['timeStamp'] = (int)$conversation['initiatorMessageTimestamp'];
                    $conversationItemList[$i]['lastMessageSenderId'] = $conversation['initiatorMessageSenderId'];
                    $conversationItemList[$i]['isSystem'] = $conversation['initiatorMessageIsSystem'];
                    $conversationItemList[$i]['text'] = $conversation['initiatorText'];

                    $conversationItemList[$i]['lastMessageId'] = $conversation['initiatorLastMessageId'];
                    $conversationItemList[$i]['recipientRead'] = $conversation['initiatorRecipientRead'];
                    $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['initiatorMessageRecipientId'];
                    $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['initiatorMessageWasAuthorized'];
                }
                $conversationData = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdListForApi( $conversationItemList );
                $this->assign('conversationData', $conversationData);
                OW::getDocument()->addOnloadScript("add_mailbox_search_events('".OW::getRouter()->urlForRoute('frmmainpage.mailbox_responder')."')");
                break;
            case 'group':
                $contentMenu->setItemActive('group');
                OW::getDocument()->setHeading(OW::getLanguage()->text('groups', 'group_list_menu_item_my'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $this->prepareGroupOrChanelData('group',$userId,$count);
                break;
            case 'chanel':
                $contentMenu->setItemActive('chanel');
                $this->setPageHeading(OW::getLanguage()->text('frmmainpage', 'chanel_page_heading'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $this->prepareGroupOrChanelData('chanel',$userId,$count);
                break;
            default:
                $contentMenu->setItemActive('all');
                $this->setPageHeading(OW::getLanguage()->text('frmmainpage', 'all_page_heading'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $dataArr = FRMMAINPAGE_BOL_Service::getInstance()->findUserChatsAndGroups($userId,0,$count);
                $result = FRMMAINPAGE_BOL_Service::getInstance()->prepareChatGroupData($dataArr,$excludeData);
                $tplList=$result['tplList'];
                $excludeData=$result['excludeData'];
                $cmp = new FRMMAINPAGE_MCMP_ChatGroupList('',$tplList);
                $this->addComponent('cmp', $cmp);
                OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');
                if(count($dataArr) >= $count) {
                    OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                            'component' => 'FRMMAINPAGE_MCMP_ChatGroupList',
                            'listType' => 'latest',
                            'excludeList' => $excludeData,
                            'node' => '.owm_chat_group_list.owm_list_page .owm_list_item_parent',
                            'showOnline' => true,
                            'count' => $count,
                            'preloader' => '#chat_group-list-preloader',
                            'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.chatGroups_responder'),
                            'searchSelector' => '#frmmainpage_chat_group_search'
                        )) . ");
            ", 50);
                }

                OW::getDocument()->addOnloadScript("add_chat_group_search_events('".OW::getRouter()->urlForRoute('frmmainpage.chatGroups_responder')."')");
                $friendsService = FRIENDS_BOL_Service::getInstance();
                $data = $friendsService->findUserFriendsInList($userId, 0, $count);
                $userListData = json_encode(array(
                    'component' => 'FRMMAINPAGE_MCMP_NewChatGroupList',
                    'excludeList' => $data,
                    'node' => '#new-chat-group',
                    'showOnline' => true,
                    'count' => $count,
                    'componentWindow' => '#new-chat-group',
                    'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.friends_responder')
                ));

                $js ='$("#newsfeed-status-form-inv").click({userListData:'.$userListData.'},ShowNewChatGroupList);';
                OW::getDocument()->addOnloadScript($js);

                $this->assign("showCreate", false);
                if ( GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate() ){
                    $this->assign("showCreate", true);
                    $this->assign("createGroupUrl", OW::getRouter()->urlForRoute('groups-create'));
                }
                $this->addComponent('newChatGroupCmp', new FRMMAINPAGE_MCMP_NewChatGroupList(null, $data,true) );
        }

        $this->addComponent('contentMenu',  $service->getContentMenu());

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticJsUrl() . 'frmmainpage.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

    }



    public function chatsAndGroups($params){
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('chatsAndGroups');
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('mailbox') ||  !OW::getPluginManager()->isPluginActive('groups')) {
            throw new Redirect404Exception();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmmainpage', 'chat_groups_console_title'));


        $activeModes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        if(!in_array('chat', $activeModes)) {
            throw new Redirect404Exception();
        }
        $excludeData=array();
        $userId = OW::getUser()->getId();
        $count = FRMMAINPAGE_BOL_Service::$item_count;
        $dataArr = FRMMAINPAGE_BOL_Service::getInstance()->findUserChatsAndGroups($userId,0,FRMMAINPAGE_BOL_Service::$item_count);
        $result = FRMMAINPAGE_BOL_Service::getInstance()->prepareChatGroupData($dataArr,$excludeData);
        $tplList=$result['tplList'];
        $excludeData=$result['excludeData'];
        $cmp = new FRMMAINPAGE_MCMP_ChatGroupList('',$tplList);
        $this->addComponent('cmp', $cmp);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'mobile_user_list.js');

        if(count($dataArr) >= $count) {
            OW::getDocument()->addOnloadScript("
            window.mobileUserList = new OW_UserList(" . json_encode(array(
                    'component' => 'FRMMAINPAGE_MCMP_ChatGroupList',
                    'listType' => 'latest',
                    'excludeList' => $excludeData,
                    'node' => '.owm_chat_group_list.owm_list_page .owm_list_item_parent',
                    'showOnline' => true,
                    'count' => $count,
                    'preloader' => '#chat_group-list-preloader',
                    'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.chatGroups_responder'),
                    'searchSelector' => '#frmmainpage_chat_group_search'
                )) . ");
            ", 50);
        }

        $this->assign('otpForm',true);
        $menuCmp = new FRMMAINPAGE_MCMP_Menu('chatGroups');
        $this->addComponent('menuCmp', $menuCmp);
        OW::getDocument()->addOnloadScript("add_chat_group_search_events('".OW::getRouter()->urlForRoute('frmmainpage.chatGroups_responder')."')");
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticJsUrl() . 'frmmainpage.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmainpage')->getStaticCssUrl() . 'frmmainpage.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

        $friendsService = FRIENDS_BOL_Service::getInstance();

        $data = $friendsService->findUserFriendsInList($userId, 0, $count);
        $userListData = json_encode(array(
                'component' => 'FRMMAINPAGE_MCMP_NewChatGroupList',
                'excludeList' => $data,
                'node' => '#new-chat-group',
                'showOnline' => true,
                'count' => $count,
                'componentWindow' => '#new-chat-group',
                'responderUrl' => OW::getRouter()->urlForRoute('frmmainpage.friends_responder')
            ));

        $js ='$("#newsfeed-status-form-inv").click({userListData:'.$userListData.'},ShowNewChatGroupList);';
        OW::getDocument()->addOnloadScript($js);

        $this->assign("showCreate", false);
        if ( GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate() ){
            $this->assign("showCreate", true);
            $this->assign("createGroupUrl", OW::getRouter()->urlForRoute('groups-create'));
        }

        $this->addComponent('newChatGroupCmp', new FRMMAINPAGE_MCMP_NewChatGroupList(null, $data,true) );
    }

    public function chatGroups_responder($params)
    {
        $service = FRMMAINPAGE_BOL_Service::getInstance();
        $service->check_permission('chatGroups_responder');
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $q = empty($_POST['q']) ? null : UTIL_HtmlTag::stripTagsAndJs($_POST['q']);
        $excludeData = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $count = FRMMAINPAGE_BOL_Service::$item_count;
        $page = (int)ceil(count($excludeData)/$count)+1;
        $first = ( $page - 1 ) * $count;
        $userId=OW::getUser()->getId();
        $dataArr = FRMMAINPAGE_BOL_Service::getInstance()->findUserChatsAndGroups($userId,$first,FRMMAINPAGE_BOL_Service::$item_count, $q);
        $result = FRMMAINPAGE_BOL_Service::getInstance()->prepareChatGroupData($dataArr,$excludeData,$q);
        $tplList=$result['tplList'];
        echo json_encode(['tplList'=>$tplList,'last_q'=>$q,'excludeList'=>$result['excludeData'],'length'=>sizeof($tplList)]);
        exit;
    }

    /**
     * @param string $type
     * @return string $url
     */
    private function getCreateGroupChannelUrl($type)
    {
        $param = [];

        if ($type == 'chanel') {
            $param = ['ischanel' => 1];
        }

        return OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('groups-create'), $param);
    }

}