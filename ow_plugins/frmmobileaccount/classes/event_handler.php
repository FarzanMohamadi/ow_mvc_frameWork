<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobileaccount.bol
 * @since 1.0
 */
class FRMMOBILEACCOUNT_CLASS_EventHandler
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
        $service = FRMMOBILEACCOUNT_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind("on.before.post.request.fail.for.csrf", array($service, "onBeforePostRequestFailForCSRF"));
        $eventManager->bind("before_mobile_validation_redirect", array($service, "onBeforeMobileValidationRedirect"));
        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'onPluginsInit'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'addStylesheetMobile'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_SIGNIN_BUTTON_ADD, array($service, 'changeSignInButton'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_JOIN_PAGE_RENDER, array($service, 'changeJoinPage'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_AUTOLOGIN_COOKIE_UPDATE, array($service, 'autoLoginCookieUpdate'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_SIGNIN_PAGE_RENDER, array($service, 'changeSignInPage'));
       // $eventManager->bind('base.splash_screen_exceptions', array($service, 'catchAllRequestsExceptions'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'catchAllRequestsExceptions'));
        $eventManager->bind('frmsms.verify_code_event_non_user', array($service, 'createUserAfterVerifyCode'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FORM_SIGNIN_RENDER, array($service, 'onBeforeFormSigninRender'));
        $eventManager->bind('redirect.forced.guest.new.page', array($service, 'redirectGuestToNewSigninPage'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'onAddMembersOnlyException'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($service, 'onAddMaintenanceModeExceptions'));
        $eventManager->bind('frm.on.users.import.subscription', array($service, 'onImportUsersForSubscription'));
        $eventManager->bind('base.questions_field_get_value', array($service, 'checkEmailIsSystematic'));
        $eventManager->bind('change.edit.question.data', array($service, 'checkEmailIsSystematicForEdit'));
        $eventManager->bind('base.on_before_user_create', array($service, 'onBeforeCreateUser'));
        $eventManager->bind('frm.passwordchangeinterval.whitelist.criteria', array($service, 'passwordChangeIntervalCriteria'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        $eventManager->bind('check.edit.profile.mandatory.user.approve', array($service, 'checkEditProfileMandatoryAction'));
        $eventManager->bind('frmadminnotification.send_info_after_user_registered', array($service, 'sendInfoAfterUserRegister'));
        $eventManager->bind('base.password_protected_exceptions', array($service, 'onAddPasswordProtectedExceptions'));
        $eventManager->bind('check.email.is.systematic', array($service, 'isSystematicCreatedBySystem'));
    }
}