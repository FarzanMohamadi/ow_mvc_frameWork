<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsms.bol
 * @since 1.0
 */
class FRMSMS_CLASS_EventHandler
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

    public function genericInit(){
        $service = FRMSMS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRenderer'));
        $eventManager->bind('base.on_before_email_verify_page_redirected', array($service, 'onBeforeVerifyEmailPageRedirect'));
        $eventManager->bind(FRMEventManager::ON_RENDER_JOIN_FORM, array($service, 'on_render_join_form'));
        $eventManager->bind('base.question_field_create', array($service, 'onQuestionFieldCreate'));
        $eventManager->bind('frmsms.on_after_sms_token_save', array($service, 'onAfterSmsTokenSave'));
        $eventManager->bind('base.questions_save_data', array($service, 'onQuestionProfileSaveData'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_CHECK_USER_STATUS, array($service, 'onBeforeRequestHandle'));
        $eventManager->bind('base.questions_save_data', array($service, 'onBeforeQuestionSaveData'));
        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'onPluginsInit'));
        $eventManager->bind('frmsms.phone_number_check', array($service, 'onPhoneNumberCheck'));
        $eventManager->bind('base.forgot_password.form_process',array($service,'processForm'));
        $eventManager->bind('base.forgot_password.form_generated',array($service,'forgotPasswordFormGenerated'));
        $eventManager->bind('frmsms.verify_code_event', array($service, 'verifyCodeEvent'));
        $eventManager->bind('frmsms.check_received_message', array($service, 'checkReceivedMessage'));
        $eventManager->bind('frmsms.delete.token',array($service,'deleteToken'));
        $eventManager->bind('frmsms.check.request.time.interval',array($service,'checkRequestTime'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER,array($service,'onUserRegister'));
        $eventManager->bind('base.before_render_old_password_input',array($service,'renderOldPassword'));
        $eventManager->bind('base.before_check_old_password',array($service,'checkOldPassword'));
        $eventManager->bind('frmsms.get.user.mobile.number', array($service, 'getUserMobileNumber'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'onUnregisterUser'));
    }
    
    public function init()
    {
        $this->genericInit();

        $service = FRMSMS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();

        // admin controller events
        $eventManager->bind(FRMSMS_BOL_Service::ON_GET_USERS_LIST_MENU_IN_ADMIN, array($service, 'onGetUsersListMenuInAdmin'));
        $eventManager->bind('frmsms.add.activate.sms.code.button', array($service, 'addActivateSMSCodeButton'));
        $eventManager->bind('frmsms.get.userlist.needs.activation', array($service, 'getUserListAndCountNeedsActivationSMS'));
        $eventManager->bind('frmsms.find.unverified.status.for.user.list', array($service, 'findUnverifiedSMSStatusForUserList'));
        $eventManager->bind('frmsms.activate.user.sms.code', array($service, 'activateUserSmsCode'));
        $eventManager->bind('on.get.searchQ.admin', array($service, 'onGetSearchQAdmin'));
        $eventManager->bind('on.admin.userlist.question.field.value', array($service, 'getUserListQuestionValue'));
    }

}