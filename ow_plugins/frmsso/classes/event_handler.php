<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsso.classes
 * @since 1.0
 */
class FRMSSO_CLASS_EventHandler
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

    public function genericInit()
    {
        $em = OW::getEventManager();
        $service = FRMSSO_BOL_Service::getInstance();
        $em->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'logoutUserIfRequired'));
        $em->bind(OW_EventManager::ON_PLUGINS_INIT, array($service, 'loginUserIfSSOLoggedIn'));
        $em->bind(FRMEventManager::ON_BEFORE_SIGNIN_BUTTON_ADD, array($service, 'changeSignInButton'));
        $em->bind(FRMEventManager::ON_BEFORE_JOIN_CONTROLLER_START, array($service, 'beforeJoinControllerStart'));
        $em->bind(FRMEventManager::ON_BEFORE_SEND_VERIFICATION_EMAIL, array($service, 'beforeSendVerificationEmail'));
        $em->bind(OW_EventManager::ON_BEFORE_USER_REGISTER, array($service, 'setUsernameUsingSSOSession'));
        $em->bind(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array($service, 'setEmailAndDisableUsername'));
        $em->bind(FRMEventManager::ON_BEFORE_PROFILE_EDIT_FORM_BUILD, array($service, 'onBeforeProfileEditFormBuild'));
        $em->bind(FRMEventManager::ON_AFTER_CHANGE_PASSWORD_WIDGET_ADDED, array($service, 'switchChangePasswordComponent'));
        $em->bind(FRMEventManager::ON_BEFORE_FORM_SIGNIN_RENDER, array($service, 'onBeforeFormSigninRender'));

    }

    public function init()
    {
        $this->genericInit();
    }
}