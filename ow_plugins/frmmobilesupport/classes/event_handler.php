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
class FRMMOBILESUPPORT_CLASS_EventHandler
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
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        //$eventManager->bind('frmmobilesupport.save.login.cookie', array($service, 'saveDeviceToken'));
        //$eventManager->bind(OW_EventManager::ON_USER_LOGOUT, array($service, 'userLogout'));
        //$eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'addMobileCss'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'showDownloadLinks'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'checkForUsingOnlyMobile'));
        $eventManager->bind('notifications.on_add', array($service, 'onNotificationAdd'));
        $eventManager->bind('after.feed.action', array($service, 'afterActionAdd'));
        $eventManager->bind('newsfeed.edit_post', array($service, 'afterEditPost'));
        $eventManager->bind('frmmobilesupport.browser.information', array($service, 'getBrowserInformation'));
        $eventManager->bind('frmmobilesupport.check.native.request', array($service, 'checkNativeRequest'));
        $eventManager->bind('admin.add_auth_labels', array($service, "onCollectAuthLabels"));
        $eventManager->bind('base.password_protected_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.delete.expired.login.cookie', array($service, 'deleteDeviceToken'));
        $eventManager->bind('notifications.after_items_viewed', array($service, 'onNotificationViewed'));
        $eventManager->bind("on.before.post.request.fail.for.csrf", array($service, "onBeforePostRequestFailForCSRF"));
        $eventManager->bind("before_mobile_validation_redirect", array($service, "onBeforeMobileValidationRedirect"));
        $eventManager->bind("frmmobilesupport.send_message", array($service, "onSendMessage"));
        $eventManager->bind("mailbox.send_message_attachment", array($service, "onSendMessageAttachment"));
        $eventManager->bind("mailbox.mark_conversation", array($service, "onMarkConversation"));
        $eventManager->bind("mailbox.send_message", array($service, "onMailboxSendMessage"));
        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'onPluginsInit'));
        $eventManager->bind("frmsecurityessentials.before_csrf_token_check", array($service, "onBeforeCSRFCheck"));
        $eventManager->bind("frmuserlogin.before_delete_session", array($service, "onBeforeSessionDelete"));
        $eventManager->bind("frmsecurityessentials.before_checking_idle", array($service, "onBeforeSessionDelete"));
        $eventManager->bind("mailbox.after_message_removed", array($service, "onAfterMessageRemoved"));
        $eventManager->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQNotificationRelease"));
        $eventManager->bind('base.append_markup', array($service, "addJSForWebNotifications"));
        $eventManager->bind('check.url.webservice', array($service, "checkUrlIsWebServiceEvent"));
        $eventManager->bind('base.on_socket_message_received', array($service, 'checkReceivedMessage'));
        $eventManager->bind('base.strip_raw_string', array($service, 'stripStringEvent'));
        $eventManager->bind('mailbox.get_conversation_info', array($service, 'getConversationInfo'));
        $eventManager->bind('frm.before.send.invite', array($service, 'fixInviteText'));
        $eventManager->bind('frmactivitylimit.on_before_user_redirect_to_block_page', array($service, 'onBeforeUserLimitExceeded'));
        $eventManager->bind('delete.all.users.active.cookies', array($service, 'deleteAllUsersActiveCookies'));
        $eventManager->bind('call_actions', array($service, 'callActions'));

    }
}