<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordchangeinterval.bol
 * @since 1.0
 */
class FRMPASSWORDCHANGEINTERVAL_MCLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function init()
    {
        $service = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($service, 'onAfterRoute'));
        $eventManager->bind(FRMEventManager::ON_AFTER_PASSWORD_UPDATE, array($service, 'onAfterPasswordUpdate'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_RESET_PASSWORD_FORM_RENDERER, array($service, 'onBeforeResetPasswordFormRenderer'));
        $eventManager->bind('base.members_only_exceptions', array($service, 'catchAllRequestsExceptions'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($service, 'onUserUnregister'));
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'frmpasswordchangeinterval'|| $params['entityType'] != 'frmpasswordchangeinterval' )
        {
            return;
        }

        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
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
        if(FRMSecurityProvider::checkPluginActive('frmprofilemanagement', true)) {
            $data['string'] = OW::getLanguage()->text('frmpasswordchangeinterval', 'description_change_password', array('value' => OW::getRouter()->urlForRoute('frmprofilemanagement.edit')));
        }

        //Notification on click logic is set here
        $event = new OW_Event('mobile.notification.data.received', array('pluginKey' => $params['pluginKey'],
            'entityType' => $params['entityType'],
            'data' => $data));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['url'])){
            $data['url']=$event->getData()['url'];
        }

        if(FRMSecurityProvider::checkPluginActive('frmprofilemanagement', true)){
            $e->setData($data);
        }
    }
}