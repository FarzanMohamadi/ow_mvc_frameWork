<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurityessentials.bol
 * @since 1.0
 */
class FRMSECURITYESSENTIALS_CLASS_EventHandler
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
        $service = FRMSECURITYESSENTIALS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('feed.collect_privacy', array($service, 'onFeedCollectPrivacy'));
        $eventManager->bind('feed.on_item_render', array($service, 'onFeedItemRender'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRenderer'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'checkWarningAlert'));
        $eventManager->bind('video.collect_video_toolbar_items', array($service, 'onCollectVideoToolbarItems'));
        $eventManager->bind("questions.on_list_item_render", array($service, 'questionItemPrivacy'));
//        $eventManager->bind('photo.collect_photo_context_actions', array($service, 'onCollectPhotoContextActions'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_OBJECT_RENDERER, array($service, 'onBeforeObjectRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array($service, 'onBeforeUpdateStatusFormRenderer'));
        $eventManager->bind(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array($service, 'onAfterUpdateStatusFormRenderer'));
        $eventManager->bind('feed.after_activity', array($service, 'onAfterActivity'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_CREATE, array($service, 'onBeforeUpdateStatusFormCreate'));
        $eventManager->bind(FRMEventManager::ON_QUERY_FEED_CREATE, array($service, 'onQueryFeedCreate'));
        $eventManager->bind('plugin.privacy.get_action_list', array($service, 'privacyAddAction'));
        $eventManager->bind('plugin.privacy.on_change_action_privacy', array($service, 'privacyOnChangeActionPrivacy'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_CREATE_IN_PROFILE, array($service, 'onBeforeUpdateStatusFormCreateInProfile'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER, array($service, 'onBeforePhotoUploadFormRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_FORM_RENDERER, array($service, 'onBeforeVideoUploadFormRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_UPLOAD_COMPONENT_RENDERER, array($service, 'onBeforeVideoUploadComponentRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_PRIVACY_CHECK, array($service, 'getActionPrivacy'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array($service, 'onBeforeFeedItemRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FEED_ACTIVITY_CREATE, array($service, 'onBeforeFeedActivity'));
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'onFeedItemRenderer'));
        $eventManager->bind('photo.onReadyResponse', array($service, 'onReadyResponseOfPhoto'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_ALBUMS_RENDERER, array($service, 'onBeforeAlbumsRenderer'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_ALBUM_INFO_RENDERER, array($service, 'onBeforeAlbumInfoRenderer'));
        $eventManager->bind('plugin.privacy.check_permission', array($service, 'check_permission'));
        $eventManager->bind('photo.onAfterPhotoMove', array($service, 'eventAfterPhotoMove'));
        $eventManager->bind(FRMEventManager::ON_AFTER_LAST_PHOTO_FEED_REMOVED, array($service, 'onAfterLastPhotoRemoved'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_ALBUM_CREATE_FOR_STATUS_UPDATE, array($service, 'onBeforeAlbumCreateForStatusUpdate'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_QUESTIONS_DATA_PROFILE_RENDER, array($service, 'onBeforeQuestionsDataProfileRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_EMAIL_VERIFY_FORM_RENDER, array($service, 'onBeforeEmailVerifyFormRender'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'catchAllRequestsExceptions'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_PRIVACY_ITEM_ADD, array($service, 'onBeforePrivacyItemAdd'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_USER_INFORMATION_RENDER, array($service, 'onBeforeUsersInformationRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_INDEX_STATUS_ENABLED, array($service, 'onBeforeIndexStatusEnabled'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_ACTIVITY_TIMESTAMP,array($service,'logoutIfIdle'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FEED_RENDERED, array($service, 'onBeforeFeedRendered'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_USER_LOGIN,array($service,'regenerateSessionID'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_CREATE_FORM_USING_FIELD_PRIVACY,array($service,'onBeforeCreateFormUsingFieldPrivacy'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE,array($service,'onBeforeContentListQueryExecute'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_PHOTO_INIT,array($service,'onBeforePhotoInit'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_USER_FEED_LIST_QUERY_EXECUTE,array($service,'onBeforeUsedFeedListQueryExecuted'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_USER_DISAPPROVE_AFTER_EDIT_PROFILE,array($service,'onBeforeUserDisapproveAfterEditProfile'));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_BEFORE_FORM_CREATION,array($service,'onBeforeFormCreation'));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_AFTER_FORM_SUBMISSION,array($service,'onAfterFormSubmission'));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_BEFORE_HTML_STRIP,array($service,'onBeforeHTMLStrip'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRendererForJSCSRF'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRenderForIECheck'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'onAfterRoute'));


        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_AFTER_READ_URL_EMBED, array($service, "onAfterReadUrlEmbed"));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_CHECK_OBJECT_BEFORE_SAVE_OR_UPDATE, array($service, "onCheckObjectBeforeSaveOrUpdate"));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_CHECK_URL_EMBED, array($service, "onCheckUrlEmbed"));

        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_GENERATE_REQUEST_MANAGER, array($service, "onGenerateRequestManager"));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_CHECK_REQUEST_MANAGER, array($service, "onCheckRequestManager"));

        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_CHANGE_GROUP_PRIVACY_TO_PRIVATE , array($service, "onCheckGroupPrivacy"));

        $eventManager->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));

        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::ON_RENDER_USER_PRIVACY , array($service, "onRenderUserPrivacy"));

        $eventManager->bind(FRMEventManager::ON_AFTER_SIGNIN_FORM_CREATED , array($service, "onAfterSignInFormCreated"));
        $eventManager->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'checkIpIsValid'));
        $eventManager->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'pluginActivateOrInstall'));
        $eventManager->bind(OW_EventManager::ON_AFTER_PLUGIN_INSTALL, array($this, 'pluginActivateOrInstall'));
        $eventManager->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'checkDefaultPrivacyOfAllPlugins'));
        $eventManager->bind('admin.before_manually_plugin_update_page_render', array($service, 'addAllPluginUpdateButton'));
        $eventManager->bind(FRMSECURITYESSENTIALS_BOL_Service::CHECK_ACCESS_USERS_LIST,array($service,'checkAccessUsersList'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VALIDATING_FIELD,array($service,'validateFileField'));
        $eventManager->bind(OW_EventManager::ON_AFTER_PLUGIN_UNINSTALL,array($service,'deleteCronJobs'));

        $eventManager->bind('check.allow.customization.byRole',array($service,'allowPageCustomizationByRole'));
        $eventManager->bind('check.verifypeer.phpmailer',array($service,'checkVerifyPeerPHPMailer'));

        $eventManager->bind('base.before.action_user_delete', array($service,'actionDeleteUrl'));
        $eventManager->bind('frm.remove.unicode.emoji', array($service,'remove_emoji'));

        $eventManager->bind('check.user.access.getContents',array($service,'checkUserAccessGetContents'));

        $eventManager->bind( FRMSECURITYESSENTIALS_BOL_Service::CHECK_USER_CAN_CHANGE_ACCOUNT_TYPE, array($this,'checkUserCanChangeAccountType'));
    }


    public function checkDefaultPrivacyOfAllPlugins(OW_Event $e){
        $checkUriEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CHECK_URI_REQUEST));
        if(isset($checkUriEvent->getData()['ignore']) && $checkUriEvent->getData()['ignore']){
            return;
        }
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()){
            return;
        }
        if ( OW::getConfig()->configExists('frmsecurityessentials', 'privacySet')  &&  !OW::getConfig()->getValue('frmsecurityessentials','privacySet'))
        {
            $result = $this->initializePrivacyOfAllPlugins();
            OW::getConfig()->saveConfig('frmsecurityessentials', 'privacySet', $result);
        }
    }

    public function pluginActivateOrInstall( OW_Event $e )
    {
        $this->initializePrivacyOfAllPlugins();
    }

    public function initializePrivacyOfAllPlugins(){
        if(FRMSecurityProvider::checkPluginActive('privacy', true) && class_exists('PRIVACY_BOL_ActionService')) {
            $actionList = PRIVACY_BOL_ActionService::getInstance()->findAllAction();
            foreach ($actionList as $action) {
                $privacyValue = OW::getConfig()->getValue('frmsecurityessentials', $action->key);
                if (!isset($privacyValue)) {
                    OW::getConfig()->saveConfig('frmsecurityessentials', $action->key, $action->defaultValue);
                }
            }
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmsecurityessentials' => array(
                    'label' => $language->text('frmsecurityessentials', 'auth_group_label'),
                    'actions' => array(
                        'security-privacy_alert' => $language->text('frmsecurityessentials', 'auth_action_label_privacy_alert'),
                        'view-users-list' => $language->text('frmsecurityessentials', 'auth_action_label_view_users_list'),
                        'user-can-view-comments' => $language->text('frmsecurityessentials', 'auth_action_label_user_can_view_comments'),
                        'customize_user_profile'=>$language->text('frmsecurityessentials', 'auth_action_label_customize_user_profile')
                    )
                )
            )
        );
    }

    public function checkUserCanChangeAccountType( OW_Event $event ) {
        $params = $event->getParams();

        $editUserId = (int) $params['editUserId'];
        $viewerId = (int) $params['viewerId'];

        $event->setData(array(
            'user_can_change_account_type' => OW::getConfig()->getValue('frmsecurityessentials', 'user_can_change_account_type') && $editUserId == $viewerId
        ));
    }
}