<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.bol
 * @since 1.0
 */
class FRMGROUPSPLUS_MCLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private function __construct()
    {
    }
    
    public function init()
    {
        if( !FRMSecurityProvider::checkPluginActive('groups', true) ){
            return;
        }
        $service = FRMGROUPSPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array($service, 'getResultForListItemGroup'));
        $eventManager->bind(FRMEventManager::ADD_GROUP_FILTER_FORM, array($service, 'addGroupFilterForm'));
        $eventManager->bind(FRMEventManager::ADD_GROUP_FILTER_ELEMENT, array($service, 'addNewElementsToGroupForm'));
        $eventManager->bind(FRMEventManager::GET_GROUP_SELECTED_CATEGORY_ID, array($service, 'getGroupSelectedCategoryId'));
        $eventManager->bind(FRMEventManager::ADD_CATEGORY_TO_GROUP, array($service, 'addCategoryToGroup'));
        $eventManager->bind(FRMEventManager::GET_GROUP_SELECTED_CATEGORY_LABEL, array($service, 'getGroupSelectedCategoryLabel'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_MOBILE_USER_MANAGER_STATUS, array($service, 'setMobileUserManagerStatus'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::CHECK_USER_MANAGER_STATUS, array($service, 'checkUserManagerStatus'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::DELETE_USER_AS_MANAGER, array($service, 'deleteUserAsManager'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::DELETE_FILES, array($service, 'deleteFiles'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ADD_FILE_WIDGET, array($service, 'addFileWidget'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::PENDING_USERS_COMPONENT, array($service, 'addPendingUsersList'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_UPDATE_GROUP_STATUS, array($service, 'onUpdateGroupStatus'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::CHECK_CAN_INVITE_ALL, array($service, 'onCanInviteAll'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ADD_USERS_AUTOMATICALLY, array($service, 'addUsersAutomatically'));
        $eventManager->bind('groups.member_list_page_render', array($service, 'memberListPageRender'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_CHANNEL_GROUP, array($service, 'setChannelGroup'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_CHANNEL_FOR_GROUP, array($service, 'setChannelForGroup'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_CHANNEL_ADD_WIDGET, array($service, 'onChannelAddWidget'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_CHANNEL_LOAD, array($service, 'onChannelLoad'));
        $eventManager->bind('frmgroupsplus.is.group.channel', array($service, 'isGroupChannel'));
        $eventManager->bind('groups.invite_user',array($service,'onGroupUserInvitation'));
        $eventManager->bind('feed.on_item_render', array($service, 'feedOnItemRenderActivity'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        $eventManager->bind('groups.before.user.leave', array($service, 'onBeforeUserLeave'));
        $eventManager->bind('base_add_comment', array($service, 'onCommentNotification'));
        $eventManager->bind('base_delete_comment', array($service, 'deleteComment'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'onUnregisterUser'));
        $eventManager->bind('add.group.setting.elements', array($service, 'addGroupSettingElements'));
        $eventManager->bind('set.group.setting', array($service, 'setGroupSetting'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::DELETE_FILES, array($service, 'deleteGroupSetting'));
        $eventManager->bind('can.create.topic', array($service, 'canCreateTopic'));
        $eventManager->bind('can.upload.in.file.widget', array($service, 'canUploadInFileWidget'));
        $eventManager->bind('frmgroupsplus.on.group.create.set.approve.setting', array($service, 'onGroupCreateCheckNeedApprove'));
        $eventManager->bind('frmgroupsplus.on.group.load.check.status', array($service, 'checkAccessGroupBasedOnStatus'));
        $eventManager->bind('frmgroupsplus.add.approve.feature', array($service, 'AddApproveFeature'));
        $eventManager->bind('frmgroupsplus.check.group.approve.status', array($service, 'checkGroupApproveStatusEvent'));
        $eventManager->bind('frmfilemanager.check_privacy', array($service, 'checkPrivacyForFileWidget'));
        $eventManager->bind('frmfilemanager.after_entity_remove', array($service, 'afterFileEntityRemoved'));
        $eventManager->bind('frmgroupsplus.check.can.invite.all', array($service, 'onInviteAllUsers'));
        $eventManager->bind('frmgroupsplus.check.status.and.approve.setting.enable', array($service, 'checkGroupStatusApproveSettingEnableEvent'));
    }


    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'groups'|| ($params['entityType'] != 'groups-add-file' && $params['entityType'] != 'groups-status' && $params['entityType']!='user_invitation' && $params['entityType']!='groups-join' && $params['entityType'] != 'photo_comments' && $params['entityType'] != 'multiple_photo_upload' && $params['entityType'] != 'groups-update-status' && $params['entityType'] != 'group_approve'))
        {
            return;
        }

        if($params['entityType'] == 'user_invitation'){
            $data = $params['data'];
            $e->setData($data);
            return;
        }

        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        //Notification on click logic is set here
        $event = new OW_Event('mobile.notification.data.received', array('pluginKey' => $params['pluginKey'],
            'entityType' => $params['entityType'],
            'data' => $data));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['url'])){
            $data['url']=$event->getData()['url'];
        }
        
        $userService = BOL_UserService::getInstance();

        $user = null;
        if (isset($params['cache']['users']['username'][$data['avatar']['urlInfo']['vars']['username']])) {
            $user = $params['cache']['users']['username'][$data['avatar']['urlInfo']['vars']['username']];
        }

        if ($user == null) {
            $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        }

        if ( !$user )
        {
            return;
        }
        $e->setData($data);
    }
}