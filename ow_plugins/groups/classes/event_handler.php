<?php
/**
 *
 * @package ow_plugins.groups.classes
 * @since 1.0
 */
class GROUPS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var GROUPS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GROUPS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var GROUPS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = GROUPS_BOL_Service::getInstance();
    }
    
    public function onAddNewContent( BASE_CLASS_EventCollector $event )
    {
        $uniqId = FRMSecurityProvider::generateUniqueId("groups-create-");
        
        if (!GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
            
            if ( $status['status'] != BOL_AuthorizationService::STATUS_PROMOTED )
            {
                return;
            }
            
            $script = UTIL_JsGenerator::composeJsString('$("#" + {$id}).click(function(){
                OW.authorizationLimitedFloatbox({$msg});
            });', array(
                "id" => $uniqId,
                "msg" => $status["msg"]
            ));
            OW::getDocument()->addOnloadScript($script);
        }
        
        $event->add(array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
            BASE_CMP_AddNewContent::DATA_KEY_ID => $uniqId,
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-create'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'add_new_label')
        ));
    }
    
    public function onBeforeGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = $params['groupId'];

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        $fileName = GROUPS_BOL_Service::getInstance()->getGroupImagePath($group);

        if ( $fileName !== null )
        {
            OW::getStorage()->removeFile($fileName);
        }
    }
    
    public function onAfterGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();

        $groupId = $params['groupId'];

        BOL_ComponentEntityService::getInstance()->onEntityDelete(GROUPS_BOL_Service::WIDGET_PANEL_NAME, $groupId);
        BOL_CommentService::getInstance()->deleteEntityComments(GROUPS_BOL_Service::ENTITY_TYPE_WAL, $groupId);

        BOL_FlagService::getInstance()->deleteByTypeAndEntityId(GROUPS_CLASS_ContentProvider::ENTITY_TYPE, $groupId);

        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId
        )));
    }
    
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        GROUPS_BOL_Service::getInstance()->onUserUnregister( $userId, !empty($params['deleteContent']) );
    }
    
    public function onForumCheckPermissions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] == 'groups' )
        {
            $groupService = GROUPS_BOL_Service::getInstance();
            $group = null;
            if (isset($params['info']['group_object'])) {
                $group = $params['info']['group_object'];
            }
            if ($group == null) {
                $group = $groupService->findGroupById($params['entityId']);
            }
            if ($group == null) {
                $event->setData(false);
            }else {
                if ($params['action'] == 'edit_topic') {
                    if ($group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized($params['entity']) || $groupService->isCurrentUserCanEdit($group)) {
                        $event->setData(true);
                    }
                } else if ($params['action'] == 'delete_topic') {
                    if ($group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized($params['entity']) || $groupService->isCurrentUserCanEdit($group)) {
                        $event->setData(true);
                    }
                } else if ($params['action'] == 'add_topic') {
                    $isUserInGroup = false;
                    $isUserManager = false;
                    $checkManager = true;
                    if (isset($params['additionalInfo']['currentUserIsMemberOfGroup']) && $params['additionalInfo']['entityId'] == $params['entityId']) {
                        $isUserInGroup = $params['additionalInfo']['currentUserIsMemberOfGroup'];
                    } else {
                        $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($params['entityId'], OW::getUser()->getId()) !== null;
                    }
                    if (isset($params['additionalInfo']['currentUserIsManager']) && $params['additionalInfo']['entityId'] == $params['entityId']) {
                        $isUserManager = $params['additionalInfo']['currentUserIsManager'];
                        $checkManager = false;
                    }
                    if (OW::getUser()->isAuthorized($params['entity'], 'add_topic') && ($isUserInGroup || $isUserManager || $groupService->isCurrentUserCanEdit($group, $checkManager))) {
                        $event->setData(true);
                    } else {

                        if ($groupService->findUser($params['entityId'], OW::getUser()->getId())) {
                            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params['entity'], 'add_topic');
                            if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED) {
                                $event->setData(true);
                                return;
                            }
                        }

                        $event->setData(false);
                    }
                } else if ($groupService->findUser($params['entityId'], OW::getUser()->getId())) {
                    $event->setData(true);
                } else {
                    $event->setData(false);
                }
            }
        }
    }
    
    public function onForumFindCaption( OW_Event $event )
    {

        $params = $event->getParams();
        if ( !isset($params['entity']) || !isset($params['entityId']) )
        {
            return;
        }

        if ( $params['entity'] == 'groups' && GROUPS_CMP_BriefInfoWidget::userAllowedAccess() )
        {
            $component = new GROUPS_CMP_BriefInfo($params['entityId']);
            $eventData['component'] = $component;
            $eventData['key'] = 'main_menu_list';
            $event->setData($eventData);
        }
    }
    
    public function onCollectAdminNotifications( BASE_CLASS_EventCollector $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

        if ( $is_forum_connected && !OW::getPluginManager()->isPluginActive('forum') )
        {
            $language = OW::getLanguage();

            $event->add($language->text('groups', 'error_forum_disconnected', array('plugins_url' => OW::getRouter()->urlForRoute('admin_plugins_installed'), 'groups_admin_url' => OW::getRouter()->urlForRoute('groups-admin-additional-features'))));
        }
    }
    
    public function onForumUninstall( OW_Event $event )
    {
        $config = OW::getConfig();

        if ( $config->getValue('groups', 'is_forum_connected') )
        {
            $event = new OW_Event('forum.delete_section', array('entity' => 'groups'));
            OW::getEventManager()->trigger($event);

            $event = new OW_Event('forum.delete_widget');
            OW::getEventManager()->trigger($event);

            $config->saveConfig('groups', 'is_forum_connected', 0);

            $actionId = BOL_AuthorizationActionDao::getInstance()->getIdByName('add_topic');

            BOL_AuthorizationService::getInstance()->deleteAction($actionId);
        }
    }
    
    public function onForumActivate( OW_Event $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

        // Add latest topic widget if forum plugin is connected
        if ( $is_forum_connected )
        {
            $event->setData(array('forum_connected' => true, 'place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_RIGHT));
        }
    }
    
    public function onAfterGroupCreate( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = (int) $params['groupId'];

        $event = new OW_Event('feed.action', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'groups',
        ));

        OW::getEventManager()->trigger($event);
    }
    
    public function onFeedEntityAction( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE )
        {
            return;
        }

        $groupId = (int) $params['entityId'];
        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);

        if ( $group === null )
        {
            return;
        }

        $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        $visibility = $private
                ? 4 + 8 // Visible for autor (4) and current feed (8)
                : 15; // Visible for all (15)

        $sentenceCorrected = false;
        if ( mb_strlen($group->description) > 500 )
        {
            $sentence = strip_tags($group->description);
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
        }
        if($sentenceCorrected){
            $content = $sentence.'...';
        }
        else{
            $content = UTIL_String::truncate(strip_tags($group->description), 500, "...");
        }
        $content = array(
            "format" => "image_content",
            "vars" => array(
                "imageId" => $group->id,
                "thumbnailId" => $group->id,
                "title" => UTIL_String::truncate(strip_tags($group->title), 200, '...'),
                "description" => $content,
                "url" => array( "routeName" => "groups-view", "vars" => array('groupId' => $group->id)),
                "iconClass" => "ow_ic_group"
            )
        );
        if(isset($params['isEdited']) && $params['isEdited']) {
            $actionFeed = NEWSFEED_BOL_ActionDao::getInstance()->findAction('group',$groupId);
            if (isset($actionFeed)) {
                $data = array(
                    'params' => array(
                        'feedType' => 'groups',
                        'feedId' => $groupId,
                        'visibility' => $visibility
                    ),
                    'ownerId' => $group->userId,
                    'time' => (int)$group->timeStamp,
                    'string' => array("key" => "groups+feed_create_string"),
                    'content' => $content,
                    'view' => array(
                        'iconClass' => 'ow_ic_files'
                    )
                );
            }

        }
        else{
            $data = array(
                'params' => array(
                    'feedType' => 'groups',
                    'feedId' => $groupId,
                    'visibility' => $visibility
                ),
                'ownerId' => $group->userId,
                'time' => (int)$group->timeStamp,
                'string' => array("key" => "groups+feed_create_string"),
                'content' => $content,
                'view' => array(
                    'iconClass' => 'ow_ic_files'
                )
            );
        }
        $e->setData($data);
    }
    
    public function onAfterGroupEdit( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $groupId = (int) $params['groupId'];

        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);
        $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;

        $event = new OW_Event('forum.edit_group', array('entity' => 'groups', 'entityId'=>$groupId, 'name'=>$group->title, 'description'=>$group->description));
        OW::getEventManager()->trigger($event);

        $event = new OW_Event('feed.action', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'groups',
            'isEdited' => TRUE
        ));

        OW::getEventManager()->trigger($event);

        if ( $private )
        {
            $users = $groupService->findGroupUserIdList($groupId);
            $follows = OW::getEventManager()->call('feed.get_all_follows', array(
                'feedType' => 'groups',
                'feedId' => $groupId
            ));

            foreach ( $follows as $follow )
            {
                if ( in_array($follow['userId'], $users) )
                {
                    continue;
                }

                OW::getEventManager()->call('feed.remove_follow', array(
                    'feedType' => 'groups',
                    'feedId' => $groupId,
                    'userId' => $follow['userId']
                ));
            }
            $groupService->removeFeedsOfPrivateGroupsByGroupId($groupId);
        }
    }
    
    public function onGroupUserJoin( OW_Event $e )
    {
        $params = $e->getParams();

        $groupId = (int) $params['groupId'];
        $userId = (int) $params['userId'];
        $groupUserId = (int) $params['groupUserId'];

        $groupService = GROUPS_BOL_Service::getInstance();
        $groupService->updateLastTimeStampOfGroup($groupId);
        $group = $groupService->findGroupById($groupId);

        if ( $group->userId == $userId )
        {
            return;
        }

        if ( isset($_POST['no-join-feed']) && $_POST['no-join-feed'] )
        {
            return;
        }

        // add subscriptions for every topic of group
        if (OW::getConfig()->configExists('frmforumplus', 'subscribe_group_users_to_topic') &&
            OW::getConfig()->getValue('frmforumplus', 'subscribe_group_users_to_topic') &&
            FRMSecurityProvider::checkPluginActive('forum', true)) {
            FORUM_BOL_SubscriptionService::getInstance()->addSubscriptionForAllGroupTopics($userId, $groupId);
        }
        // end of add subscriptions for every topic of group

        $url = $groupService->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $joinFeedString=true;
        if (OW::getConfig()->configExists('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed')){
            $fileUploadFeedValue= json_decode( OW::getConfig()->getValue('frmgroupsplus','groupFileAndJoinAndLeaveFeed') );
            if(!in_array('joinFeed',$fileUploadFeedValue)){
                $joinFeedString=false;
            }
        }
        if($joinFeedString){
            $data = array(
                'time' => time(),
                'string' => array(
                    "key" => 'groups+feed_join_string',
                    "vars" => array(
                        'groupTitle' => $title,
                        'groupUrl' => $url
                    )
                ),
                'view' => array(
                    'iconClass' => 'ow_ic_add'
                ),
                'data' => array(
                    'joinUsersId' => $userId
                )
            );

            $event = new OW_Event('feed.action', array(
                'feedType' => 'groups',
                'feedId' => $group->id,
                'entityType' => 'groups-join',
                'entityId' => $groupUserId,
                'pluginKey' => 'groups',
                'userId' => $userId,
                'visibility' => 8,
            ), $data);

            OW::getEventManager()->trigger($event);

        }
    }
    
    public function onFeedCollectWidgets( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'place' => 'group',
            'section' => BOL_ComponentService::SECTION_RIGHT,
            'order' => 0
        ));
    }
    
    public function onForumCollectWidgetPlaces( BASE_CLASS_EventCollector $e )
    {
        if ( OW::getConfig()->getValue('groups', 'is_forum_connected') )
        {
            $e->add(array(
                'place' => 'group',
                'section' => BOL_ComponentService::SECTION_RIGHT,
                'order' => 0
            ));
        }
    }
    
    public function onFeedWidgetConstruct( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['feedType'] != 'groups' )
        {
            return;
        }

        $data = $e->getData();

        $isManager = false;
        if (isset($params['additionalInfo']) && isset($params['additionalInfo']['currentUserIsManager'])) {
            $isManager = $params['additionalInfo']['currentUserIsManager'];
        }
        if ( !OW::getUser()->isAuthorized('groups') && !OW::getUser()->isAuthorized('groups', 'add_comment') && !OW::getUser()->isAdmin() && !$isManager )
        {
            $data['statusForm'] = false;
            $actionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'add_comment');
            
            if ( $actionStatus["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $data["statusMessage"] = $actionStatus["msg"];
            }
            
            $e->setData($data);

            return;
        }

        $groupId = (int) $params['feedId'];
        $group = null;
        if (isset($params['group'])) {
            $group = $params['group'];
        }

        if (isset($params['additionalInfo']['group']) && $params['additionalInfo']['group']->id == $groupId) {
            $group = $params['additionalInfo']['group'];
        }

        if ($group == null) {
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }

        $isUserInGroup = false;
        if (isset($params['additionalInfo']['currentUserIsMemberOfGroup']) && isset($params['additionalInfo']['entityId']) && $params['additionalInfo']['entityId'] == $groupId) {
            $isUserInGroup = $params['additionalInfo']['currentUserIsMemberOfGroup'];
        } else if (isset($params['additionalInfo']['currentUserIsMemberOfGroup']) && isset($params['additionalInfo']['group']) && $params['additionalInfo']['group']->id == $groupId) {
            $isUserInGroup = $params['additionalInfo']['currentUserIsMemberOfGroup'];
        } else {
            $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        }

        $data['statusForm'] = $isUserInGroup && $group->status == GROUPS_BOL_Group::STATUS_ACTIVE;

        $e->setData($data);
    }
    
    public function onGroupToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $e->getParams();
        $backUri = OW::getRequest()->getRequestUri();

        if ( OW::getEventManager()->call('feed.is_inited') )
        {
            $url = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'follow');

            $eventParams = array(
                'userId' => OW::getUser()->getId(),
                'feedType' => GROUPS_BOL_Service::ENTITY_TYPE_GROUP,
                'feedId' => $params['groupId']
            );
            $followCode = '';
            $unFollowCode='';
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['groupId'],'isPermanent'=>true,'activityType'=>'follow_group')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $followCode = $frmSecuritymanagerEvent->getData()['code'];
            }
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['groupId'],'isPermanent'=>true,'activityType'=>'unFollow_group')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $unFollowCode = $frmSecuritymanagerEvent->getData()['code'];
            }
            if ( !OW::getEventManager()->call('feed.is_follow', $eventParams) )
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('groups', 'feed_group_follow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'follow',
                        'code' =>$followCode)),
                    'class' => 'group_details_groups_feed_group_following_status',
                ));
            }
            else
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('groups', 'feed_group_unfollow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'unfollow',
                        'code' =>$unFollowCode)),
                    'class' => 'group_details_groups_feed_group_following_status',
                ));
            }
        }
    }
    
    public function onAdsCollectEnabledPlugins( BASE_CLASS_EventCollector $event )
    {
        $event->add('groups');
    }
    
    public function findAllGroupsUsers( OW_Event $e )
    {
        $out = GROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        $e->setData($out);

        return $out;
    }
    
    public function onFeedCollectFollow( BASE_CLASS_EventCollector $e )
    {
        $groupUsers = GROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        foreach ( $groupUsers as $groupId => $users )
        {
            foreach ( $users as $userId )
            {
                $e->add(array(
                    'feedType' => 'groups',
                    'feedId' => $groupId,
                    'userId' => $userId
                ));
            }
        }
    }
    
    public function onGroupUserJoinFeedAddFollow( OW_Event $event )
    {
        $params = $event->getParams();

        $groupId = $params['groupId'];
        $userId = $params['userId'];

        OW::getEventManager()->call('feed.add_follow', array(
            'feedType' => 'groups',
            'feedId' => $groupId,
            'userId' => $userId
        ));
    }
    
    public function onFeedStatusAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'groups-status' )
        {
            return;
        }

        $service = GROUPS_BOL_Service::getInstance();
        $group = $service->findGroupById($params['feedId']);
        $group->lastActivityTimeStamp = time();
        $service->saveGroup($group);
        $url = $service->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data['context'] = array(
            'label' => $title,
            'url' => $url
        );

        $data['contextFeedType'] = $params['feedType'];
        $data['contextFeedId'] = $params['feedId'];

        $event->setData($data);
    }
    
    public function onFeedItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $actionUserId = $userId = (int) $data['action']['userId'];
        if ( OW::getUser()->isAuthenticated() && in_array($params['feedType'], array('groups')) )
        {
            $groupDto = null;
            if (isset($params['group'])) {
                $groupDto = $params['group'];
            }
            if ($groupDto == null || $groupDto->id != $params['feedId']) {
                $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['feedId']);
            }
            $isGroupOwner = $groupDto->userId == OW::getUser()->getId();
            $isGroupModerator = OW::getUser()->isAuthorized('groups') || OW::getUser()->isAdmin();

            if ( $actionUserId != OW::getUser()->getId() && ($isGroupOwner || $isGroupModerator) )
            {
                $isMember = false;
                if (isset($params['cache']['users_groups']) && isset($params['cache']['users_groups'][$actionUserId])) {
                    if (in_array($groupDto->id, $params['cache']['users_groups'][$actionUserId])) {
                        $isMember = true;
                    }
                } else {
                    $groupUserDto = GROUPS_BOL_Service::getInstance()->findUser($groupDto->id, $actionUserId);
                    $isMember = $groupUserDto !== null;
                }
                if ( $isMember )
                {
                    $data['contextMenu'] = empty($data['contextMenu']) ? array() : $data['contextMenu'];


                    if ( $groupDto->userId == $userId )
                    {
                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('groups', 'delete_group_user_label'),
                            'url' => 'javascript://',
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('groups', 'group_owner_delete_error'),
                                'onclick' => 'OW.error($(this).data().message); return false;'
                            )
                        ));
                    }
                    else
                    {
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $urlParams = array(
                            'redirectUri' => urlencode($callbackUri)
                        );
                        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'deleteUser_group')));
                        if(isset($frmSecuritymanagerEvent->getData()['code'])){
                            $urlParams['code'] = $frmSecuritymanagerEvent->getData()['code'];

                        }
                        $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'deleteUser', array(
                            'groupId' => $groupDto->id,
                            'userId' => $userId
                        )),$urlParams );

                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('groups', 'delete_user_from_group'),
                            'url' => $deleteUrl,
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('groups', 'delete_group_user_confirmation'),
                                'onclick' => "return confirm_redirect($(this).data().message, '{$deleteUrl}');",
                                'class' => 'groups_delete_user'
                            )
                        ));
                    }
                }
            }

            $canRemove = $isGroupOwner || $params['action']['userId'] == OW::getUser()->getId() || $isGroupModerator;

            if ( $canRemove )
            {
                array_unshift($data['contextMenu'], array(
                    'label' => OW::getLanguage()->text('groups', 'delete_feed_item_label'),
                    'class' => 'newsfeed_remove_btn',
                    'attributes' => array(
                        'data-confirm-msg' => OW::getLanguage()->text('groups', 'delete_feed_item_confirmation')
                    )
                ));
            }
            if (OW::getUser()->getId() != $params['action']['userId'] &&
                !in_array(OW::getLanguage()->text('base', 'flag'), array_column($data['contextMenu'],'label'))) {
                array_unshift($data['contextMenu'], array(
                    'label' => OW::getLanguage()->text('base', 'flag'),
                    'attributes' => array(
                        'onclick' => 'OW.flagContent($(this).data().etype, $(this).data().eid)',
                        "data-etype" => $params['action']['entityType'],
                        "data-eid" => $params['action']['entityId'],
                        "class" => "groups_flag"
                    )
                ));
            }
        }

        $event->setData($data);
    }
    
    public function onFeedItemRenderContext( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ($params['feedType'] != 'groups')
        {
            return;
        }
        if ( in_array($params['action']['entityType'], ['groups-status']) )
        {
            $data['context'] = null;
        }

        $service = GROUPS_BOL_Service::getInstance();

        $group = null;
        if (isset($params['group'])) {
            $group = $params['group'];
        }

        // update URL
        if(isset($data["string"]["vars"]['groupUrl']) && isset($params["feedId"])){
            if ($group == null || $group->id != $params['feedId']) {
                $group = $service->findGroupById($params['feedId']);
            }
            $data["string"]["vars"]["groupUrl"] = GROUPS_BOL_Service::getInstance()->getGroupUrl($group);
        }

        // update group title
        if(isset($data["string"]["vars"]['groupTitle']) && isset($params["feedId"])){
            if ($group == null || $group->id != $params['feedId']) {
                $group = $service->findGroupById($params['feedId']);
            }
            $data["string"]["vars"]['groupTitle'] = $group->title;
            $event->setData($data);
        }

        if ( ($params['action']['entityType'] == 'forum-topic' || $params['action']['entityType'] =='groups-status') && isset($data['contextFeedType'])
                && $data['contextFeedType'] == 'groups' && $data['contextFeedType'] != $params['feedType'] )
        {
            if ($group == null || $group->id != $params['contextFeedId']) {
                $group = $service->findGroupById($data['contextFeedId']);
            }
            if($group) {
                $url = $service->getGroupUrl($group);
                $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

                $data['context'] = array(
                    'label' => $title,
                    'url' => $url
                );
            }
        }

        $event->setData($data);
    }
    
    /*public function onFeedItemRenderContext( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( empty($data['contextFeedType']) )
        {
            return;
        }
        
        if ( $data['contextFeedType'] != "groups" )
        {
            return;
        }
        
        if ( $params['feedType'] == "groups" )
        {
            $data["context"] = null;
            $event->setData($data);
            
            return;
        }
        
        $service = GROUPS_BOL_Service::getInstance();
        $group = $service->findGroupById($data['contextFeedId']);
        $url = $service->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data['context'] = array(
            'label' => $title,
            'url' => $url
        );

        $event->setData($data);
    }*/
    
    public function onFeedItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE)
        {
            return;
        }

        $groupService = GROUPS_BOL_Service::getInstance();
        $groupId = $params['action']['entityId'];
        $group = null;
        if (isset($params['cache']['groups'][$groupId])) {
            $group = $params['cache']['groups'][$groupId];
        }
        if ($group == null) {
            $group =  $groupService->findGroupById($groupId);
        }

        if(!isset($group))
        {
            return;
        }
        if(isset($data['content']['format']) && $data['content']['format'] == 'image_content'){
            $imageUrl = $groupService->getGroupImageUrl($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG);
            $data['content']['vars']['image'] = $imageUrl;

            $thumbnailUrl = $groupService->getGroupImageUrl($group);
            $data['content']['vars']['thumbnail'] = $thumbnailUrl;
        }

        $event->setData($data);

        $usersCount = GROUPS_BOL_Service::getInstance()->findUserListCount($groupId);

        if ( $usersCount == 1 )
        {
            return;
        }

        $users = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId, GROUPS_BOL_Service::PRIVACY_EVERYBODY);
        $activityUserIds = array();

        foreach ( $params['activity'] as $activity )
        {
            if ( $activity['activityType'] == 'groups-join')
            {
                $activityUserIds[] = $activity['data']['userId'];
            }
        }

        $lastUserId = reset($activityUserIds);
        $follows = array_intersect($activityUserIds, $users);
        $notFollows = array_diff($users, $activityUserIds);
        $idlist = array_merge($follows, $notFollows);

        $viewMoreUrl = null;
        
        if ( count($idlist) > 7 )
        {
            $viewMoreUrl = array("routeName" => "groups-user-list", "vars" => array(
                "groupId" => $groupId
            ));
        }
        
        if ( is_array($data["content"])  )
        {
            $data["content"]["vars"]["userList"] = array(
                "label" => array(
                    "key" => "groups+feed_activity_users",
                    "vars" => array(
                        "usersCount" => $usersCount
                    )
                ),
                "viewAllUrl" => $viewMoreUrl,
                "ids" => array_slice($idlist, 0, 7)
            );
        }
        else // Backward compatibility
        {
            $avatarList = new BASE_CMP_MiniAvatarUserList( array_slice($idlist, 0, 5) );
            $avatarList->setEmptyListNoRender(true);

            if ( count($idlist) > 7 )
            {
                $avatarList->setViewMoreUrl(OW::getRouter()->urlForRoute($viewMoreUrl["routeName"], $viewMoreUrl["vars"]));
            }

            $language = OW::getLanguage();
            $content = $avatarList->render();

            if ( $lastUserId )
            {
                $userName = BOL_UserService::getInstance()->getDisplayName($lastUserId);
                $userUrl = BOL_UserService::getInstance()->getUserUrl($lastUserId);
                $content .= $language->text('groups', 'feed_activity_joined', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
            }

            $data['assign']['activity'] = array('template' => 'activity', 'vars' => array(
                'title' => $language->text('groups', 'feed_activity_users', array('usersCount' => $usersCount)),
                'content' => $content
            ));
        }

        $event->setData($data);
    }
    
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'groups' => array(
                    'label' => $language->text('groups', 'auth_group_label'),
                    'actions' => array(
                        'add_topic' => $language->text('groups', 'auth_action_label_add_topic'),
                        'create' => $language->text('groups', 'auth_action_label_create'),
                        'view' => $language->text('groups', 'auth_action_label_view'),
                        'add_comment' => $language->text('groups', 'auth_action_label_wall_post'),
                        'delete_comment_by_content_owner' => $language->text('groups', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }
    
    public function onFeedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('groups', 'feed_content_label'),
            'activity' => array('*:' . GROUPS_BOL_Service::FEED_ENTITY_TYPE, '*:' . GROUPS_BOL_Service::GROUP_FEED_ENTITY_TYPE)
        ));
    }
    
    public function onPrivacyCollectActions( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $privacyValueEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD, array('key' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS)));
        $defaultValue = GROUPS_BOL_Service::PRIVACY_EVERYBODY;
        if(isset($privacyValueEvent->getData()['value'])){
            $defaultValue = $privacyValueEvent->getData()['value'];
        }
        $action = array(
            'key' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS,
            'pluginKey' => 'groups',
            'label' => $language->text('groups', 'privacy_action_view_my_groups'),
            'description' => $language->text('groups', 'privacy_action_view_my_groups_description'),
            'defaultValue' => $defaultValue,
            'sortOrder' => 1000
        );

        $event->add($action);
    }
    
    public function onFeedCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('groups-join:*', GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:groups-join', GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:' . GROUPS_BOL_Service::FEED_ENTITY_TYPE, GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
    }
    
    public function onPrivacyChange( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]) )
        {
            return;
        }

        GROUPS_BOL_Service::getInstance()->setGroupUserPrivacy($userId, $actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
        //GROUPS_BOL_Service::getInstance()->setGroupsPrivacy($userId, $actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
    }
    
    public function onBeforeUserJoin( OW_Event $event )
    {
        $data = $event->getData();
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $privacy = GROUPS_BOL_Service::PRIVACY_EVERYBODY;

        $t = OW::getEventManager()->call('plugin.privacy.get_privacy', array(
            'ownerId' => $params['userId'],
            'action' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS
        ));

        $data['privacy'] = empty($t) ? $privacy : $t;

        $event->setData($data);
    }
    
    public function onForumCanView( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] != 'groups' )
        {
            return;
        }


        $groupId = $params['entityId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        if ( empty($group) )
        {
            return;
        }

        $canView = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group, false);

        if ( $group->whoCanView != GROUPS_BOL_Service::WCV_INVITE )
        {
            $event->setData($canView);

            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $isUser = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId()) !== null;

        if ( !$isUser && !OW::getUser()->isAuthorized('groups') )
        {
            throw new Redirect404Exception();
        }
    }
    
    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $service = GROUPS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();

        $groupsCount = $service->findMyGroupsCount($userId);
        $invitesCount = $service->findUserInvitedGroupsCount($userId, true);

        if ( $groupsCount > 0 || $invitesCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'my_groups'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $groupsCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('groups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT => $invitesCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT_URL => OW::getRouter()->urlForRoute('groups-invite-list')
            ));
        }
    }

    public function onAfterFeedCommentAdd( OW_Event $event )
    {
        $params = $event->getParams();
        if($params['entityType'] != 'groups-status' && $params['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE){
            return;
        }
        $action = null;
        if (isset($params['action'])) {
            $action = $params['action'];
        }
        switch ( $params['entityType'] )
        {
            case 'groups-status':
                $groupId = null;
                if ($action == null) {
                    $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($params['entityType'], $params['entityId']);
                }
                if ( empty($action) )
                {
                    return;
                }

                $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findByActionIds(array($action->getId()));
                $activityIds = array();
                foreach ($activities as $activity) {
                    $activityIds[] = $activity->id;
                }
                $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids($activityIds);
                foreach ($activityIds as $activityId) {
                    if(isset($feedList[$activityId])) {
                        foreach ($feedList[$activityId] as $feed) {
                            if ($feed->feedType == 'groups') {
                                $groupId = $feed->feedId;
                            }
                        }
                    }
                }

                if($groupId != null) {
                    GROUPS_BOL_Service::getInstance()->updateLastTimeStampOfGroup($groupId);
                }

                OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                    'activityType' => 'comment',
                    'activityId' => $params['commentId'],
                    'entityId' => $params['entityId'],
                    'entityType' => $params['entityType'],
                    'userId' => $params['userId'],
                    'pluginKey' => 'groups',
                    'action_loaded' => $action,
                ), array(
                    'string' => array(
                        "key" => "groups+activity_string_status_comment"
                    ),
                    'time'=>time()
                )));

                break;

            case  GROUPS_BOL_Service::FEED_ENTITY_TYPE :

                GROUPS_BOL_Service::getInstance()->updateLastTimeStampOfGroup($params['entityId']);

                OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                    'activityType' => 'comment',
                    'activityId' => $params['commentId'],
                    'entityId' => $params['entityId'],
                    'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
                    'userId' => $params['userId'],
                    'action_loaded' => $action,
                    'pluginKey' => 'groups'
                ), array(
                    'string' => array(
                        "key" => "groups+comment_activity_string"
                    )
                )));
                break;
        }
    }
    
    public function cleanCache( OW_Event $event )
    {
        GROUPS_BOL_Service::getInstance()->clearListingCache();
    }
    
    public function sosialSharingGetGroupInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'groups' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(0, 'groups', 'view') )
            {
                $event->setData($data);
                return;
            }

            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['entityId']);
            
            if ( !empty($groupDto) )
            {
                $data['display'] = $groupDto->whoCanView !==  GROUPS_BOL_Service::WCV_INVITE;
            }
        }

        $event->setData($data);
    }

    
    public function afterUserLeave( OW_Event $event )
    {
        $params = $event->getParams();

        if(!isset($params["userIds"]) || !isset($params["groupId"]))
        {
            return;
        }

        $userIds = $params["userIds"];

        $eventParams = array(
            'userId' => $userIds,
            'feedType' => GROUPS_BOL_Service::ENTITY_TYPE_GROUP,
            'feedId' => $params["groupId"]
        );

        OW::getEventManager()->call('feed.remove_follow', $eventParams);

        // delete subscriptions
        $eventParams = array(
            'userIds' => $userIds,
            'groupId' => $params["groupId"]
        );
        OW::getEventManager()->call('group.forums.topics.unsubscribe', $eventParams);
    }
    
    /**
     * Get sitemap urls
     *
     * @param OW_Event $event
     * @return void
     */
    public function onGroupContentUpdate( OW_Event $event )
    {
        $params = $event->getParams();
        if(isset($params['groupId'])){
            $service = GROUPS_BOL_Service::getInstance();
            $service->updateLastTimeStampOfGroup($params['groupId']);
        }
    }

    /**
     * Get sitemap urls
     *
     * @param OW_Event $event
     * @return void
     */
    public function onSitemapGetUrls( OW_Event $event )
    {
        $params = $event->getParams();

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('groups', 'view') )
        {
            $offset = (int) $params['offset'];
            $limit  = (int) $params['limit'];
            $urls   = array();

            switch ( $params['entity'] )
            {
                case 'groups_authors' :
                    $usersIds  = GROUPS_BOL_Service::getInstance()->findLatestGroupAuthorsIds($offset, $limit);
                    $userNames = BOL_UserService::getInstance()->getUserNamesForList($usersIds);

                    // skip deleted users
                    foreach ( array_filter($userNames) as $userName )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('groups-user-groups', array(
                            'user' => $userName
                        ));
                    }
                    break;

                case 'groups_user_list' :
                    $groups = GROUPS_BOL_Service::getInstance()->findLatestPublicGroupListIds($offset, $limit);

                    foreach ( $groups as $groupId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('groups-user-list', array(
                            'groupId' => $groupId
                        ));
                    }
                    break;

                case 'groups' :
                    $groups = GROUPS_BOL_Service::getInstance()->findLatestPublicGroupListIds($offset, $limit);

                    foreach ( $groups as $groupId )
                    {
                        $urls[] = OW::getRouter()->urlForRoute('groups-view', array(
                            'groupId' => $groupId
                        ));
                    }
                    break;

                case 'groups_list' :
                    $urls[] = OW::getRouter()->urlForRoute('groups-index');
                    $urls[] = OW::getRouter()->urlForRoute('groups-most-popular');
                    $urls[] = OW::getRouter()->urlForRoute('groups-latest');
                    break;
            }

            if ( $urls )
            {
                $event->setData($urls);
            }
        }
    }

    public function onCollectMetaData( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();

        $items = array(
            array(
                "entityKey" => "mostPopular",
                "entityLabel" => $language->text("groups", "seo_meta_most_popular_label"),
                "iconClass" => "ow_ic_heart",
                "langs" => array(
                    "title" => "groups+meta_title_most_popular",
                    "description" => "groups+meta_desc_most_popular",
                    "keywords" => "groups+meta_keywords_most_popular"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "latest",
                "entityLabel" => $language->text("groups", "seo_meta_latest_label"),
                "iconClass" => "ow_ic_clock",
                "langs" => array(
                    "title" => "groups+meta_title_latest",
                    "description" => "groups+meta_desc_latest",
                    "keywords" => "groups+meta_keywords_latest"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "userGroups",
                "entityLabel" => $language->text("groups", "seo_meta_user_groups_label"),
                "iconClass" => "ow_ic_clock",
                "langs" => array(
                    "title" => "groups+meta_title_user_groups",
                    "description" => "groups+meta_desc_user_groups",
                    "keywords" => "groups+meta_keywords_user_groups"
                ),
                "vars" => array("site_name")
            ),
            array(
                "entityKey" => "groupPage",
                "entityLabel" => $language->text("groups", "seo_meta_groups_page_label"),
                "iconClass" => "ow_ic_groups",
                "langs" => array(
                    "title" => "groups+meta_title_groups_page",
                    "description" => "groups+meta_desc_groups_page",
                    "keywords" => "groups+meta_keywords_groups_page"
                ),
                "vars" => array("site_name", "group_title", "group_description")
            ),
            array(
                "entityKey" => "groupUsers",
                "entityLabel" => $language->text("groups", "seo_meta_group_users_label"),
                "iconClass" => "ow_ic_groups",
                "langs" => array(
                    "title" => "groups+meta_title_group_users",
                    "description" => "groups+meta_desc_group_users",
                    "keywords" => "groups+meta_keywords_group_users"
                ),
                "vars" => array("site_name", "group_name")
            ),
        );

        foreach ($items as &$item)
        {
            $item["sectionLabel"] = $language->text("groups", "seo_meta_section");
            $item["sectionKey"] = "groups";
            $e->add($item);
        }
    }

    public function checkPrivateGroups(){
        if(OW::getConfig()->configExists('groups', 'check_all_private_groups') && OW::getConfig()->getValue('groups', 'check_all_private_groups')){
            OW::getConfig()->saveConfig('groups', 'check_all_private_groups', false);
            GROUPS_BOL_Service::getInstance()->removeFeedsOfPrivateGroups();
        }
    }

    public function onCollectSearchItems(OW_Event $event){
        if (!OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups', 'view'))
        {
            return;
        }
        $params = $event->getParams();
        $selected_section = null;
        if(!empty($params['selected_section']))
            $selected_section = $params['selected_section'];
        if( isset($selected_section) && $selected_section != OW_Language::getInstance()->text('frmadvancesearch','all_sections') && $selected_section!= OW::getLanguage()->text('frmadvancesearch', 'groups_label') )
            return;
        $searchValue = '';
        if ( !empty($params['q']) )
        {
            $searchValue = $params['q'];
        }
        $searchValue = strip_tags(UTIL_HtmlTag::stripTags($searchValue));
        $maxCount = empty($params['maxCount'])?10:$params['maxCount'];
        $first= empty($params['first'])?0:$params['first'];
        $first=(int)$first;
        $count=empty($params['count'])?$first+$maxCount:$params['count'];
        $count=(int)$count;

        $result = array();

        if ( !empty($params['nativeMobile']) )
        {
            $nativeMobile = $params['nativeMobile'];
        }else{
            $nativeMobile = false;
        }
        $isNativeAdminOrGroupModerator = false;
        $eventCheckNativeAccess = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.get.groups.list.mobile', array('nativeMobile' => $nativeMobile)));
        if (isset($eventCheckNativeAccess->getData()['isNativeAdminOrGroupModerator'])) {
            $isNativeAdminOrGroupModerator = $eventCheckNativeAccess->getData()['isNativeAdminOrGroupModerator'];
        }
        $groups = array();
        $usersCountList = array();
        if (!isset($params['do_query']) || $params['do_query']) {
            $groups = GROUPS_BOL_Service::getInstance()->findGroupsByFiltering(false, 'active', true,
                $first, $count, null, null, $searchValue, null, $isNativeAdminOrGroupModerator);
            $groupIdList = array();
            foreach ($groups as $item) {
                $groupIdList[] = $item->id;
            }
            $usersCountList = GROUPS_BOL_GroupUserDao::getInstance()->findCountByGroupIdList($groupIdList);
        }
        $count = 0;
        $userIdList = array_column($groups, 'userId');
        $userIdListUnique = array_unique($userIdList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdListUnique);
        $userUrls = BOL_UserService::getInstance()->getUserUrlsForList($userIdListUnique);
        foreach($groups as $item){
            /* @var $item GROUPS_BOL_Group */
            $itemInformation = array();
            $itemInformation['title'] = $item->title;
            $itemInformation['id'] = $item->id;
            $userId = $item->userId;
            $itemInformation['userId'] = $userId;
            $itemInformation['displayName'] = $displayNames[$userId];
            $itemInformation['userUrl'] = $userUrls[$userId];
            $itemInformation['createdDate'] = $item->timeStamp;
            $itemInformation['usersCount'] = $usersCountList[$item->id];
            $itemInformation['link'] = GROUPS_BOL_Service::getInstance()->getGroupUrl($item);
            $itemInformation['label'] = OW::getLanguage()->text('frmadvancesearch', 'groups_label');
            if(isset($item->imageHash)){
                $itemInformation['image'] = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($item);
            } else {
                $itemInformation['emptyImage'] = true;
                $itemInformation['image'] = GROUPS_BOL_Service::getInstance()->generateDefaultImageUrl();
            }
            $itemInformation['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo((int) $item->id, $item->imageHash, 'group');
            $itemInformation['status'] = $item->status;
            $result[] = $itemInformation;
            $count++;
            if($count == $maxCount){
                break;
            }
        }

        $data = $event->getData();
        $data['groups']= array('label' => OW::getLanguage()->text('frmadvancesearch', 'groups_label'), 'data' => $result);
        $event->setData($data);
    }
    public function editNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if($params['pluginKey']!='groups' || $params['entityType']!='user_invitation' )
            return;
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($data['groupId']);
        if(!isset($group)) {
            $event->setData(null);
        }else {
            $userId = $data['avatar']['userId'];
            $userService = BOL_UserService::getInstance();
            $user = $userService->findUserById($userId);
            $userName = $user->getUsername();
            $data['avatar']['urlInfo']['vars']['username'] = $userName;
            $displayName = $userService->getDisplayNamesForList(array($userId));
            $inviterUrl = $userService->getUserUrlsForList(array($userId));
            $groupUrl = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $group->id));
            $data['string']= array(
                    'key' => 'frmgroupsplus+group_user_invitation_notification',
                    'vars' => array(
                        'userName' => $displayName,
                        'userUrl' => $inviterUrl,
                        'groupTitle' => $group->title,
                        'groupUrl' => $groupUrl
                    )
                );
            $event->setData($data);
        }
    }

    public function newsfeedUpdateStatusFrom(OW_Event $event){
        $params = $event->getParams();
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        $actionKey=$attr[OW_RequestHandler::ATTRS_KEY_ACTION];
        if ((!isset($params['feedType']) || $params['feedType'] != 'groups') && $actionKey != "dashboard")
            return;
        $data = $event->getData();
        $field = new HiddenField('reply_to');
        $data['elements'][] = $field;
        $event->setData($data);

        $js = "
            function clearPostReply(){
                $('.reply_to').remove();
                $('form[name=newsfeed_update_status] input[name=reply_to]').attr('value', '');
            }
            function addPostReplyTo(actionId, text){
                clearPostReply();
                $('form[name=newsfeed_update_status] input[name=reply_to]').attr('value', actionId);
                $('form[name=newsfeed_update_status] .ow_status_update_btn_block').prepend('<div class=\"reply_to reply_to_text ow_ic_delete\">'+text+'</div>');
                $('form[name=newsfeed_update_status] textarea[name=status]').trigger('focus');
                $('.reply_to_text').on('click',clearPostReply);
                owForms['newsfeed_update_status'].bind( 'success', clearPostReply);
            }
            ";
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'groups')
            return;

        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];
        $groupService = GROUPS_BOL_Service::getInstance();
        //user_invitation
        if ($entityType == 'user_invitation') {
            $group = null;
            if (isset($params['cache']['groups'][$entityId])) {
                $group = $params['cache']['groups'][$entityId];
            }
            if ($group == null) {
                $group = $groupService->findGroupById($entityId);
            }
            if (isset($group)) {
                $notificationData["string"]["vars"]["groupTitle"] = $group->title;
            } else
                $notificationData = null;
        } elseif ($entityType == 'groups-status') {
            if (FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                $action = null;
                if (isset($params['cache']['actions_by_entity'][$entityType . '-' . $entityId])) {
                    $action = $params['cache']['actions_by_entity'][$entityType . '-' . $entityId];
                }
                if ($action == null) {
                    $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
                }
                if (isset($action)) {
                    $data = json_decode($action->data, true);
                    $group = null;
                    if (isset($data['contextFeedId'])) {
                        $groupId = $data['contextFeedId'];
                        if (isset($params['cache']['groups'][$groupId])) {
                            $group = $params['cache']['groups'][$groupId];
                        }
                        if ($group == null) {
                            $group = $groupService->findGroupById($groupId);
                        }
                    }
                    if ($group != null) {
                        $notificationData["string"]["vars"]["groupTitle"] = UTIL_String::truncate($group->title, 60, '...');
                    }
                    if (isset($data['status'])) {
                        $notificationData["string"]["vars"]["status"] = $data['status'];
                    }
                }
            }
        } elseif ($entityType == 'groups-add-file') {
            if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $groupFile = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findById($entityId);
                if (isset($groupFile)) {
                    $groupId = $groupFile->groupId;
                    $group = null;
                    if (isset($params['cache']['groups'][$groupId])) {
                        $group = $params['cache']['groups'][$groupId];
                    }
                    if ($group == null) {
                        $group = $groupService->findGroupById($groupId);
                    }
                    if (isset($group)) {
                        $notificationData["string"]["vars"]["groupTitle"] = UTIL_String::truncate($group->title, 60, '...');
                        $notificationData["string"]["vars"]["fileName"] = UTIL_String::truncate($notificationData["string"]["vars"]["fileName"], 120, '...');
                    }
                }
            }
        } elseif ($entityType == 'groups_wal') {
            $group = null;
            if (isset($params['cache']['groups'][$entityId])) {
                $group = $params['cache']['groups'][$entityId];
            }
            if ($group == null) {
                $group = $groupService->findGroupById($entityId);
            }
            if (isset($group)) {
                $notificationData["string"]["vars"]["groupTitle"] = UTIL_String::truncate($group->title, 60, '...');
                $notificationData["string"]["vars"]["comment"] = UTIL_String::truncate($notificationData["content"], 120, '...');
            }
        }

        $event->setData($notificationData);
    }

    public function genericInit()
    {
        $eventHandler = $this;
        
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_ON_DELETE, array($eventHandler, "onBeforeGroupDelete"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "onAfterGroupDelete"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "onAfterGroupCreate"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_DELETED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "onAfterGroupEdit"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoin"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoinFeedAddFollow"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_BEFORE_ADDED, array($eventHandler, "onBeforeUserJoin"));
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(!isset($event->getData()['isMobileVersion']) || $event->getData()['isMobileVersion']==false){
            OW::getEventManager()->bind('groups.on_toolbar_collect', array($eventHandler, "onGroupToolbarCollect"));
        }
        OW::getEventManager()->bind('groups.get_all_group_users', array($eventHandler, "findAllGroupsUsers"));
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(!isset($event->getData()['isMobileVersion']) || $event->getData()['isMobileVersion']==false) {
            OW::getEventManager()->bind('feed.collect_follow', array($eventHandler, "onFeedCollectFollow"));
        }
        OW::getEventManager()->bind('feed.on_entity_action', array($eventHandler, "onFeedEntityAction"));
        OW::getEventManager()->bind('feed.collect_privacy', array($eventHandler, "onFeedCollectPrivacy"));
        OW::getEventManager()->bind('feed.on_entity_add', array($eventHandler, "onFeedStatusAdd"));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($eventHandler, "onFeedCollectConfigurableActivity"));
        OW::getEventManager()->bind('feed.after_comment_add', array($eventHandler, "onAfterFeedCommentAdd"));
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderActivity"));
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderContext"));

        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($eventHandler, "onPrivacyCollectActions"));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($eventHandler, "onPrivacyChange"));

        OW::getEventManager()->bind('forum.check_permissions', array($eventHandler, "onForumCheckPermissions"));
        OW::getEventManager()->bind('forum.can_view', array($eventHandler, 'onForumCanView'));

        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($eventHandler, "onUserUnregister"));
        OW::getEventManager()->bind('ads.enabled_plugins', array($eventHandler, "onAdsCollectEnabledPlugins"));
        OW::getEventManager()->bind('admin.add_auth_labels', array($eventHandler, "onCollectAuthLabels"));
        OW::getEventManager()->bind('notifications.on_item_render',  array($this, 'editNotification'));


        OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'onAddNewContent'));
        OW::getEventManager()->bind('frmgroupsplus.on_delete_user', array(GROUPS_BOL_Service::getInstance(), "onGroupUserLeave"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_DELETED, array($eventHandler, "afterUserLeave"));
        
        OW::getEventManager()->bind("base.sitemap.get_urls", array($this, 'onSitemapGetUrls'));
        OW::getEventManager()->bind("groups.group.content.update", array($this, 'onGroupContentUpdate'));
        OW::getEventManager()->bind("feed.before_action_delete", array(GROUPS_BOL_Service::getInstance(), 'onGroupDeletePostRemoveNotificationHandler'));
        OW::getEventManager()->bind('groups.groups.status.flag.changer', array(GROUPS_BOL_Service::getInstance(),'groupStatusFlagRenderer'));
        OW::getEventManager()->bind('feed.after_like_added', array(GROUPS_BOL_Service::getInstance(), 'onLikeNotification'));
        OW::getEventManager()->bind('groups.set_user_as_owner', array(GROUPS_BOL_Service::getInstance(), 'setUserAsOwner'));
        $this->checkPrivateGroups();

        OW::getEventManager()->bind('newsfeed.generic_item_render', array(GROUPS_BOL_Service::getInstance(), 'genericItemRender'));
        OW::getEventManager()->bind('newsfeed.feed.render', array(GROUPS_BOL_Service::getInstance(), 'newsfeedFeedRender'));
        OW::getEventManager()->bind('newsfeed.widget.feed.params', array(GROUPS_BOL_Service::getInstance(), 'newsfeedWidgetFeedParams'));
        OW::getEventManager()->bind('notification.get_edited_data', array($this, 'getEditedDataNotification'));
        OW::getEventManager()->bind('newsfeed.on.delete.feed', array(GROUPS_BOL_Service::getInstance(), 'onDeleteFeed'));
        OW::getEventManager()->bind('entityType.check.access.update.status',  array(GROUPS_BOL_Service::getInstance(), 'checkAccessUpdateStatus'));
        OW::getEventManager()->bind('search.additional.parameter',  array(GROUPS_BOL_Service::getInstance(), 'searchAdditionalParameters'));

    }
}