<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_MCLASS_EventHandler
{
    /**
     * @var FRMMOBILESUPPORT_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMMOBILESUPPORT_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function init()
    {
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmmobilesupport.save.login.cookie', array($service, 'saveDeviceToken'));
        $eventManager->bind('after.feed.action', array($service, 'afterActionAdd'));
        $eventManager->bind('newsfeed.edit_post', array($service, 'afterEditPost'));
        $eventManager->bind(OW_EventManager::ON_USER_LOGOUT, array($service, 'userLogout'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'addMobileCss'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'checkForUsingOnlyMobile'));
        $eventManager->bind('notifications.on_add', array($service, 'onNotificationAdd'));
        $eventManager->bind('frmmobilesupport.browser.information', array($service, 'getBrowserInformation'));
        $eventManager->bind('frmmobilesupport.check.native.request', array($service, 'checkNativeRequest'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.password_protected_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.delete.expired.login.cookie', array($service, 'deleteDeviceToken'));
        $eventManager->bind('notifications.after_items_viewed', array($service, 'onNotificationViewed'));
        $eventManager->bind('mobile.notification.data.received', array($service, 'onMobileNotificationDataReceived'));
        $eventManager->bind("frmmobilesupport.send_message", array($service, "onSendMessage"));
        $eventManager->bind("mailbox.send_message_attachment", array($service, "onSendMessageAttachment"));
        $eventManager->bind("mailbox.mark_conversation", array($service, "onMarkConversation"));
        $eventManager->bind("mailbox.send_message", array($service, "onMailboxSendMessage"));
        $eventManager->bind("on.before.post.request.fail.for.csrf", array($service, "onBeforePostRequestFailForCSRF"));
        $eventManager->bind("before_mobile_validation_redirect", array($service, "onBeforeMobileValidationRedirect"));
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'onPluginsInit'));
        $eventManager->bind("frmsecurityessentials.before_csrf_token_check", array($service, "onBeforeCSRFCheck"));
        $eventManager->bind('frmmobilesupport.exclude.catch.request', array($service, 'excludeCatchGetInformationRequest'));
        $eventManager->bind("mailbox.after_message_removed", array($service, "onAfterMessageRemoved"));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'showDownloadLinks'));
        
        /* extracted from frmwidgetplus */
        $eventManager->bind('frmwidgetplus.general.before.view.render', array($service, 'generalBeforeViewRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_GROUP_VIEW_RENDER, array($service, 'beforeGroupViewRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_NEWS_VIEW_RENDER, array($service, 'beforeNewsViewRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_VIDEO_RENDER, array($service, 'beforeVideoViewRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_PHOTO_RENDER, array($service, 'beforePhotoViewRender'));
        $eventManager->bind('frm.on.before.competition.view.render', array($service, 'beforeCompetitionViewRender'));
        $eventManager->bind('frm.on.before.event.view.render', array($service, 'beforeEventViewRender'));
        $eventManager->bind('frm.on.before.profile.pages.view.render', array($service, 'beforeProfilePagesViewRender'));
        $eventManager->bind('frm.on.before.group.forum.view.render', array($service, 'beforeGroupForumViewRender'));
        $eventManager->bind("frmuserlogin.before_delete_session", array($service, "onBeforeSessionDelete"));
        $eventManager->bind("frmsecurityessentials.before_checking_idle", array($service, "onBeforeSessionDelete"));
        $eventManager->bind('frm.on.before.group.forum.topic.view.render', array($service, 'beforeGroupForumTopicViewRender'));
        $eventManager->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQNotificationRelease"));
        $eventManager->bind('check.url.webservice', array($service, "checkUrlIsWebServiceEvent"));
        $eventManager->bind('base.strip_raw_string', array($service, 'stripStringEvent'));
        $eventManager->bind('mailbox.get_conversation_info', array($service, 'getConversationInfo'));
        $eventManager->bind('frmactivitylimit.on_before_user_redirect_to_block_page',array($service, 'onBeforeUserLimitExceeded'));
        $eventManager->bind('frm.before.send.invite', array($service, 'fixInviteText'));
        /****************************************************/


    }
}