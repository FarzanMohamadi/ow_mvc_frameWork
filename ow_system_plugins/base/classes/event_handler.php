<?php
/**
 * @package ow_core
 * @since 1.0
 */
class BASE_CLASS_EventHandler
{

    public function genericInit()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_USER_LOGIN, array($this, 'onUserLoginSaveStatistics'));
        $eventManager->bind(BOL_ContentService::EVENT_AFTER_ADD, array($this, 'onAfterAdd'));
        $eventManager->bind('base.add_global_lang_keys', array($this, 'onAddGlobalLangs'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteUserContent'));
        $eventManager->bind(OW_EventManager::ON_USER_LOGIN, array($this, 'onUserLogin'));
        $eventManager->bind(OW_EventManager::ON_USER_LOGOUT, array($this, 'onUserLogout'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onJoinMandatoryUserApprove'));
        $eventManager->bind(OW_EventManager::ON_AFTER_USER_COMPLETE_PROFILE, array($this, 'onJoinMandatoryUserApprove'));
        $eventManager->bind(OW_EventManager::ON_USER_EDIT, array($this, 'onUserEditFeed'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onJoinFeed'));
        $eventManager->bind('feed.after_comment_add', array($this, 'onUserJoinCommentFeed'));
        $eventManager->bind('feed.after_like_added', array($this, 'onLikeUserJoin'));
        $eventManager->bind('feed.after_like_added', array($this, 'onUserAvatarLikeFeed'));
        $eventManager->bind('feed.after_comment_add', array($this, 'onUserAvatarCommentFeed'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onUserRegisterWelcomeLetter'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregisterDeleteDisaproved'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $eventManager->bind('base_add_comment', array($this, 'onAddComment'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregisterRemovePreference'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteMediaPanelFiles'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_SUSPEND, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_SUSPEND, array($this, 'sendSuspendNotification'));
        $eventManager->bind(OW_EventManager::ON_USER_UNSUSPEND, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_APPROVE, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_DISAPPROVE, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_MARK_FEATURED, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_UNMARK_FEATURED, array($this, 'clearUserListQueryCache'));
        $eventManager->bind('base.questions_field_get_label', array($this, 'getQuestionLabel'));
        $eventManager->bind('base.before_decorator', array($this, 'onBeforeDecoratorRender'));
        $eventManager->bind('plugin.privacy.get_action_list', array($this, 'onPrivacyAddAction'));
        $eventManager->bind('base.members_only_exceptions', array($this, 'onAddMembersOnlyException'));
        $eventManager->bind('base.password_protected_exceptions', array($this, 'onAddPasswordProtectedExceptions'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($this, 'onAddMaintenanceModeExceptions'));
        $eventManager->bind(OW_EventManager::ON_USER_LOGIN, array($this, 'onUserLoginSetAdminCookie'));
        $eventManager->bind('core.emergency_exit', array($this, 'onEmergencyExit'));
        $eventManager->bind('base.mandatory_user_approve.edit', array($this, 'onMandatoryApproveUserEdit'));

        $eventManager->bind('admin.add_auth_labels', array($this, 'onAddAuthLabels'));

        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregisterClearMailQueue'));

        $eventManager->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetUserInfo'));

        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'setAccountTypeUserRoleOnUserRegister'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'deleteInviteCode'));
        $eventManager->bind('base.before_save_user', array($this, 'setUserRoleOnChangeAccountType'));

        $eventManager->bind('base.questions_field_add_fake_questions', array($this, 'addFakeQuestions'));

        $eventManager->bind(OW_EventManager::ON_JOIN_FORM_RENDER, array($this, 'onInviteMembersProcessJoinForm'));

        $eventManager->bind(BASE_CMP_ModerationToolsWidget::EVENT_COLLECT_CONTENTS, array($this, 'onCollectModerationWidgetContent'));
        $eventManager->bind("base.moderation_tools.collect_menu", array($this, 'onCollectModerationToolsMenu'));

        $eventManager->bind(BOL_ContentService::EVENT_BEFORE_DELETE, array($this, 'deleteEntityFlags'));

        BASE_CLASS_ContentProvider::getInstance()->init();
        $eventManager->bind('base.after_avatar_update', array($this, 'onAfterAvatarUpdate'));

        $eventManager->bind("base.sitemap.get_urls", array($this, 'onSitemapGetUrls'));
        $eventManager->bind("base.provide_page_meta_info", array($this, 'onProvideMetaInfoForPage'));
        OW::getEventManager()->bind('notification.get_edited_data', array($this, 'getEditedDataNotification'));

        $eventManager->bind('socket.user_socket_created', array($this, 'onSocketFirstLogin'));
        $eventManager->bind('socket.all_user_socket_closed', array($this, 'onSocketLastLogout'));

        $eventManager->bind('base.code.change', array($this, 'onCodeChange'));

        $eventManager->bind('frmfilemanager.import_files', array($this, 'importFilesToFileWidget'));
        $eventManager->bind('frmfilemanager.check_privacy', array($this, 'checkPrivacyForFileWidget'));
    }

    public function init()
    {
        $this->genericInit();
        $eventManager = OW::getEventManager();
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddDeleteActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddFlagActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddSuspendActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddAuthActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserApproveActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserFeatureActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddEditProfile'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockedActionTool'));

        $eventManager->bind('base.dashboard_menu_items', array($this, 'onDashboardMenuItem'));
        $eventManager->bind('base.preference_menu_items', array($this, 'onPreferenceMenuItem'));
        $eventManager->bind('base.on_avatar_toolbar_collect', array($this, 'onAvatarToolbarCollect'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'addJsDeclarations'));
        $eventManager->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $eventManager->bind('ads.enabled_plugins', array($this, 'onAddAdsEnabled'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onPluginUninstallDeleteComments'));

        $eventManager->bind(BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array($this, 'onPreferenceAddFormElement'));
        $eventManager->bind(BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT, array($this, 'onAddPreferenceSectionLabels'));
        $eventManager->bind('feed.collect_configurable_activity', array($this, 'onFeedCollectConfigurableActivity'));
        //$eventManager->bind('base.attachment_delete_image', array($this, 'onDeleteAttachmentImage'));
        $eventManager->bind('base.attachment_save_image', array($this, 'onSaveAttachmentImage'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteUserAttachments'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalizeAddScrollJs'));
        $eventManager->bind('join.get_captcha_field', array($this, 'getCaptcha'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'onPluginsInitCheckUserStatus'));
        $eventManager->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'onCollectQuickLinks'));
        $eventManager->bind("base.collect_seo_meta_data", array($this, 'onCollectMetaData'));

        if ( defined('OW_ADS_XP_TOP') )
        {
            $eventManager->bind('base.add_page_content', array($this, 'addPageBanner'));
        }
    }

    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $userId = OW::getUser()->getId();

        if ( $userId )
        {
            $blockedCount = BOL_UserService::getInstance()->countBlockedUsers($userId);

            if ( $blockedCount )
            {
                $event->add(array(
                    BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'my_blocked_users'),
                    BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('users-blocked'),
                    BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $blockedCount,
                    BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('users-blocked')
                ));
            }
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

        switch ( $params['entity'] )
        {
            // users
            case 'users' :
                if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('base', 'view_profile') )
                {
                    $offset = (int) $params['offset'];
                    $limit  = (int) $params['limit'];

                    $users = BOL_UserService::getInstance()->findLatestUserIdsList($offset, $limit);
                    $urls = BOL_UserService::getInstance()->getUserUrlsForList($users);

                    if ( $urls )
                    {
                        $event->setData($urls);
                    }
                }
                break;

            // base pages
            case 'base_pages' :
                // list of basic pages
                $urls = array(
                    OW_URL_HOME,
                    OW::getRouter()->urlForRoute('base.mobile_version'),
                    OW::getRouter()->urlForRoute('base_join'),
                    OW::getRouter()->urlForRoute('static_sign_in'),
                    OW::getRouter()->urlForRoute('base_forgot_password')
                );

                // get all public static docs
                $staticDocs = BOL_NavigationService::getInstance()->findAllStaticDocuments();

                foreach( $staticDocs as $doc )
                {
                    $menuItem = BOL_NavigationService::getInstance()->findMenuItemByDocumentKey($doc->key);

                    // is the page public
                    if ( $menuItem && in_array($menuItem->visibleFor,
                            array(BOL_NavigationService::VISIBLE_FOR_ALL, BOL_NavigationService::VISIBLE_FOR_GUEST)) )
                    {
                        $urls[] = OW_URL_HOME . $doc->uri;
                    }
                }

                $event->setData($urls);
                break;

            // base user pages
            case 'user_list' :
                if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('base', 'view_profile') )
                {
                    $event->setData(array(
                        OW::getRouter()->urlForRoute('users'),
                        OW::getRouter()->urlForRoute('base_user_lists', array(
                            'list' => 'latest'
                        )),
                        OW::getRouter()->urlForRoute('base_user_lists', array(
                            'list' => 'featured'
                        )),
                        OW::getRouter()->urlForRoute('base_user_lists', array(
                            'list' => 'online'
                        )),
                        OW::getRouter()->urlForRoute('base_user_lists', array(
                            'list' => 'search'
                        ))
                    ));
                }
                break;
        }
    }

    public function onAfterAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $entityTypes = explode(',',
            OW::getConfig()->getValue('base', 'site_statistics_disallowed_entity_types'));
        $ignoreStatistics = false;
        if($params['entityType']=='user_join' && isset($params['forEditProfile']) && $params['forEditProfile']){
            $ignoreStatistics = true;
        }
        if ( !in_array($params['entityType'], $entityTypes) && !$ignoreStatistics)
        {
            BOL_SiteStatisticService::getInstance()->addEntity($params['entityType'], $params['entityId']);
        }
    }


    public function deleteEntityFlags( OW_Event $event )
    {
        $params = $event->getParams();

        BOL_FlagService::getInstance()->deleteEntityFlags($params["entityType"], $params["entityId"]);
    }

    public function onCollectModerationWidgetContent( BASE_CLASS_EventCollector $event )
    {
        $flagGroups = BOL_FlagService::getInstance()->getContentGroupsWithCount();

        if ( empty($flagGroups) )
        {
            return;
        }

        $flagsCmp = new BASE_CMP_ModerationPanelList($flagGroups);

        $event->add(array(
            "url" => OW::getRouter()->urlForRoute("base.moderation_flags_index"),
            "name" => "flags",
            "label" => OW::getLanguage()->text("base", "flagged_content"),
            "content" => $flagsCmp->render()
        ));
    }

    public function onCollectModerationToolsMenu( BASE_CLASS_EventCollector $event )
    {
        $flagGroups = BOL_FlagService::getInstance()->getContentGroupsWithCount();

        if ( empty($flagGroups) )
        {
            return;
        }

        $event->add(array(
            "url" => OW::getRouter()->urlForRoute("base.moderation_flags_index"),
            "label" => OW::getLanguage()->text("base", "flagged_content"),
            "iconClass" => "ow_ic_clock ow_dynamic_color_icon",
            "key" => "flags"
        ));
    }

    public function deleteInviteCode( OW_Event $e )
    {
        $params = $e->getParams();

        if( !empty($params['params']['code']) )
        {
            BOL_UserService::getInstance()->deleteInvitationCode($params['params']['code']);
        }
    }


    public function onEmergencyExit( OW_Event $e )
    {
        $authenticate = false;
        if ( !empty($_COOKIE['adminToken']) && trim($_COOKIE['adminToken']) == OW::getConfig()->getValue('base', 'admin_cookie') )
        {
            $authenticate = true;
        }
        if (OW::getUser()->isAuthenticated() && BOL_AuthorizationService::getInstance()->isSuperModerator(OW::getUser()->getId())) {
            $authenticate = true;
        }
        if ($authenticate) {
            OW::getSession()->set('errorData', serialize($e->getParams()));
        }
    }

    public function onMandatoryApproveUserEdit( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = $params['userId'];
        $newUser = (isset($params['newUser']) && $params['newUser']);

        // change notif when approved by an admin
        $approved = (isset($params['approved']) && $params['approved']);
        $approvedAdmin = OW::getUser()->getId();

        $userService = BOL_UserService::getInstance();
        $profile_url = $userService->getUserUrl($userId);

        $moderators = BOL_AuthorizationService::getInstance()->getModeratorList();

        $moderatorIds = array();
        foreach ( $moderators as $moderator ) {
            $moderatorIds[] = $moderator->userId;
        }
        $findModeratorEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::FIND_MODERATOR_FOR_USER, array('userId' => $userId), $moderatorIds));
        $moderatorIds = $findModeratorEvent->getData();
        $url = $profile_url;
        foreach ( $moderatorIds as $moderatorId ) {
            // Not all moderators are able to edit users
            $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId, $moderatorId);
            if (!$hasAccessToApproveUser['valid']) {
                continue;
            }

            $params = array(
                'pluginKey' => 'base',
                'entityType' => 'user-edit-approve',
                'entityId' => $userId,
                'action' => 'admin-user-edit',
                'userId' => $moderatorId,
                'time' => time()
            );

            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $dicKey = ($newUser)?'base+user_add_approve_notifications':'base+user_edit_approve_notifications';

            $data = array(
                'avatar' => $avatars[$userId],
                'string' => array(
                    'key' => $dicKey,
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'adminName' => $userService->getDisplayName($approvedAdmin),
                        'adminUrl' => $userService->getUserUrl($approvedAdmin),
                        'photoUrl' => $url
                    )
                ),
                'url' => $url
            );

            if($approved){
                $data['string']['key'] = 'base+user_edit_approved_notifications';
                $data['disabled'] = true;
            }

            $event = new OW_Event('notifications.add', $params, $data);
            OW::getEventManager()->trigger($event);
        }
    }

    public function onUserLoginSaveStatistics( OW_Event $event )
    {
        $params = $event->getParams();
        BOL_SiteStatisticService::getInstance()->addEntity('user_login', $params['userId']);
    }

    public function onUserLoginSetAdminCookie( OW_Event $event )
    {
        $params = $event->getParams();

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            $newToken = UTIL_String::getRandomString(32);
            OW::getConfig()->saveConfig('base', 'admin_cookie', $newToken, null, false);
            setcookie('adminToken', $newToken, time() + 3600 * 24 * 100, '/', null, false, true);
        }
    }

    public function onUserLogout( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        if ( $userId < 0 )
        {
            return;
        }

        BOL_UserService::getInstance()->onLogout($userId);
    }

    public function onUserLogin( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        if ( $userId < 0 )
        {
            return;
        }

        BOL_UserService::getInstance()->onLogin($userId, OW::getApplication()->getContext());
    }

    public function onBeforeDecoratorRender( BASE_CLASS_PropertyEvent $e )
    {
        // to add some logic for decorators
    }

    public function onPluginsInitCheckUserStatus()
    {
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CHECK_USER_STATUS));
        if ( OW::getUser()->isAuthenticated() )
        {
            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());

            if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated() && !BOL_UserService::getInstance()->isApproved() && !OW::getUser()->isAdmin() )
            {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array('controller' => 'BASE_CTRL_Edit', 'action' => 'index'));
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_CTRL_User', 'signOut');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_CTRL_Edit', 'index');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_CTRL_Edit', 'ajaxResponder');
            }

            if ( $user !== null )
            {
                if ( BOL_UserService::getInstance()->isSuspended($user->getId()) && !OW::getUser()->isAdmin() )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.suspended_user', array('controller' => 'BASE_CTRL_SuspendedUser', 'action' => 'index'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_User', 'signOut');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Avatar');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Edit');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_DeleteUser');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Captcha');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Console');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_AjaxLoader');
                }

                $useVerifyEmailRedirect = true;
                $verifyEmailEvent = OW::getEventManager()->trigger(new OW_Event('base.on_before_email_verify_page_redirected'));
                if(isset($verifyEmailEvent->getData()['do-not-show'])){
                    $useVerifyEmailRedirect = false;
                }
                if ( $useVerifyEmailRedirect && (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.email_verify', array(OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_EmailVerify', OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'index'));

                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_CTRL_User', 'signOut');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_CTRL_EmailVerify');
                }

                $isAdminUrl = false;

                $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

                $attrs = OW::getRequestHandler()->getHandlerAttributes();
                if ( !empty($attrs[OW_RequestHandler::ATTRS_KEY_CTRL]) )
                {
                    $parents = class_parents($attrs[OW_RequestHandler::ATTRS_KEY_CTRL], true);

                    if ( in_array('ADMIN_CTRL_Abstract', $parents) )
                    {
                        $isAdminUrl = true;
                    }
                }

                if ( !$isAdminUrl )
                {
                    if ( empty($accountType))
                    {
                        OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile.account_type', array('controller' => 'BASE_CTRL_CompleteProfile', 'action' => 'fillAccountType'));
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_CTRL_Console', 'listRsp');
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_CTRL_User', 'signOut');
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'INSTALL_CTRL_Install');
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_CTRL_BaseDocument', 'installCompleted');
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_CTRL_AjaxLoader');
                        OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_CTRL_AjaxComponentAdminPanel');
                    }
                    else
                    {
                        $questionsEditStamp = OW::getConfig()->getValue('base', 'profile_question_edit_stamp');
                        $updateDetailsStamp = BOL_PreferenceService::getInstance()->getPreferenceValue('profile_details_update_stamp', OW::getUser()->getId());

                        if ( $questionsEditStamp >= (int) $updateDetailsStamp )
                        {
                            require_once OW_DIR_CORE . 'validator.php';
                            $questionList = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList($user->id);

                            if ( !empty($questionList) )
                            {
                                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array('controller' => 'BASE_CTRL_CompleteProfile', 'action' => 'fillRequiredQuestions'));
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_CTRL_Console', 'listRsp');
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_CTRL_User', 'signOut');
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'INSTALL_CTRL_Install');
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_CTRL_BaseDocument', 'installCompleted');
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_CTRL_AjaxLoader');
                                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_CTRL_AjaxComponentAdminPanel');
                            }
                            else
                            {
                                BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', time(), OW::getUser()->getId());
                            }
                        }
                    }
                }
            }
            else
            {
                OW::getUser()->logout();
            }
        }
    }

    public function addPageBanner( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['key'] == 'base.content_top' )
        {
            $event->add(OW_ADS_XP_TOP);
        }
        elseif ( $params['key'] == 'base.content_bottom' )
        {
            $event->add(OW_ADS_XP_BOT);
        }
    }

    public function getQuestionLabel( OW_Event $event )
    {
        $params = $event->getParams();

        $presentation = !empty($params['presentation']) ? $params['presentation'] : null;
        $fieldName = !empty($params['fieldName']) ? $params['fieldName'] : null;
        $configs = !empty($params['configs']) ? $params['configs'] : null;
        $type = !empty($params['type']) ? $params['type'] : null;

        if ( $type == 'view' && $fieldName == 'birthdate' && $presentation == BOL_QuestionService::QUESTION_PRESENTATION_AGE )
        {
            $event->setData(OW::getLanguage()->text('base', 'questions_question_birthday_label_presentation_age'));
        }
    }

    public function getCaptcha( OW_Event $e )
    {
        $e->setData(new CaptchaField('captchaField'));
    }

    public function onFinalizeAddScrollJs( $e )
    {
        $plugin = OW::getPluginManager()->getPlugin('base');

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.mousewheel.js');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.jscrollpane.js');
    }

    public function clearUserListQueryCache( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        OW::getCacheManager()->clean(array(BOL_UserDao::CACHE_TAG_ALL_USER_LIST));
    }

    public function sendSuspendNotification( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];
        $message = $params['message'];

        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);
        if ( empty($user) || empty($message) )
        {
            return false;
        }

        $email = $user->email;
        $displayName = $userService->getDisplayName($userId);

        $txt = OW::getLanguage()->text('base', 'suspend_notification_text', array('realName' => $displayName, 'suspendReason' => $message));
        $html = OW::getLanguage()->text('base', 'suspend_notification_html', array('realName' => $displayName, 'suspendReason' => $message));

        $subject = OW::getLanguage()->text('base', 'suspend_notification_subject');

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($txt)
                ->setHtmlContent($html)
                ->setSubject($subject);
            $mail->setPriority(BASE_CLASS_Mail::PRIORITY_VERY_HIGH);
            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            //printVar($e);
            //Skip invalid notification
        }
    }

    public function onDeleteUserAttachments( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            if ( isset($params['deleteContent']) && (bool) $params['deleteContent'] )
            {
                BOL_AttachmentService::getInstance()->deleteUserAttachments($userId);
            }
        }
    }

    public function onDeleteMediaPanelFiles( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        BOL_MediaPanelService::getInstance()->deleteImagesByUserId($userId);
    }

    public function onSaveAttachmentImage( OW_Event $event )
    {
        $params = $event->getParams();
        if ( empty($params['uid']) || empty($params['pluginKey']) )
        {
            return null;
        }

        BOL_AttachmentService::getInstance()->updateStatusForBundle($params['pluginKey'], $params['uid'], 1);
        $result = BOL_AttachmentService::getInstance()->getFilesByBundleName($params['pluginKey'], $params['uid']);
        return $result ? $result[0] : null;
    }


    public function onFeedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('admin', 'feed_content_registration'),
            'activity' => 'create:user_join'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_edit'),
            'activity' => 'create:user_edit'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_avatar_change'),
            'activity' => 'create:avatar-change'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_user_comment'),
            'activity' => 'create:user-comment'
        ));
    }

    public function onUserUnregisterRemovePreference( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        BOL_PreferenceService::getInstance()->deletePreferenceDataByUserId($userId);
    }

    public function onPrivacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $privacyValueEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD, array('key' => 'base_view_profile')));
        $defaultValue = 'everybody';
        if(isset($privacyValueEvent->getData()['value'])){
            $defaultValue = $privacyValueEvent->getData()['value'];
        }
        $action = array(
            'key' => 'base_view_profile',
            'pluginKey' => 'base',
            'label' => $language->text('base', 'privacy_action_view_profile', null, 'View Profile'),
            'description' => $language->text('base', 'privacy_action_view_profile_description', null, ' '),
            'defaultValue' => $defaultValue
        );

        $event->add($action);
        $privacyValueEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD, array('key' => 'base_view_my_presence_on_site')));
        $defaultValue = 'everybody';
        if(isset($privacyValueEvent->getData()['value'])){
            $defaultValue = $privacyValueEvent->getData()['value'];
        }
        $action = array(
            'key' => 'base_view_my_presence_on_site',
            'pluginKey' => 'base',
            'label' => $language->text('base', 'privacy_action_view_my_presence_on_site', null, 'View Profile'),
            'description' => $language->text('base', 'privacy_action_view_my_presence_on_site_description', null, ' '),
            'defaultValue' => $defaultValue
        );

        $event->add($action);
    }

    public function onAddPreferenceSectionLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $sectionLabels = array(
            'general' => array(
                'label' => $language->text('base', 'preference_section_general'),
                'iconClass' => 'ow_ic_script'
            )
        );

        $event->add($sectionLabels);
    }

    public function onPreferenceAddFormElement( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $params = $event->getParams();
        $values = $params['values'];

        $fromElementList = array();

        $fromElement = new CheckboxField('mass_mailing_subscribe');
        $fromElement->setLabel($language->text('base', 'preference_mass_mailing_subscribe_label'));
        $fromElement->setDescription($language->text('base', 'preference_mass_mailing_subscribe_description'));

        if ( isset($values['mass_mailing_subscribe']) )
        {
            $fromElement->setValue($values['mass_mailing_subscribe']);
        }


        $timeZoneSelect = new Selectbox("timeZoneSelect");
        $timeZoneSelect->setLabel($language->text('admin', 'timezone'));
        $timeZoneSelect->addOptions(UTIL_DateTime::getTimezones());

        $timeZoneSelect->setValue($values['timeZoneSelect']);

        $fromElementList[] = $timeZoneSelect;
        $fromElementList[] = $fromElement;
        $event->add($fromElementList);

    }

    public function onAddAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'base' => array(
                    'label' => $language->text('base', 'auth_group_label'),
                    'actions' => array(
                        'add_comment' => $language->text('base', 'auth_action_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('base', 'delete_comment_by_content_owner'),
                        'search_users' => $language->text('base', 'search_users'),
                        'view_profile' => $language->text('base', 'auth_view_profile'),
                        'edit_user_profile' => $language->text('base', 'edit_user_profile'),
                    )
                )
            )
        );
    }

    public function onPluginUninstallDeleteComments( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['pluginKey']) )
        {
            BOL_CommentService::getInstance()->deletePluginComments($params['pluginKey']);
        }
    }

    public function onAddAdsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('base');
    }

    public function addAdminNotification( BASE_CLASS_EventCollector $coll )
    {
        $notificationCollectorEvent = OW::getEventManager()->trigger(new OW_Event('base.on_before_admin_notification_collector', array('type' => 'cron')));
        if(isset($notificationCollectorEvent->getData()['ignore'])){
            return;
        }
        if ( OW::getConfig()->getValue('base', 'cron_is_configured') || defined('OW_PLUGIN_XP') )
        {
            return;
        }

        $coll->add(OW::getLanguage()->text('admin', 'cron_configuration_required_notice', array(
            'helpUrl' => 'https://'
        )));
    }

    public function addJsDeclarations( OW_Event $e )
    {
        //Langs
        OW::getLanguage()->addKeyForJs('base', 'ajax_floatbox_users_title');
        OW::getLanguage()->addKeyForJs('base', 'flag_as');
        OW::getLanguage()->addKeyForJs('base', 'delete_user_confirmation_label');
        OW::getLanguage()->addKeyForJs('base', 'authorization_limited_permissions');
        OW::getLanguage()->addKeyForJs('base', 'avatar_change');
        OW::getLanguage()->addKeyForJs('base', 'avatar_crop');

        $scriptGen = UTIL_JsGenerator::newInstance()->setVariable(array('OW', 'ajaxComponentLoaderRsp'), OW::getRouter()->urlFor('BASE_CTRL_AjaxLoader', 'component'));
        $scriptGen->setVariable(array('OW', 'ajaxAttachmentLinkRsp'), OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addLink'));

        //Ping
        if(FRMSecurityProvider::isSocketEnable() && OW_User::getInstance()->isAuthenticated()){
            // socket
            $userId = OW::getUser()->getId();
            $hash = OW_SocketPing::getHash($userId);
            $session_id = session_id();

            $socket_auth = "[$userId, '$hash', '$session_id']";
            $socket_host = OW::getConfig()->getValue('base', 'socket_host');
            OW::getDocument()->addScriptDeclarationBeforeIncludes("var socket; var socket_auth = $socket_auth;");
            OW::getDocument()->addScriptDeclaration("init_socket('$socket_host');");
        }

        $scriptGen->addScript('OW.getPing().setRspUrl({$url});', array(
            'url' => OW::getRouter()->urlFor('BASE_CTRL_Ping', 'index')
        ));

        //UsersApi
        $scriptGen->newObject(array('OW', 'Users'), 'OW_UsersApi', array(array(
            "rsp" => OW::getRouter()->urlFor('BASE_CTRL_AjaxUsersApi', 'rsp')
        )));

        OW::getDocument()->addScriptDeclaration($scriptGen->generateJs());

        //Light Cron
        $cronReady = OW::getConfig()->configExists('base', 'cron_is_configured') && OW::getConfig()->getValue('base', 'cron_is_configured');

        if ( !$cronReady && !defined('OW_PLUGIN_XP') )
        {
            OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(
                '$.get({$cron});'
                , array(
                'cron' => OW::getRequest()->buildUrlQueryString(OW_URL_HOME . 'ow_cron/run.php', array(
                    'ow-light-cron' => 1
                ))
            )));
        }
    }

    public function onAvatarToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'title' => OW::getLanguage()->text('base', 'console_item_label_dashboard'),
            'iconClass' => 'ow_ic_house',
            'url' => OW::getRouter()->urlForRoute('base_member_dashboard'),
            'order' => 1
        ));

        $e->add(array(
            'title' => OW::getLanguage()->text('base', 'console_item_label_profile'),
            'iconClass' => 'ow_ic_user',
            'url' => OW::getRouter()->urlForRoute('base_member_profile'),
            'order' => 3
        ));
    }

    public function onAddComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'base_profile_wall' )
        {
            return;
        }

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $userService = BOL_UserService::getInstance();

        $user = $userService->findUserById($entityId);

        if ( $user->getId() == $userId )
        {
            return;
        }

        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        $url = OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($entityId)));

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];

        $event = new OW_Event('notifications.add', array(
            'pluginKey' => 'base',
            'entityType' => 'base_profile_wall',
            'entityId' => $commentId,
            'action' => 'base_add_user_comment',
            'userId' => $user->getId(),
        ), array(
            'avatar' => $avatar,
            'string' => array(
                'key' => 'base+profile_comment_notification',
                'vars' => array(
                    'userName' => $userService->getDisplayName($userId),
                    'userUrl' => $userService->getUserUrl($userId),
                    'profileUrl' => $userService->getUserUrl($user->getId())
                )
            ),
            'content' => $comment->getMessage(),
            'url' => $userService->getUserUrl($user->getId())
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'base',
            'sectionLabel' => OW::getLanguage()->text('base', 'notification_section_label'),
            'action' => 'base_add_user_comment',
            'description' => OW::getLanguage()->text('base', 'email_notifications_setting_user_comment'),
            'sectionIcon' => 'ow_ic_file',
            'selected' => true
        ));

        $e->add(array(
            'section' => 'admin',
            'sectionLabel' => OW::getLanguage()->text('base', 'admin_notification_section_label'),
            'action' => 'admin-user-edit',
            'description' => OW::getLanguage()->text('base', 'user_edit_approve_notifications_label'),
            'sectionIcon' => 'ow_ic_file',
            'selected' => true
        ));

        $e->add(array(
            'section' => 'admin',
            'sectionLabel' => OW::getLanguage()->text('base', 'admin_notification_section_label'),
            'action' => 'log_failed',
            'description' => OW::getLanguage()->text('base', 'logger_write_failed'),
            'sectionIcon' => 'ow_ic_file',
            'selected' => true
        ));
    }

    public function onAddMaintenanceModeExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
    }

    public function onAddPasswordProtectedExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'ajaxSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_CTRL_EmailVerify', 'action' => 'verify'));
        $event->add(array('controller' => 'BASE_CTRL_Unsubscribe', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'redirectToMobile'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'ajaxResponder'));
        $event->add(array('controller' => 'BASE_CTRL_Captcha', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_Captcha', 'action' => 'ajaxResponder'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'ajaxSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_Unsubscribe', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'redirectToMobile'));
        $event->add(array('controller' => 'BASE_CTRL_AjaxLoader', 'action' => 'init'));
        $event->add(array('controller' => 'BASE_CTRL_AjaxLoader', 'action' => 'component'));
        $event->add(array('controller' => 'BASE_CTRL_Avatar', 'action' => 'ajaxResponder'));
    }

    public function onPreferenceMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();
        $language = OW::getLanguage();

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('preference');
        $menuItem->setLabel($language->text('base', 'preference_menu_item'));
        $menuItem->setIconClass('ow_ic_gear_wheel ow_dynamic_color_icon');
        $menuItem->setUrl($router->urlForRoute('base_preference_index'));
        $menuItem->setOrder(1);

        $event->add($menuItem);
    }

    public function onDashboardMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();
        $language = OW::getLanguage();

        $menuItems = array();

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('widget_panel');
        $menuItem->setLabel($language->text('base', 'widgets_panel_dashboard_label'));
        $menuItem->setIconClass('ow_ic_house');
        $menuItem->setUrl($router->urlForRoute('base_member_dashboard'));
        $menuItem->setOrder(1);

        $event->add($menuItem);


        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('profile_edit');
        $menuItem->setLabel($language->text('base', 'edit_index'));
        $menuItem->setIconClass('ow_ic_user');
        $menuItem->setUrl($router->urlForRoute('base_edit'));
        $menuItem->setOrder(2);

        $event->add($menuItem);

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('preference');
        $menuItem->setLabel($language->text('base', 'preference_index'));
        $menuItem->setIconClass('ow_ic_gear_wheel');
        $menuItem->setUrl($router->urlForRoute('base_preference_index'));
        $menuItem->setOrder(4);

        $event->add($menuItem);
    }

    public function onUserUnregisterDeleteDisaproved( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $userService = BOL_UserService::getInstance();

        if ( !$userService->isApproved($userId) )
        {
            return;
        }

        $userService->deleteDisaproveByUserId($userId);
    }

    public function onUserUnregisterClearMailQueue( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        BOL_MailService::getInstance()->deleteQueuedMailsByRecipientId($userId);
    }

    public function onInviteMembersProcessJoinForm( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['code'] !== null )
        {
            $info = BOL_UserService::getInstance()->findInvitationInfo($params['code']);

            if ( $info !== null )
            {
                throw new JoinRenderException();
            }
        }
    }

    public function onActionToolbarAddUserBlockActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $authorizationService = BOL_AuthorizationService::getInstance();

        if ( $authorizationService->isActionAuthorizedForUser($params['userId'], 'base','block') || $authorizationService->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $resultArray = array();

        $uniqId = FRMSecurityProvider::generateUniqueId("block-");
        $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleText = !$isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $isBlocked ? 'unblock_user_icon' : 'block_user_icon';

        $toggleClass = !$isBlocked ? 'ow_mild_green' : 'ow_mild_red';

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $isBlocked ? "unblock" : "block";

        $toggleCommand = !$isBlocked ? "unblock" : "block";

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';
        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;
        $unBlockCode = '';
        $blockCode = '';
        $eData = array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass,
            "unBlockCode" => $unBlockCode,
            "blockCode" => $blockCode
        );
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userBlock_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['blockCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnBlock_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['unBlockCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click", 'var self = $(this); toggle = function() {
            OW.Utils.toggleText(self, e.data.toggleText);
            OW.Utils.toggleAttr(self, "class", e.data.toggleClass);
            OW.Utils.toggleAttr(self, "data-command", e.data.toggleCommand);
        };
        if ( self.attr("data-command") == "block" )
            OW.Users.blockUserWithConfirmation(e.data.userId, e.data.blockCode, toggle);
        else {
            OW.Users.unBlockUser(e.data.userId, e.data.unBlockCode);
            toggle();
        }'
            , array("e"),$eData);

        OW::getDocument()->addOnloadScript($js);

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.block_user";
        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER] = 8;

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserFeatureActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3
        );

        $userId = (int) $params['userId'];

        $uniqId = FRMSecurityProvider::generateUniqueId("feature-");
        $isFeatured = BOL_UserService::getInstance()->isUserFeatured($userId);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $toggleText = !$isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $isFeatured ? "unfeature" : "feature";
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["class"] = "user_profile_star";

        $toggleCommand = !$isFeatured ? "unfeature" : "feature";

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;
        $featureCode = '';
        $UnFeatureCode='';
        $eData = array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "featureCode" => $featureCode,
            "unFeatureCode" => $UnFeatureCode
        );

        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userFeature_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['featureCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnFeature_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['unFeatureCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click", 'OW.Users[$(this).attr("data-command") == "feature" ? "featureUser" : "unFeatureUser"](e.data.userId,e.data.featureCode,e.data.unFeatureCode);
         OW.Utils.toggleText(this, e.data.toggleText);
         OW.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), $eData);

        OW::getDocument()->addOnloadScript($js);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.make_featured";
        $event->add($action);
    }

    public function onActionToolbarAddUserApproveActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
        if (!$hasAccessToApproveUser['valid']) {
            return;
        }

        if ( BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }

        if (  OW::getConfig()->getValue('base', 'mandatory_user_approve') != 1 )
        {
            return;
        }

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array('userId' => $userId)),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_approve_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.approve_user"
        );
        $event->add($action);

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => ['onclick'=>'request_change()'],
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_request_change_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.request_change_user"
        );
        $event->add($action);
        $js = "function request_change(){
            floatBox = OW.ajaxFloatBox('BASE_CMP_CommentRequestChangeMessage', [{userId:$userId}]); 
        }";
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
    }

    public function onActionToolbarAddAuthActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $uniqId = FRMSecurityProvider::generateUniqueId('change-role-');

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'authorization_give_user_role'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green profile_change_role',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.change_role"
        );

        $event->add($action);

        $js = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $uniqId, 'click', 'window.baseChangeUserRoleFB = OW.ajaxFloatBox("BASE_CMP_GiveUserRole", [e.data.userId], { width:556, title: e.data.title });', array('e'), array(
            'userId' => $userId,
            'title' => OW::getLanguage()->text('base', 'authorization_give_user_role')
        ));

        OW::getDocument()->addOnloadScript($js);
    }

    public function onActionToolbarAddSuspendActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $userId = (int) $params['userId'];

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 4
        );

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';

        $uniqId = FRMSecurityProvider::generateUniqueId('pat-suspend-');
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;

        $toogleText = null;
        $toggleCommand = null;
        $toggleClass = null;

        $suspended = $userService->isSuspended($userId);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $toggleText = !$suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $suspended ? "unsuspend" : "suspend";

        $toggleCommand = !$suspended ? "unsuspend" : "suspend";

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $suspended ? "unsuspend_user_icon" : "suspend_user_icon";

        $toggleClass = !$suspended ? "ow_mild_green" : "ow_mild_red";

        $rsp = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'ajaxRsp');
        $rsp = OW::getRequest()->buildUrlQueryString($rsp, array(
            "userId" => $userId
        ));

        OW::getLanguage()->addKeyForJs('base', 'suspend_floatbox_title');
        OW::getLanguage()->addKeyForJs('base', 'are_you_sure');

        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        $js = UTIL_JsGenerator::newInstance();
        $suspendCode='';
        $unSuspendCode='';
        $eData = array(
            "uniqId" => $uniqId,
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass,
            "suspendCode" =>$suspendCode,
            "unSuspendCode" => $unSuspendCode
        );
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userSuspend_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $suspendCode = $frmSecuritymanagerEvent->getData()['code'];
            $eData['suspendCode'] = $suspendCode;
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnSuspend_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $unSuspendCode = $frmSecuritymanagerEvent->getData()['code'];
            $eData['unSuspendCode'] = $unSuspendCode;
        }
        $js->jQueryEvent("#" . $uniqId, "click", ' 
            
            if ( $(this).attr("data-command") == "suspend" )  
            {
                OW.ajaxFloatBox("BASE_CMP_SetSuspendMessage", [e.data.userId,e.data.suspendCode,e.data.unSuspendCode],{width: 520, title: OW.getLanguageText(\'base\', \'suspend_floatbox_title\', {\'displayName\': e.data.displayName})}); 
            }
            else
            {
                OW.trigger("base.on_suspend_command", ["unsuspend"])
            } '
            , array("e"), array(
                "userId" => $userId,
                "toggleText" => $toggleText,
                "toggleCommand" => $toggleCommand,
                "toggleClass" => $toggleClass,
                "displayName" => $displayName,
                "suspendCode" =>$suspendCode,
                "unSuspendCode" => $unSuspendCode

            ));

        $js->addScript( ' OW.bind("base.on_suspend_command", function( command, message ) {
                var element = $("#"+{$uniqId});

                OW.Users[command == "suspend" ? "suspendUser" : "unSuspendUser"]({$userId},{$suspendCode},{$unSuspendCode}, null, message,);
                OW.Utils.toggleText(element, {$toggleText});
                OW.Utils.toggleAttr(element, "class", {$toggleClass});
                OW.Utils.toggleAttr(element, "data-command", {$toggleCommand});
                
             }); ', $eData );

        OW::getDocument()->addOnloadScript($js);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.suspend_user";

        $event->add($action);
    }

    public function onActionToolbarAddEditProfile( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $viewerId = OW::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $adminMode = OW::getUser()->isAuthorized('base','edit_user_profile');
        $isSuperAdmin = BOL_AuthorizationService::getInstance()->isSuperModerator($userId);

        if( $ownerMode || ($adminMode && !$isSuperAdmin)){

			if ( $adminMode && !$ownerMode ){
                $profileEditUrl = OW::getRouter()->urlForRoute('base_edit_user_datails', array('userId' => $userId));
            }else{
                $profileEditUrl =  OW::getRouter()->urlForRoute('base_edit');
            }
            $label = OW::getLanguage()->text('base', 'edit_profile_link');
            $linkId = "edit_profile" . rand(10, 1000000);;

            $resultArray = array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => $label,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => $profileEditUrl,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => 'edit.action.profile',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1
            );
    
            $event->add($resultArray);
        }


    }

    public function onActionToolbarAddFlagActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId'])
            || $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $linkId = 'ud' . rand(10, 1000000);
        $script = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $linkId, 'click', 'OW.flagContent(e.data.entityType, e.data.entityId);'
            , array('e'), array(
                'entityType' => BASE_CLASS_ContentProvider::ENTITY_TYPE_PROFILE,
                'entityId' => $userId
            ));

        OW::getDocument()->addOnloadScript($script);

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'flag'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 7,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.flag_user",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'report_user_icon'
        );

        $event->add($resultArray);
    }

    public function onActionToolbarAddDeleteActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $callbackUrl = OW::getRouter()->urlFor('BASE_CTRL_User', 'userDeleted');
        $code='';
        $eData = array('userId' => $userId, 'callbackUrl' => $callbackUrl, 'code'=>$code);
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userDelete_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['code'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $linkId = 'ud' . rand(10, 1000000);

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_delete_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'delete_user_icon',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 5,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.delete_user"
        );

        $newDeletePath = OW::getEventManager()->trigger(new OW_Event('base.before.action_user_delete', array('href' => 'javascript://', 'userId' => $userId)));
        if(isset($newDeletePath->getData()['href'])){
            $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = $newDeletePath->getData()['href'];
        }else{
            $script = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $linkId, 'click', 'OW.Users.deleteUser(e.data.userId,e.data.code, e.data.callbackUrl, false);'
                , array('e'), $eData);
            OW::getDocument()->addOnloadScript($script);
        }

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserBlockedActionTool ( BASE_CLASS_EventCollector $event ) {

        $params = $event->getParams();

        if ( empty($params['userId']) ) {
            return;
        }

        if ($params['userId'] != OW::getUser()->getId()) {
            return;
        }

        $uniqId = FRMSecurityProvider::generateUniqueId('users-blocked-');

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'my_blocked_users'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlForRoute('users-blocked'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'block_user_icon',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 9,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.users_blocked"
        );

        $event->add($action);
    }
    
    public function onUserRegisterWelcomeLetter( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId === 0 || !BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }
        if(isset($params['forEditProfile']) && $params['forEditProfile']==true ) {
            return;
        }
        BOL_PreferenceService::getInstance()->savePreferenceValue('send_wellcome_letter', 1, $userId);
        OW::getEventManager()->trigger(new OW_Event(BOL_UserService::EVENT_AFTER_REGISTER_WELCOME_LETTER));
    }

    public function onUserAvatarCommentFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'avatar-change' )
        {
            return;
        }

        $avatarId = $params['entityId'];

        $service = BOL_AvatarService::getInstance();
        $avatar = $service->findAvatarById($avatarId);

        if ( !$avatar )
        {
            return;
        }

        $userId = $avatar->userId;

        if ( $userId == $params['userId'] )
        {
            return;
           /* $string = array('key' => 'base+feed_activity_avatar_string_own');*/
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = array(
                'key' => 'base+feed_activity_avatar_string',
                'vars' => array('user' => $userEmbed)
            );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'base'
        ), array(
            'string' => $string
        )));
    }

    public function onUserAvatarLikeFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'avatar-change' )
        {
            return;
        }

        $avatarId = $params['entityId'];

        $service = BOL_AvatarService::getInstance();
        $avatar = $service->findAvatarById($avatarId);

        if ( !$avatar )
        {
            return;
        }

        $userId = $avatar->userId;

        if ( $userId == $params['userId'] )
        {
            return;
            /*$string = array('key' => 'base+feed_activity_avatar_string_like_own');*/
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = array(
                'key' => 'base+feed_activity_avatar_string_like',
                'vars' => array('user' => $userEmbed)
            );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'base'
        ), array(
            'string' => $string,
            'ownerId' => $userId
        )));
    }

    public function onLikeUserJoin( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_join' )
        {
            return;
        }

        $userId = $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'base'
        ), array(
            'string' => array(
                'key' => 'base+feed_activity_join_profile_string_like',
                'vars' => array('user' => $userEmbed)
            )
        )));
    }

    public function onUserJoinCommentFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_join' )
        {
            return;
        }

        $userId = $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'base'
        ), array(
            'string' => array(
                'key' => 'base+feed_activity_join_profile_string',
                'vars' => array('user' => $userEmbed)
            )
        )));
    }

    public function onJoinFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'native' || (isset($params['forEditProfile']) && $params['forEditProfile']==true))
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'base',
            'entityType' => 'user_join',
            'entityId' => $userId,
            'userId' => $userId,
            'replace' => true
        ), array(
            'time'=>time(),
            'string' => array('key' => 'base+feed_user_join'),
            'view' => array(
                'iconClass' => 'ow_ic_user'
            ),
            'ownerId' =>$userId
        ));
        OW::getEventManager()->trigger($event);
    }

    public function onUserEditFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'native' )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'base',
            'entityType' => 'user_edit',
            'entityId' => $userId,
            'userId' => $userId,
            'replace' => true
        ), array(
            'string' => array('key' => 'base+feed_user_edit_profile'),
            'data' => array(
                'userId' => $userId
            ),
            'features' => array(),
            'view' => array(
                'iconClass' => 'ow_ic_user'
            )
        ));
        OW::getEventManager()->trigger($event);
    }

    public function onJoinMandatoryUserApprove( OW_Event $event )
    {
        $params = $event->getParams();

        $disapprove = true;
        $userDisapprove = new OW_Event(FRMEventManager::ON_BEFORE_USER_DISAPPROVE_AFTER_EDIT_PROFILE, array('params' => $params));
        OW::getEventManager()->trigger($userDisapprove);
        if(isset($userDisapprove->getData()['disapprove'])){
            $disapprove = $userDisapprove->getData()['disapprove'];
        }

        if ( !OW::getConfig()->getValue('base', 'mandatory_user_approve') || !$disapprove)
        {
            $e = new OW_Event(OW_EventManager::ON_USER_APPROVE, array('userId' => (int) $params['userId']));
            OW::getEventManager()->trigger($e);

            return;
        }

        BOL_UserService::getInstance()->disapprove((int) $params['userId']);
    }

    public function onAddGlobalLangs( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('site_name' => OW::getConfig()->getValue('base', 'site_name')));
        $event->add(array('site_url' => OW_URL_HOME));
        $event->add(array('site_email' => OW::getConfig()->getValue('base', 'site_email')));
    }

    public function onDeleteUserContent( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $moderatorId = BOL_AuthorizationService::getInstance()->getModeratorIdByUserId($userId);
            if ( $moderatorId !== null )
            {
                BOL_AuthorizationService::getInstance()->deleteModerator($moderatorId);
            }

            BOL_AuthorizationService::getInstance()->deleteUserRolesByUserId($userId);

            if ( isset($params['deleteContent']) && (bool) $params['deleteContent'] )
            {
                BOL_CommentService::getInstance()->deleteUserComments($userId);
                BOL_RateService::getInstance()->deleteUserRates($userId);
                BOL_VoteService::getInstance()->deleteUserVotes($userId);
            }

            //delete widgets
            BOL_ComponentEntityService::getInstance()->onEntityDelete(BOL_ComponentEntityService::PLACE_DASHBOARD, $userId);
            BOL_ComponentEntityService::getInstance()->onEntityDelete(BOL_ComponentEntityService::PLACE_PROFILE, $userId);

            // delete email verify
            BOL_EmailVerifyService::getInstance()->deleteByUserId($userId);

            // delete remote auth info
            BOL_RemoteAuthService::getInstance()->deleteByUserId($userId);

            // delete user auth token
            BOL_AuthTokenDao::getInstance()->deleteByUserId($userId);
        }
    }

    public function sosialSharingGetUserInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( !empty($params['entityId']) && $params['entityType'] == 'user' )
        {
            $user = BOL_UserService::getInstance()->findUserById($params['entityId']);

            $displaySocialSharing = true;

            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('base', 'view_profile') )
            {
                $displaySocialSharing = false;
            }

            $eventParams = array(
                'action' => 'base_view_profile',
                'ownerId' => $user->id,
                'viewerId' => 0
            );

            try
            {
                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $ex )
            {
                $displaySocialSharing = false;
            }

            if ( !empty($user) )
            {
                $data['display'] = $displaySocialSharing;
            }

            $event->setData($data);
        }
    }

    public function setAccountTypeUserRoleOnUserRegister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $user = BOL_UserService::getInstance()->findUserById($params['userId']);

        if ( empty($user) )
        {
            return;
        }

        $accountType = $user->accountType;

        if ( empty($accountType) )
        {
            return;
        }

        $accountTypeDto = BOL_QuestionService::getInstance()->findAccountTypeByName($accountType);

        if ( empty($accountTypeDto) || empty($accountTypeDto->roleId) )
        {
            return;
        }

        BOL_AuthorizationService::getInstance()->deleteUserRole($user->id, $accountTypeDto->roleId);
        BOL_AuthorizationService::getInstance()->saveUserRole($user->id, $accountTypeDto->roleId);
    }

    public function setUserRoleOnChangeAccountType( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['dto']) || !($params['dto'] instanceof BOL_User ) )
        {
            return;
        }

        $user = $params['dto'];

        $newAccountType = $user->accountType;
        $oldAccountType = null;

        if ( empty($user->id) )
        {
            return;
        }

        $oldUser = BOL_UserService::getInstance()->findByIdWithoutCache($user->id);

        if ( !empty($oldUser) )
        {
            $oldAccountType = $oldUser->accountType;
        }

        if ( $newAccountType === $oldAccountType )
        {
            return;
        }

        if ( !empty($newAccountType) )
        {
            if ( !empty($oldAccountType) )
            {
                $oldAccountTypeDto = BOL_QuestionService::getInstance()->findAccountTypeByName($oldAccountType);

                /* @var $defaultRole BOL_AuthorizationRole */
                $defaultRole = BOL_AuthorizationService::getInstance()->getDefaultRole();

                if ( !empty($oldAccountTypeDto) && !empty($oldAccountTypeDto->roleId) && $oldAccountTypeDto->roleId != $defaultRole->id )
                {
                    BOL_AuthorizationService::getInstance()->deleteUserRole($user->id, $oldAccountTypeDto->roleId);
                }
            }

            $accountTypeDto = BOL_QuestionService::getInstance()->findAccountTypeByName($newAccountType);

            if ( !empty($accountTypeDto) && !empty($accountTypeDto->roleId) )
            {
                BOL_AuthorizationService::getInstance()->deleteUserRole($user->id, $accountTypeDto->roleId);
                BOL_AuthorizationService::getInstance()->saveUserRole($user->id, $accountTypeDto->roleId);
            }
        }
    }
    public function addFakeQuestions( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['name']) && $params['name'] == 'email' )
        {
            $e->setData(false);
        }
    }

    public function onAfterAvatarUpdate( OW_Event $e )
    {
        $params = $e->getParams();

        if ( !empty($params['trackAction']) && $params['trackAction'] == true )
        {
            if ( !empty($params['avatarId']) && !empty($params['userId']) )
            {
                BOL_AvatarService::getInstance()->trackAvatarChangeActivity($params['userId'], $params['avatarId']);
            }
        }
    }

    public function onCollectMetaData( BASE_CLASS_EventCollector $e )
    {
        $language = OW::getLanguage();

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_users"),
                "sectionKey" => "base.users",
                "entityKey" => "userLists",
                "entityLabel" => $language->text("base", "seo_meta_user_list_label"),
                "iconClass" => "ow_ic_newsfeed",
                "langs" => array(
                    "title" => "base+meta_title_user_list",
                    "description" => "base+meta_desc_user_list",
                    "keywords" => "base+meta_keywords_user_list"
                ),
                "vars" => array( "user_list", "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_base_pages"),
                "sectionKey" => "base.base_pages",
                "entityKey" => "index",
                "entityLabel" => $language->text("base", "seo_meta_index_label"),
                "iconClass" => "ow_ic_house",
                "langs" => array(
                    "title" => "base+meta_title_index",
                    "description" => "base+meta_desc_index",
                    "keywords" => "base+meta_keywords_index"
                ),
                "vars" => array( "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_base_pages"),
                "sectionKey" => "base.base_pages",
                "entityKey" => "join",
                "entityLabel" => $language->text("base", "seo_meta_join_label"),
                "iconClass" => "ow_ic_add",
                "langs" => array(
                    "title" => "base+meta_title_join",
                    "description" => "base+meta_desc_join",
                    "keywords" => "base+meta_keywords_join"
                ),
                "vars" => array( "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_base_pages"),
                "sectionKey" => "base.base_pages",
                "entityKey" => "sign_in",
                "entityLabel" => $language->text("base", "seo_meta_sign_in_label"),
                "iconClass" => "ow_ic_key",
                "langs" => array(
                    "title" => "base+meta_title_sign_in",
                    "description" => "base+meta_desc_sign_in",
                    "keywords" => "base+meta_keywords_sign_in"
                ),
                "vars" => array( "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_base_pages"),
                "sectionKey" => "base.base_pages",
                "entityKey" => "forgotPass",
                "entityLabel" => $language->text("base", "seo_meta_forgot_pass_label"),
                "iconClass" => "ow_ic_lock",
                "langs" => array(
                    "title" => "base+meta_title_forgot_pass",
                    "description" => "base+meta_desc_forgot_pass",
                    "keywords" => "base+meta_keywords_forgot_pass"
                ),
                "vars" => array( "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_users"),
                "sectionKey" => "base.users",
                "entityKey" => "userPage",
                "entityLabel" => $language->text("base", "seo_meta_user_page_label"),
                "iconClass" => "ow_ic_user",
                "langs" => array(
                    "title" => "base+meta_title_user_page",
                    "description" => "base+meta_desc_user_page",
                    "keywords" => "base+meta_keywords_user_page"
                ),
                "vars" => array( "site_name" )
            )
        );

        $e->add(
            array(
                "sectionLabel" => $language->text("base", "seo_meta_section_users"),
                "sectionKey" => "base.users",
                "entityKey" => "userSearch",
                "entityLabel" => $language->text("base", "seo_meta_user_search_label"),
                "iconClass" => "ow_ic_lens",
                "langs" => array(
                    "title" => "base+meta_title_user_search",
                    "description" => "base+meta_desc_user_search",
                    "keywords" => "base+meta_keywords_user_search"
                ),
                "vars" => array( "site_name" )
            )
        );
    }

    public function onProvideMetaInfoForPage( OW_Event $event )
    {
        $document = OW::getDocument();
        $language = OW::getLanguage();

        if( !$document || !$document instanceof OW_HtmlDocument )
        {
            return;
        }

        $params = $event->getParams();

        if( BOL_SeoService::getInstance()->isMetaDisabledForEntity($params["sectionKey"], $params["entityKey"]) )
        {
            $document->addMetaInfo("robots", "noindex");
            return;
        }

        $vars = empty($params["vars"]) ? array() : $params["vars"];

        $title = false;
        $desc = false;
        $keywords = false;

        if( !empty($params["title"]) )
        {
            $parts = explode("+", $params["title"]);
            $title = $this->processMetaText($language->text($parts[0], $parts[1], $vars), false, BOL_SeoService::META_TITLE_MAX_LENGTH);
        }

        if( !empty($params["description"]) )
        {
            $parts = explode("+", $params["description"]);
            $desc = $this->processMetaText($language->text($parts[0], $parts[1], $vars), true, BOL_SeoService::META_DESC_MAX_LENGTH);
        }

        if( !empty($params["keywords"]) )
        {
            $parts = explode("+", $params["keywords"]);
            $keywords = $this->processMetaText($language->text($parts[0], $parts[1], $vars));
        }

        // add standard meta
        if( $title )
        {
            $document->setTitle($title);
        }

        if( $desc )
        {
            $document->setDescription($desc);
        }

        if( $keywords )
        {
            $document->setKeywords($keywords);
        }

        //add og
        $imageUrl = BOL_SeoService::getInstance()->getSocialLogoUrl();

        if( !empty($params["image"]) )
        {
            $imageUrl = trim($params["image"]);
        }

        $document->addMetaInfo("og:type", "website", 'property');
        $document->addMetaInfo("og:site_name", OW::getConfig()->getValue('base', 'site_name'), 'property');
        $document->addMetaInfo("og:locale", BOL_LanguageService::getInstance()->getCurrent()->getTag(), 'property');
        $document->addMetaInfo("og:url", OW_URL_HOME, 'property');
        if( $title )
        {
            $document->addMetaInfo("og:title", $title, 'property');
        }

        if( $desc )
        {
            $document->addMetaInfo("og:description", $desc, 'property');
        }

        if( $imageUrl )
        {
            $document->addMetaInfo("og:image", $imageUrl, 'property');
        }

        if( $title )
        {
            $document->addMetaInfo("twitter:title", $title);
        }

        if( $desc )
        {
            $document->addMetaInfo("twitter:description", $desc);
        }

        if( $imageUrl )
        {
            $document->addMetaInfo("twitter:image", $imageUrl);
        }
    }

    protected function processMetaText( $text, $escape = true, $maxLength = null )
    {
        if( $escape )
        {
            $text = htmlspecialchars(trim($text));
        }
        else
        {
            $text = str_replace('"', "", strip_tags($text));
        }

        if( $maxLength !== null && mb_strlen($text) > $maxLength )
        {
            $text = UTIL_String::truncate($text, $maxLength - 3, '...');
        }

        return $text;
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'base')
            return;
        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];

        if($entityType == 'base_profile_wall'){
            $commentService = BOL_CommentService::getInstance();
            $comment = $commentService->findComment($entityId);
            if (isset($comment))
            {
                $notificationData["string"]["vars"]["comment"] = UTIL_String::truncate( $comment->getMessage(), 120, '...' );
            }
        }

        $event->setData($notificationData);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function onSocketFirstLogin( OW_Event $event )
    {
        $params = $event->getParams();
        if(isset($params['first']) && $params['first']){
            BOL_UserService::getInstance()->updateActivityStamp($params['user_id'], OW::getApplication()->getContext());
        }
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function onSocketLastLogout( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['user_id'];
        BOL_UserService::getInstance()->updateActivityStampForLastLogout($userId);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function onCodeChange( OW_Event $event )
    {
        $params = $event->getParams();
        $configReset = false;
        if (isset($params['config_reset'])) {
            $configReset = $params['config_reset'];
        }

        // update last code change config
//        OW::getConfig()->saveConfig('base', 'last_code_change', time(), 'Time for last code change.', false);
        $data['type'] = 'change_data';
        $data['exit'] = !$configReset;
        $data['time'] = time();
        $data['configReset'] = $configReset;

        // Send data to socket
        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $data, 'userId' => -1)));

        // Send data to Rabbitmq
        FRMSecurityProvider::sendUsingRabbitMQ($data, $data['type']);
    }

    /***
     * For frmfilemanager initialization
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function importFilesToFileWidget(OW_Event $event){
        $params = $event->getParams();
        if (isset($params['type']) && $params['type'] != 'profile'){
            return;
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();

        $dir0Id = $service->insert('frm:profile', 1, 'directory', time(), '', false, true);
        $all_users = BOL_UserDao::getInstance()->findAllIds();
        foreach ($all_users as $uId){
            $service->insert('frm:profile:'.$uId, $dir0Id,'directory', time(), '', true, true);
        }
    }

    /***
     * For frmfilemanager pricacy check
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function checkPrivacyForFileWidget(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['entityType']) || $params['entityType'] != 'profile'){
            return;
        }

        $data = $event->getData();
        if ($params['level'] <= 1){
            $data['read'] = false;
            $data['write'] = false;
        }
        elseif ($params['level'] == 2){
            // outside a profile folder: Block for now
            $read = false;
            if(!$read)
            {
                $data['read'] = false;
                $data['write'] = false;
                $data['name'] = OW::getLanguage()->text('base', 'private_page_heading');
            }
            else{
                $entityId = (int)$params['entityId'];
                $data['name'] = BOL_UserService::getInstance()->getDisplayName($entityId);
            }
        }
        elseif($params['level'] >= 3){
            // inside a profile, only my own
            $entityId = (int)$params['entityId'];
            $hasAccess = FRMFILEMANAGER_BOL_Service::getInstance()->hasProfileAccess($entityId);
            if (!$hasAccess){
                $data['read'] = false;
                $data['write'] = false;
            }
        }
        $event->setData($data);
    }
}
