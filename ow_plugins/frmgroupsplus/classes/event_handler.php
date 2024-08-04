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
class FRMGROUPSPLUS_CLASS_EventHandler
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
        $eventManager->bind('frmgroupsplus.add_widget', array($service, 'addWidgetToOthers'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_USER_MANAGER_STATUS, array($service, 'setUserManagerStatus'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::CHECK_USER_MANAGER_STATUS, array($service, 'checkUserManagerStatus'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::DELETE_USER_AS_MANAGER, array($service, 'deleteUserAsManager'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::DELETE_FILES, array($service, 'deleteFiles'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_UPDATE_GROUP_STATUS, array($service, 'onUpdateGroupStatus'));
        $eventManager->bind('frmgroupsplus.delete_widget', array($service, 'deleteWidget'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($service, 'pluginDeactivate'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($service, 'pluginUninstall'));
        $eventManager->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::CHECK_CAN_INVITE_ALL, array($service, 'onCanInviteAll'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ADD_USERS_AUTOMATICALLY, array($service, 'addUsersAutomatically'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_CHANNEL_GROUP, array($service, 'setChannelGroup'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::SET_CHANNEL_FOR_GROUP, array($service, 'setChannelForGroup'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_CHANNEL_ADD_WIDGET, array($service, 'onChannelAddWidget'));
        $eventManager->bind(FRMGROUPSPLUS_BOL_Service::ON_CHANNEL_LOAD, array($service, 'onChannelLoad'));
        $eventManager->bind('groups.member_list_page_render', array($service, 'memberListPageRender'));

        $eventManager->bind('groups.invite_user',array($service,'onGroupUserInvitation'));
        $eventManager->bind('notifications.on_item_render', array($service, 'onNotificationRender'));
        $eventManager->bind('frmgroupsplus.is.group.channel', array($service, 'isGroupChannel'));
        $eventManager->bind('feed.on_item_render', array($service, 'feedOnItemRenderActivity'));
        $eventManager->bind('base.add_main_console_item', array($service, 'addConsoleItem'));

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
        $eventManager->bind('frmadvancesearch.on_collect_search_items',  array($service, 'onCollectSearchItems'));
        $eventManager->bind('frmgroupsplus.on.group.create.set.approve.setting', array($service, 'onGroupCreateCheckNeedApprove'));
        $eventManager->bind('Groups.After.Create', array($service, 'afterGroupCreateSendNotification'));
        $eventManager->bind('frmgroupsplus.on.group.load.check.status', array($service, 'checkAccessGroupBasedOnStatus'));
        $eventManager->bind('frmgroupsplus.add.approve.feature', array($service, 'AddApproveFeature'));
        $eventManager->bind('frmgroupsplus.after.group.create.approve.feedback', array($service, 'checkGroupApproveFeedback'));
        $eventManager->bind('frmgroupsplus.check.group.approve.status', array($service, 'checkGroupApproveStatusEvent'));
        $eventManager->bind('frmgroupsplus.on.get.groups.list.mobile', array($service, 'onGetGroupsListMobile'));
        $eventManager->bind('frmfilemanager.import_files', array($service, 'importFilesToFileWidget'));
        $eventManager->bind('frmfilemanager.check_privacy', array($service, 'checkPrivacyForFileWidget'));
        $eventManager->bind('frmfilemanager.after_entity_remove', array($service, 'afterFileEntityRemoved'));
        $eventManager->bind('frmgroupsplus.check.can.invite.all', array($service, 'onInviteAllUsers'));
        $eventManager->bind('frmgroupsplus.check.status.and.approve.setting.enable', array($service, 'checkGroupStatusApproveSettingEnableEvent'));

    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmgroupsplus' => array(
                    'label' => $language->text('frmgroupsplus', 'auth_frminvite_label'),
                    'actions' => array(
                        'all-search' => $language->text('frmgroupsplus', 'auth_action_label_all_search'),
                        'direct-add' => $language->text('frmgroupsplus', 'auth_action_label_direct_add'),
                        'add-forced-groups' => $language->text('frmgroupsplus', 'auth_action_label_add_forced_groups'),
                        'create_group_without_approval_need' => $language->text('frmgroupsplus', 'auth_action_label_create_group_without_approval_need')
                    )
                )
            )
        );
    }
}