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
class FRMPASSWORDCHANGEINTERVAL_BOL_Service
{
    CONST CATCH_REQUESTS_KEY = 'frmpasswordchangeinterval.catch';
    CONST SECTION_PASSWORD_VALIDATION_INFORMATION = 1;
    CONST SECTION_PASSWORD_VALIDATION_VALID_USERS = 2;
    CONST SECTION_PASSWORD_VALIDATION_INVALID_USERS = 3;
    CONST EXPIRED_TIME_OF_TOKEN = 864000; //5 day

    CONST DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITHOUT_NOTIF = 'normal';
    CONST DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITH_NOTIF = 'normal_notif';
    CONST DEAL_WITH_EXPIRED_PASSWORD_FORCE_WITH_NOTIF = 'force_notif';

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $passwordValidationDao;

    private function __construct()
    {
        $this->passwordValidationDao = FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance();
    }

    /**
     * @param $time
     * @param $userId
     * @return FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidation
     */
    public function updateTimePasswordChanged($time = null, $userId = null)
    {
        return $this->passwordValidationDao->updateTimePasswordChanged($time, $userId);
    }

    public function deleteAllUsersFromPasswordValidation()
    {
        $this->passwordValidationDao->deleteAllUsersFromPasswordValidation();
    }

    /**
     * @return FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidation
     */
    public function getCurrentUser()
    {
        return $this->passwordValidationDao->getCurrentUser();
    }

    /**
     * @param $passwordValidation
     * @return bool
     */
    public function isChangable($passwordValidation)
    {
        $dealWithExpiredPassword = OW::getConfig()->getValue('frmpasswordchangeinterval', 'dealWithExpiredPassword');
        if($this->isUserPasswordExpired($passwordValidation) || ($passwordValidation!=null && !$passwordValidation->valid)){
            if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITHOUT_NOTIF){
                return false;
            }else if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITH_NOTIF){
                $this->sendNotificationToCurrentUserForChangingPassword(OW::getUser()->getId());
                return false;
            }else if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_FORCE_WITH_NOTIF){
                $this->sendNotificationToCurrentUserForChangingPassword(OW::getUser()->getId());
                return true;
            }
        }
        return false;
    }


    public function userPasswordUpdate(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['user'])) {
            $user = $params['user'];
            $userId = $user->id;
            $passwordValidation = $this->getUserByUserId($userId);

            if ($passwordValidation == null) {
                $token = md5(UTIL_String::getRandomString(8, 5));
                $this->passwordValidationDao->createPasswordValidationObject($userId, true, $token, time());
            } else {
                $passwordValidation->setPasswordTime(time());
                $passwordValidation->setValid(true);
                $passwordValidation->setToken(null);
                FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->save($passwordValidation);

                OW::getEventManager()->call('notifications.remove', array(
                    'entityType' => 'frmpasswordchangeinterval',
                    'entityId' => $userId
                ));

            }

        }
    }


    /**
     * @return bool
     */
    public function isForceChangable()
    {
        $dealWithExpiredPassword = OW::getConfig()->getValue('frmpasswordchangeinterval', 'dealWithExpiredPassword');
        if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITHOUT_NOTIF){
            return false;
        }else if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITH_NOTIF){
            return false;
        }else if($dealWithExpiredPassword==FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_FORCE_WITH_NOTIF){
            return true;
        }
        return false;
    }

    /**
     * @param $userId
     */
    public function sendNotificationToCurrentUserForChangingPassword($userId)
    {
        $adminId = BOL_AuthorizationService::getInstance()->getSuperModeratorUserId();

        $notificationParams = array(
            'pluginKey' => 'frmpasswordchangeinterval',
            'action' => 'change-password',
            'entityType' => 'frmpasswordchangeinterval',
            'entityId' => $userId,
            'userId' => $userId,
            'time' => time(),
            'mobile_notification' => false,
        );
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($adminId));

        $notificationData = array(
            'string' => array(
                'key' => 'frmpasswordchangeinterval+description_change_password',
                'vars' => array('value' => $this->getUserProfileEditUrl())
            ),
            'avatar' => $avatars[$adminId]
        );
        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);
    }

    /**
     * @param $passwordValidation
     * @return bool
     */
    public function isUserPasswordExpired($passwordValidation){
        $expired_time = OW::getConfig()->getValue('frmpasswordchangeinterval', 'expire_time') * 60 * 60 * 24;
        if ($passwordValidation == null) {
            $userObject = OW::getUser()->getUserObject();
            if ( isset($userObject) && time() - OW::getUser()->getUserObject()->getJoinStamp() > $expired_time) {
                return true;
            }
        } else {
            if (time() - $passwordValidation->passwordTime > $expired_time) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $tokenTime
     * @return boolean
     */
    public function isTokenExpired($tokenTime){
        $expired_time = FRMPASSWORDCHANGEINTERVAL_BOL_Service::EXPIRED_TIME_OF_TOKEN;
        if (time() - $tokenTime > $expired_time) {
            return true;
        } else {
            return false;
        }
    }

    public function setAllUsersPasswordInvalid($sendEmail)
    {

        $this->passwordValidationDao->setAllUsersPasswordInvalid($sendEmail);
    }

    public function setAllUsersPasswordExpire()
    {
        $this->passwordValidationDao->setAllUsersPasswordExpire();
    }

    /**
     * @param $userId
     */
    public function setUserPasswordValid($userId)
    {
        $this->passwordValidationDao->setUserPasswordValid($userId);
    }

    /**
     * @param $userId
     */
    public function setUserPasswordInvalid($userId)
    {
        $this->passwordValidationDao->setUserPasswordInvalid($userId);
    }

    /***
     * @param null $searchValue
     * @param int $count
     * @return array
     */
    public function getAllUsersValid($searchValue = null, $count = 20)
    {
        return $this->passwordValidationDao->getAllUsersValid($searchValue, $count);
    }

    /**
     * @param $regenerate
     * @param userId
     */
    public function resendLinkToUserByUserId($regenerate, $userId){
        $this->passwordValidationDao->resendLinkToUserByUserId($regenerate, $userId);
    }

    /***
     * @param null $searchValue
     * @param int $count
     * @return array
     */
    public function getAllUsersInvalid($searchValue = null, $count = 20)
    {
        return $this->passwordValidationDao->getAllUsersInvalid($searchValue, $count);
    }

    /**
     * @param int $sectionId
     * @return array
     */
    public function getSections($sectionId)
    {
        $sections = array();
        $sections[] = array(
            'sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INFORMATION,
            'active' => $sectionId == FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INFORMATION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmpasswordchangeinterval.admin.section-id', array('sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INFORMATION)),
            'label' => OW::getLanguage()->text('frmpasswordchangeinterval','password_validation_header')
        );
        $sections[] = array(
            'sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_VALID_USERS,
            'active' => $sectionId == FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_VALID_USERS ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmpasswordchangeinterval.admin.section-id', array('sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_VALID_USERS)),
            'label' => OW::getLanguage()->text('frmpasswordchangeinterval','valid_users_header')
        );
        $sections[] = array(
            'sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INVALID_USERS,
            'active' => $sectionId == FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INVALID_USERS ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmpasswordchangeinterval.admin.section-id', array('sectionId' => FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INVALID_USERS)),
            'label' => OW::getLanguage()->text('frmpasswordchangeinterval','invalid_users_header')
        );
        return $sections;
    }

    /**
     * @param $sectionId
     * @param null $searchValue
     * @return array
     */
    public function getUsersBySectionId($sectionId, $searchValue = null){
        if($sectionId==FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_VALID_USERS){
            return $this->getAllUsersValid($searchValue);
        }else if($sectionId==FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INVALID_USERS){
            return $this->getAllUsersInvalid($searchValue);
        }
    }


    /**
     * @param $userId
     * @param $sectionId
     * @return string
     */
    public function getChangeStatusUrl($userId, $sectionId){
        if($sectionId==FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_VALID_USERS){
            return "javascript:if(confirm('".OW::getLanguage()->text('frmpasswordchangeinterval','invalidate_user_password_warning')."')){location.href='" . OW::getRouter()->urlForRoute('frmpasswordchangeinterval.admin.invalidate-password', array('userId' => $userId, 'sectionId' => $sectionId)) . "';}";
        }else if($sectionId==FRMPASSWORDCHANGEINTERVAL_BOL_Service::SECTION_PASSWORD_VALIDATION_INVALID_USERS){
            return "javascript:if(confirm('".OW::getLanguage()->text('frmpasswordchangeinterval','validate_user_password_warning')."')){location.href='" . OW::getRouter()->urlForRoute('frmpasswordchangeinterval.admin.validate-password', array('userId' => $userId, 'sectionId' => $sectionId)) . "';}";
        }
        return "";
    }

    /**
     * @param $token
     * @return FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidation
     */
    public function getUserByToken($token){
        return $this->passwordValidationDao->getUserByToken($token);
    }

    /**
     * @param $userId
     * @return FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidation
     */
    public function getUserByUserId($userId){
        return $this->passwordValidationDao->getUserByUserId($userId);
    }

    /**
     * @param OW_Event $event
     */
    public function onUserRegistered(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['forEditProfile']) && $params['forEditProfile']==true){
            return;
        }
        if(isset($params['userId'])){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user != null){
                $userInformation = $this->getUserByUserId($user->id);
                if($userInformation != null) {
                    $this->setUserPasswordValid($user->getId());
                }
            }
        }
    }

    public function catchAllRequestsExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'changeUserPassword'
        ));

        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'checkValidatePassword'
        ));

    }

    /**
     * @param OW_Event $event
     */
    public function onBeforeResetPasswordFormRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if ($params['user']) {
            $user = $params['user'];
            $passwordValidation = $this->getUserByUserId($user->id);
            if ($this->isForceChangable() && $passwordValidation != null && (!$passwordValidation->valid || ($passwordValidation->token != null && $this->isTokenExpired($passwordValidation->tokenTime)))) {
                UTIL_Url::redirect(OW::getRouter()->urlForRoute('frmpasswordchangeinterval.invalid-password', array('userId' => $passwordValidation->userId)));
            }
        }
    }

    /**
     * @param OW_Event $event
     */
    public function onAfterPasswordUpdate(OW_Event $event)
    {
        $params = $event->getParams();
        if ($params['userId'] != null) {
            $this->updateTimePasswordChanged(null, $params['userId']);
            if (!OW::getRequest()->isAjax()) {
                OW::getFeedback()->info(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_changed_successfully'));
            }
        }
    }

    /**
     * @param OW_Event $event
     */
    public function onAfterRoute(OW_Event $event)
    {
        $checkUriEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CHECK_URI_REQUEST));
        if(isset($checkUriEvent->getData()['ignore']) && $checkUriEvent->getData()['ignore']){
            return;
        }
        if (OW::getRequest()->isAjax() || $this->isUrlInWhitelist() || $this->isUserInWhitelist()) {
            return;
        }
        $passwordValidation = $this->getCurrentUser();
        if($this->isChangable($passwordValidation)){
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(!isset($event->getData()['isMobileVersion']) || $event->getData()['isMobileVersion']==false) {
                $attributeKey='FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval';
            }else{
                $attributeKey='FRMPASSWORDCHANGEINTERVAL_MCTRL_Iispasswordchangeinterval';
            }
            OW::getRequestHandler()->setCatchAllRequestsAttributes(FRMPASSWORDCHANGEINTERVAL_BOL_Service::CATCH_REQUESTS_KEY, array(
                OW_RequestHandler::ATTRS_KEY_CTRL => $attributeKey,
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMPASSWORDCHANGEINTERVAL_BOL_Service::CATCH_REQUESTS_KEY, 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'index');
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMPASSWORDCHANGEINTERVAL_BOL_Service::CATCH_REQUESTS_KEY, 'FRMPASSWORDCHANGEINTERVAL_MCTRL_Iispasswordchangeinterval', 'index');
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMPASSWORDCHANGEINTERVAL_BOL_Service::CATCH_REQUESTS_KEY, 'FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval', 'logoutAndGoToForgotPassword');
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMPASSWORDCHANGEINTERVAL_BOL_Service::CATCH_REQUESTS_KEY, 'FRMPASSWORDCHANGEINTERVAL_MCTRL_Iispasswordchangeinterval', 'logoutAndGoToForgotPassword');
        }
    }

    /**
     * @return bool
     */
    public function isUserInWhitelist()
    {
        if (!OW::getUser()->isAuthenticated() || OW::getUser()->isAdmin()) {
            return true;
        }
        $extraCriteria = OW_EventManager::getInstance()->trigger(new OW_Event('frm.passwordchangeinterval.whitelist.criteria'));
        if(isset($extraCriteria->getData()['addToWhiteList']) && $extraCriteria->getData()['addToWhiteList'])
        {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUrlInWhitelist()
    {
        if (OW::getRequest()->getRequestUri() == 'sign-out' || strpos(OW::getRequest()->getRequestUri(), 'changeuserpassword') > -1 || strpos(OW::getRequest()->getRequestUri(), 'changeuserpasswordwithuserid') > -1 || strpos(OW::getRequest()->getRequestUri(), 'checkvalidatepassword') > -1 || strpos(OW::getRequest()->getRequestUri(), 'resendlLink') > -1) {
            return true;
        }

        return false;
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    function on_notify_actions(BASE_CLASS_EventCollector $event)
    {
        $event->add(array(
            'section' => 'frmpasswordchangeinterval',
            'action' => 'change-password',
            'description' => OW::getLanguage()->text('frmpasswordchangeinterval', 'description_change_password_action'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmpasswordchangeinterval', 'title_change_password'),
            'sectionIcon' => 'ow_ic_clock'
        ));
    }

    function getUserProfileEditUrl(){
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(!isset($event->getData()['isMobileVersion']) || $event->getData()['isMobileVersion']==false) {
            return OW::getRouter()->urlForRoute('base_edit');
        }else{
            if (FRMSecurityProvider::checkPluginActive('frmprofilemanagement', true)) {
                return OW::getRouter()->urlForRoute('frmprofilemanagement.edit');
            }
            return OW::getRouter()->urlForRoute('base_user_profile', array('username'=> OW::getUser()->getUserObject()->username));
        }
    }

    public function onUserUnregister(OW_Event $event)
    {
        $params = $event->getParams();

        if ( !isset($params['deleteContent']) || !(bool) $params['deleteContent'] )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->deleteByUserId($userId);
        }
    }

    public function onAfterConsoleItemCollected(BASE_CLASS_EventCollector $event){
        $params = $event->getParams();
        if (isset($params['items']))
        {
            $items = $params['items'];
            $checkUriEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CHECK_URI_REQUEST));
            if(isset($checkUriEvent->getData()['ignore']) && $checkUriEvent->getData()['ignore']){
                return;
            }
            if (OW::getRequest()->isAjax() || $this->isUrlInWhitelist() || $this->isUserInWhitelist()) {
                return;
            }
            $passwordValidation = $this->getCurrentUser();
            if($this->isChangable($passwordValidation)){
                $counter = 0;
                foreach ($items as $item){
                    if(isset($item['item']) && $item['item'] instanceof BASE_CMP_MyProfileConsoleItem){
                        if(isset($item['item']->getItems()['main'])) {
                            $temp = $item['item']->getItems();
                            unset($temp['main']);
                            $item['item']->setItems($temp);
                        }

                    }else if(isset($item['item']) && !($item['item'] instanceof BASE_CMP_ConsoleSwitchLanguage)){
                        unset($items[$counter]);
                    }
                    $counter++;
                }

                $event->add($items);
            }
        }

    }
}
