<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'controllers' . DS . 'edit.php';

class FRMMOBILESUPPORT_BOL_WebServiceUser
{
    use BASE_CLASS_UploadTmpAvatarTrait;

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function login(){
        $adminApproveUser = false;

        if(!isset($_POST['username']) || !isset($_POST['password'])){
            return array('valid' => false, 'message' => 'input_error', 'admin_check' => $adminApproveUser);
        }

        $username = $_POST['username'];
        $username = trim($username);
        $password = $_POST['password'];

        $frmblockingipEvent = OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.attempt'));
        if(isset($frmblockingipEvent->getData()['lock']) && $frmblockingipEvent->getData()['lock']){
            return array("valid" => false, "message" => "authorization_error", 'user_blocked' => true, 'admin_check' => $adminApproveUser);
        }

        if(FRMSecurityProvider::checkPluginActive('frmpasswordchangeinterval', true)) {
            $user = BOL_UserService::getInstance()->findByUsername($username);
            if ($user != null) {
                $passwordValidation = FRMPASSWORDCHANGEINTERVAL_BOL_PasswordValidationDao::getInstance()->getCurrentUser($user->getId());
                if ($passwordValidation != null && !$passwordValidation->valid) {
                    if (FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isTokenExpired($passwordValidation->tokenTime)) {
                        FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->resendLinkToUserByUserId(true, $user->getId());
                    }
                    return array(
                        "valid" => false,
                        "message" => 'password_expired',
                        'admin_check' => $adminApproveUser);
                }
            }
        }

        $result = OW::getUser()->authenticate(new BASE_CLASS_StandardAuth($username, $password));

        if ( $result->isValid() )
        {
            $userId = OW::getUser()->getId();
            return $this->processLoginUser($userId);
        } else{
            OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.failed'));
            return array("valid" => false, "message" => "authorization_error", 'admin_check' => $adminApproveUser);
        }
    }

    public function loginSSO(){

        header('HTTP/1.0' . ' ' . '200 OK');
        header_remove('Status 403 Forbidden');
        
        $adminApproveUser = false;
        if(!isset($_POST['token'])){
            return array('valid' => false, 'message' => 'input_error', 'admin_check' => $adminApproveUser);
        }

        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        $username = trim($username);
        $token = $_POST['token'];

        $frmblockingipEvent = OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.attempt'));
        if(isset($frmblockingipEvent->getData()['lock']) && $frmblockingipEvent->getData()['lock']){
            return array("valid" => false, "message" => "authorization_error", 'user_blocked' => true, 'admin_check' => $adminApproveUser);
        }

        // SSO token validation:
        if( isset($token) ) {
            // get user information
            $header = array(
                'Authorization: Bearer ' . $token
            );
            $userInfoUrl = OW::getConfig()->getValue('sso', 'usersDetailsUrl');
            $userInfo = SSO_BOL_Service::getInstance()->askSSOServer($userInfoUrl, null, $header);
            if($userInfo !== null){

                $userById = BOL_UserService::getInstance()->findByJoinIp($userInfo['id']);
                $userByUsername = BOL_UserService::getInstance()->findByUsername($userInfo['username']);

                // If has foreign key:
                if (isset($userById) && $userById != null) {
                    $response = $this->loginAfterSSOSuccess($userById->id,$userInfo);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userById->getId());

                // If hasn't foreign key search for username:
                } elseif (isset($userByUsername) && $userByUsername != null) {
                    $response = $this->loginAfterSSOSuccess($userByUsername->id,$userInfo);
                    $userByUsername->joinIp = $userInfo['id'];
                    BOL_UserService::getInstance()->saveOrUpdate($userByUsername);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userByUsername->getId());

                    // else register user:
                } else {
                    $mobileNumber = $userInfo['username'];
                    $realName = $userInfo['name'] . ' ' . $userInfo['family'];
                    $username = $userInfo['username'];
                    $email = $username . '@smartsis.com';
                    $password = hash('sha256', time());
                    $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
                    $user = BOL_UserService::getInstance()->createUser($username, $password, $email, $accountType, true);
                    $questionService = BOL_QuestionService::getInstance();
                    $data = array();
                    $data['username'] = $username;
                    $data['email'] = $email;
                    if ($realName) {
                        $data['realname'] = $realName;
                    }
                    $questionService->saveQuestionsData($data, $user->getId());

                    if (isset($securityCode)) {
                        BOL_QuestionService::getInstance()->saveQuestionsData(array('form_name' => 'requiredQuestionsForm', 'securityCode' => $securityCode), $user->getId());
                    }

                    $user = BOL_UserService::getInstance()->findByEmail($email);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $mobileNumber), $user->getId());
                    $user->emailVerify = true;
                    $user->joinIp = $userInfo['id'];
                    BOL_UserService::getInstance()->saveOrUpdate($user);
                    $event = new OW_Event(OW_EventManager::ON_USER_REGISTER,
                        array('userId' => $user->getId(), 'method' => 'service')
                    );
                    $usersImportEvent = OW::getEventManager()->trigger(new OW_Event('on.users.import.register',array('mobile'=>$mobileNumber)));
                    $adminVerified = isset($usersImportEvent->getData()['verified']) ? (boolean)$usersImportEvent->getData()['verified'] : false;
                    if($adminVerified && !BOL_UserService::getInstance()->isApproved( $user->getId()))
                    {
                        BOL_UserService::getInstance()->approve($user->getId());
                    }
                    OW::getEventManager()->trigger($event);
                    $response = $this->loginAfterSSOSuccess($userById->id,$userInfo);
                }
                if(!empty($response)){
                    return $response;
                }else{
                    OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.failed'));
                    return array("valid" => false, "message" => "problem_in_user_login", 'userInfo' => $userInfo, 'response'=>$response);
                }
            }else{
                return array("valid" => false, "message" => "SSO_Server_User_not_found", 'userInfo' => $userInfo);
            }

        }else{
            return array("valid" => false, "message" => "authorization_error", 'SSO_valid' => false);
        }
    }

    /***
     * @return array
     * @throws Exception
     * This function aims to implement login and validation in serverside
     */
    public function loginSSOServerSide(){

        header('HTTP/1.0' . ' ' . '200 OK');
        header_remove('Status 403 Forbidden');

        $adminApproveUser = false;
        if(!isset($_GET['code'])){
            return array('valid' => false, 'message' => 'input_error_code_not_set', 'admin_check' => $adminApproveUser);
        }
        if(!isset($_GET['redirectUrl'])){
            return array('valid' => false, 'message' => 'input_error_redirectUrl_not_set', 'admin_check' => $adminApproveUser);
        }

        $code = $_GET['code'];
        $redirectUrl = $_GET['redirectUrl'];
        
        // check if the IP is blocked or not:

        $frmblockingipEvent = OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.attempt'));

        if(isset($frmblockingipEvent->getData()['lock']) && $frmblockingipEvent->getData()['lock']){
            return array("valid" => false, "message" => "authorization_error", 'user_blocked' => true, 'admin_check' => $adminApproveUser);
        }

        // SSO token validation:

        if( isset($code) ) {

            $userInfo = SSO_BOL_Service::getInstance()->mobileSSOVerifyCodeUpdateUser($code, $redirectUrl);

            if($userInfo !== null){

                if($userInfo === "invalid_grant_error"){
                    return array("valid" => false, "message" => "SSO_Server_invalid_grant_error");
                }
                $userById = BOL_UserService::getInstance()->findByJoinIp($userInfo['id']);
                $userByUsername = BOL_UserService::getInstance()->findByUsername($userInfo['username']);

                // If has foreign key:
                if (isset($userById) && $userById != null) {
                    $response = $this->loginAfterSSOSuccess($userById->id,$userInfo);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userById->getId());

                // If hasn't foreign key search for username:
                } elseif (isset($userByUsername) && $userByUsername != null) {
                    $response = $this->loginAfterSSOSuccess($userByUsername->id,$userInfo);
                    $userByUsername->joinIp = $userInfo['id'];
                    BOL_UserService::getInstance()->saveOrUpdate($userByUsername);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userByUsername->getId());

                // else register user:
                } else {
                    $mobileNumber = $userInfo['username'];
                    $realName = $userInfo['name'] . ' ' . $userInfo['family'];
                    $username = $userInfo['username'];
                    $email = $username . '@smartsis.com';
                    $password = hash('sha256', time());
                    $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
                    $user = BOL_UserService::getInstance()->createUser($username, $password, $email, $accountType, true);
                    $questionService = BOL_QuestionService::getInstance();
                    $data = array();
                    $data['username'] = $username;
                    $data['email'] = $email;
                    if ($realName) {
                        $data['realname'] = $realName;
                    }
                    $questionService->saveQuestionsData($data, $user->getId());

                    if (isset($securityCode)) {
                        BOL_QuestionService::getInstance()->saveQuestionsData(array('form_name' => 'requiredQuestionsForm', 'securityCode' => $securityCode), $user->getId());
                    }

                    $user = BOL_UserService::getInstance()->findByEmail($email);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $mobileNumber), $user->getId());
                    $user->emailVerify = true;
                    $user->joinIp = $userInfo['id'];
                    BOL_UserService::getInstance()->saveOrUpdate($user);
                    $event = new OW_Event(OW_EventManager::ON_USER_REGISTER,
                        array('userId' => $user->getId(), 'method' => 'service')
                    );
                    $usersImportEvent = OW::getEventManager()->trigger(new OW_Event('on.users.import.register',array('mobile'=>$mobileNumber)));
                    $adminVerified = isset($usersImportEvent->getData()['verified']) ? (boolean)$usersImportEvent->getData()['verified'] : false;
                    if($adminVerified && !BOL_UserService::getInstance()->isApproved( $user->getId()))
                    {
                        BOL_UserService::getInstance()->approve($user->getId());
                    }
                    OW::getEventManager()->trigger($event);
                    $response = $this->loginAfterSSOSuccess($userById->id,$userInfo);
                }
                if(!empty($response)){
                    return $response;
                }else{
                    OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.failed'));
                    return array("valid" => false, "message" => "problem_in_user_login", 'userInfo' => $userInfo, 'response'=>$response);
                }
            }else{
                return array("valid" => false, "message" => "SSO_Server_User_not_found", 'userInfo' => $userInfo);
            }

        }else{
            return array("valid" => false, "message" => "authorization_error", 'SSO_valid' => false);
        }
    }

    public function loginAfterSSOSuccess($userId,$ssoUserInfo){
        OW_User::getInstance()->login($userId, true, true);
        $day = FRMMOBILESUPPORT_BOL_Service::getInstance()->COOKIE_SAVE_DAY;
        $loginCookie = BOL_UserService::getInstance()->saveLoginCookie($userId, (time() + 86400 * $day));
        $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->currentUserApproved(true);
        if (!$adminApproveUser) {
            $response = array("valid" => false, "message" => "admin_check", 'admin_check' => $adminApproveUser);
            $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
            if (!empty($moderator_note)) {
                $response['user_id'] = $userId;
                $response['allow_update_profile'] = true;
                $response['moderator_note'] = $moderator_note['admin_message'];
                $response['code'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getProfileEditHash($userId);
            }
            return $response;
        }
        $fcmToken = $this->getFcmTokenFromPost();
        if($fcmToken != null){
            $this->addNativeDevice($userId, $_POST['fcmToken'], $loginCookie->getCookie());
        }
        $_POST['access_token'] = $loginCookie->getCookie();
        if(FRMSecurityProvider::checkPluginActive('frmuserlogin', true)){
            FRMUSERLOGIN_BOL_Service::getInstance()->updateActiveDetails();
        }
        $securityData = $this->getUserProfileSecurityData(OW::getUser()->getUserObject());
        OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.success'));
        $_POST['access_token'] =  $loginCookie->getCookie();
        return array("valid" => true, "cookies" => array('ow_login' => $loginCookie->getCookie()),'SSO_user_info'=>$ssoUserInfo, "message" => "success", 'admin_check' => $adminApproveUser, 'security' => $securityData);
    }


    public function processLoginUser($userId) {
        $day = FRMMOBILESUPPORT_BOL_Service::getInstance()->COOKIE_SAVE_DAY;
        $loginCookie = BOL_UserService::getInstance()->saveLoginCookie($userId, (time() + 86400 * $day));
        $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->currentUserApproved(true);
        if (!$adminApproveUser) {
            $response = array("valid" => false, "message" => "admin_check", 'admin_check' => $adminApproveUser);
            $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
            if (!empty($moderator_note)) {
                $response['user_id'] = $userId;
                $response['allow_update_profile'] = true;
                $response['moderator_note'] = $moderator_note['admin_message'];
                $response['code'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getProfileEditHash($userId);
            }
            return $response;
        }
        $fcmToken = $this->getFcmTokenFromPost();
        if($fcmToken != null){
            $this->addNativeDevice($userId, $_POST['fcmToken'], $loginCookie->getCookie());
        }
        $_POST['access_token'] = $loginCookie->getCookie();
        if(FRMSecurityProvider::checkPluginActive('frmuserlogin', true)){
            FRMUSERLOGIN_BOL_Service::getInstance()->updateActiveDetails();
        }
        $securityData = $this->getUserProfileSecurityData(OW::getUser()->getUserObject());
        OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.success'));
        return array("valid" => true, "cookies" => array('ow_login' => $loginCookie->getCookie()), "message" => "success", 'admin_check' => $adminApproveUser, 'security' => $securityData);
    }

    public function getSecurityInfo($userId = null){
        if ($userId == null && !OW::getUser()->isAuthenticated()) {
            return array();
        }
        if (OW::getUser()->isAuthenticated()) {
            return $this->getUserProfileSecurityData(OW::getUser()->getUserObject());
        } else if ($userId != null) {
            $user = BOL_UserService::getInstance()->findUserById($userId);
            return $this->getUserProfileSecurityData($user);
        }
        return array();
    }

    public function getFcmTokenFromPost(){
        if(isset($_POST['fcmToken']) &&
            !empty($_POST['fcmToken']) &&
            $_POST['fcmToken'] != null &&
            $_POST['fcmToken'] != "null"){
            return $_POST['fcmToken'];
        }

        return null;
    }

    public function logout(){
        $this->logoutProcess();
        return array("valid" => true, "message" => "logout_successfully");
    }

    public function logoutProcess(){
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $fcmToken = $this->getFcmTokenFromPost();
        if ($fcmToken != null) {
            $service->deleteDevice($fcmToken);
        }
        // Todo: do not need to logout user
        OW_Auth::getInstance()->getAuthenticator()->logout();
        $access_token = isset($_POST['access_token'])?$_POST['access_token']:null;
        BOL_LoginCookieDao::getInstance()->deleteByCookie($access_token);
    }

    public function addNativeDevice($userId, $token, $cookie){

        if($token == null || $cookie == null || $userId == null){
            return;
        }
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $type = FRMMOBILESUPPORT_BOL_Service::getInstance()->nativeFcmKey;
        $addDevice = false;
        $device = $service->findDevice($token);
        if($device){
            if($device->userId !=  $userId || $device->cookie != $cookie){
                $service->deleteDevice($token);
                $addDevice = true;
            }
        }else {
            $addDevice = true;
        }
        if($addDevice){
            $service->saveDevice($userId, $token, $type, $cookie);
        }
    }

    public function fetchUsersByMobile() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!isset($_POST['mobiles'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        $mobiles = $_POST['mobiles'];
        $mobiles = explode(',', $mobiles);

        $friendsWebService = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance();
        $friendsPluginEnabled = FRMSecurityProvider::checkPluginActive('friends', true);
        $usersInfo = array();
        $currentUserId = OW::getUser()->getId();

        foreach ($mobiles as $mobile) {
            $mobileNumber = trim(UTIL_HtmlTag::stripTagsAndJs($mobile));
            if (!empty($mobileNumber)) {
                $user = FRMSMS_BOL_Service::getInstance()->findUserByQuestionsMobile($mobileNumber);
                if ($user != null && $user->getId() != $currentUserId) {
                    $usersInfoItem = $this->getUserInformationByObject($user);
                    if (isset($_POST['makeFriend']) && $_POST['makeFriend'] && $friendsPluginEnabled) {
                        $friendship = FRIENDS_BOL_Service::getInstance()->addFriendship($currentUserId, $user->getId());
                        if ($friendship != null && $friendship->status != 'active') {
                            FRIENDS_BOL_Service::getInstance()->cancel($currentUserId, $user->getId());
                            FRIENDS_BOL_Service::getInstance()->addFriendship($currentUserId, $user->getId());
                        }
                        $usersInfoItem['isFriend'] = true;
                    } else {
                        $canSeeUser = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->canUserSeeFeed($currentUserId, $user->getId());
                        if ($canSeeUser) {
                            $usersInfoItem['isFriend'] = $friendsWebService->isFriend($user->getId(), $currentUserId);
                        }
                    }
                    $usersInfo[] = $usersInfoItem;
                }
            }
        }

        return $usersInfo;
    }

    public function blockUser() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(!isset($_POST['userId'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        $userId = $_POST['userId'];
        $blocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());
        if (!$blocked) {
            BOL_UserService::getInstance()->block($userId);
        } else {
            BOL_UserService::getInstance()->unblock($userId);
        }
        return array('valid' => true, 'isBlocked' => !$blocked, 'message' => 'changed');
    }

    public function changeAvatar($checkLogin = true, $userId = null, $fileName = 'file'){
        if($checkLogin && !OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if ($userId == null) {
            $userId = OW::getUser()->getId();
        }
        $image = null;

        if (isset($_FILES[$fileName])){
            if ( (int) $_FILES[$fileName]['error'] !== 0 ||
                !is_uploaded_file($_FILES[$fileName]['tmp_name']) ||
                !UTIL_File::validateImage($_FILES[$fileName]['name']) ){
                return array('valid' => false, 'message' => 'image_not_valid');
            }
            else{
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES[$fileName]['tmp_name']);
                if ($isFileClean) {
                    $image = $_FILES[$fileName];
                } else {
                    return array('valid' => false, 'message' => 'virus_detected');
                }
            }
        } else {
            return array('valid' => false, 'message' => 'empty_file');
        }

        $extInfo = pathinfo($image['name']);
        if(isset($extInfo['extension']) && strtolower($extInfo['extension']) == 'png') {
            return array('valid' => false, 'message' => 'extension_not_valid');
        }

        $key = BOL_AvatarService::getInstance()->getAvatarChangeSessionKey();
        if (!isset($key) || $key == null){
            BOL_AvatarService::getInstance()->setAvatarChangeSessionKey();
        }

        $uploadResult = $this->uploadTmpAvatar($image);
        if (!isset($uploadResult['result']) || !$uploadResult['result'] || !isset($uploadResult['url']) || !isset($uploadResult['key'])) {
            return array('valid' => false, 'message' => 'upload_avatar_error', 'result' => $uploadResult);
        }

        $avatarSet = BOL_AvatarService::getInstance()->setUserAvatar($userId, $uploadResult['url'], array('isModerable' => true, 'trackAction' => true ));

        if ( $avatarSet )
        {
            $avatar = BOL_AvatarService::getInstance()->findByUserId($userId);

            if ( $avatar )
            {
                $event = new OW_Event('base.after_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar->id,
                    'upload' => true,
                    'crop' => false
                ));
                OW::getEventManager()->trigger($event);
            }

            BOL_AvatarService::getInstance()->deleteUserTempAvatar($uploadResult['key']);
        } else {
            return array('valid' => false, 'message' => 'set_user_avatar_error');
        }
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrlByAvatarDto($avatar);
        $imageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($userId, $avatarUrl);
        return array('valid' => true, 'imageInfo' => $imageInfo, 'message' => 'changed', 'avatarUrl' => $avatarUrl);
    }

    public function changeCoverPhoto($userId = null, $fileName = 'file'){
        if(!FRMSecurityProvider::checkPluginActive('coverphoto', true)){
            return array('valid' => false, 'message' => 'plugin_not_enabled');
        }
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if (!isset($_FILES[$fileName])){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if ($userId == null) {
            $userId = OW::getUser()->getId();
        }

        $resp = COVERPHOTO_BOL_Service::getInstance()->uploadNewCover('user', $userId, 'new_cover', $fileName);
        if(!$resp['result']) {
            return array('valid' => false, 'message' => $resp['code']);
        }

        $avatarUrl = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('user', $userId);
        return array('valid' => true, 'message' => 'changed', 'Url' => $avatarUrl);
    }

    public function removeAvatar(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = (int) OW::getUser()->getId();

        $valid = BOL_AvatarService::getInstance()->deleteUserAvatar(OW::getUser()->getId());
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId, 2);
        $imageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($userId, $avatarUrl);
        return array('valid' => $valid, 'imageInfo' => $imageInfo,'userId' => $userId, 'avatarUrl' => $avatarUrl);
    }

    public function changePassword(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();

        if(!isset($_POST['oldPassword']) || !isset($_POST['newPassword'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        $oldPassword = UTIL_HtmlTag::stripJs($_POST['oldPassword']);
        $newPassword = UTIL_HtmlTag::stripJs($_POST['newPassword']);
        $validOldPassword = $this->checkOldPasswordIsValid( $userId, $oldPassword );
        if (!$validOldPassword) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if (FRMSecurityProvider::checkPluginActive('frmpasswordstrengthmeter', true)) {
            $newPasswordValidator = FRMPASSWORDSTRENGTHMETER_BOL_Service::getInstance()->checkPasswordValid($newPassword);
            if (!$newPasswordValidator['valid']) {
                return array('valid' => false, 'error_data' => $newPasswordValidator['error']);
            }
        }
        BOL_UserService::getInstance()->updatePassword( $userId, $newPassword );
        return array('valid' => true, 'message' => 'changed');
    }



    private function checkOldPasswordIsValid($userId,$oldPassword)
    {
        if ($this->hasTokenRequirementPlugin())
        {
            $validOldPassword = FRMSMS_BOL_Service::getInstance()->checkOldPassword($userId,$oldPassword);
        } else {
            $validOldPassword = BOL_UserService::getInstance()->isValidPassword($userId, $oldPassword);
        }
        return $validOldPassword;
    }


    /**
     * check requirement plugins installed to check old password
     * @return bool
     * @throws Redirect404Exception
     */
    private function hasTokenRequirementPlugin()
    {
        if(!FRMSecurityProvider::checkPluginActive('frmmobileaccount', true)) {
            return false;
        }
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return false;
        }

        return true;
    }

    public function checkLogin(){
        $valid = false;
        $isMobileValid = true;
        $lastUserMobileSaved = '';
        $message = 'authorization_error';
        $forcedToChangePassword = false;
        $unread_conversations_count = 0;
        $unread_groups_count = 0;
        $unread_notifications_count = 0;
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $generalWebService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
        $adminApproveUser = true;

        $versionInfo = '';
        if ($service->checkUrlIsWebService(false)) {
            $versionInfo = $service->getAppInformation(FRMMOBILESUPPORT_BOL_Service::getInstance()->nativeFcmKey, '');
        }
        $userInfo = null;
        $response = array();
        $keepAllQuestions = false;
        if(OW::getUser()->isAuthenticated()){
            if(empty(trim(OW::getUser()->getUserObject()->accountType))) {
                $response['account_type_labels'] = $this->getAccountLabelTypes();
                $response['default_account_type_labels'] = $this->getDefaultAccountLabels();
            }

            if(OW::getUser()->isAuthenticated()){
                $fillQuestions = array();
                $questions = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId());
                if (!empty($questions)) {
                    $fillQuestions['username'] = BOL_UserService::getInstance()->getUserName(OW::getUser()->getId());
                    $fillQuestions [] = $this->prepareQuestions($questions,array(),$keepAllQuestions);

                }
                $response['fillQuestions'] = $fillQuestions;
            }

            $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->currentUserApproved(true, $questions);
            $userInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById(OW::getUser()->getId());
            $valid = true;
            $message = 'authenticate_before';

            if(FRMSecurityProvider::checkPluginActive('frmpasswordchangeinterval', true)) {
                $passwordValidation = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->getCurrentUser();
                $forcedToChangePassword = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isChangable($passwordValidation);
                if ($passwordValidation != null && !$passwordValidation->valid) {
                    $valid = false;
                    $message = 'password_expired';
                }
            }
            if (!$adminApproveUser) {
                $valid = false;
                $message = 'admin_check';
                $userId = OW::getUser()->getId();
                $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
                if (!empty($moderator_note)) {
                    $response['user_id'] = $userId;
                    $response['allow_update_profile'] = true;
                    $response['moderator_note'] = $moderator_note['admin_message'];
                    $response['code'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getProfileEditHash($userId);
                }
            }

            if(FRMSecurityProvider::checkPluginActive('mailbox', true)){
                $unread_conversations_count = MAILBOX_BOL_ConversationService::getInstance()->getUnreadConversationsCount();
            }

            if(FRMSecurityProvider::checkPluginActive('frmsms', true)){
                $mobileService = FRMSMS_BOL_Service::getInstance();
                $isMobileVerifyObject = $mobileService->findUserMobileVerifyObject();
                if (isset($isMobileVerifyObject)) {
                    $isMobileValid = (bool) $isMobileVerifyObject->valid;
                    $lastUserMobileSaved = $isMobileVerifyObject->mobile;
                }
            }

            $unread_groups_count = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->getUnreadGroupsCount();
            $unread_notifications_count = FRMMOBILESUPPORT_BOL_WebServiceNotifications::getInstance()->getNewNotificationsCount();
        }

        $passwordStrength = 1;
        $minimumRequirementPasswordStrength = OW::getConfig()->getValue('frmpasswordstrengthmeter','minimumRequirementPasswordStrength');
        if ($minimumRequirementPasswordStrength != null) {
            $passwordStrength = $minimumRequirementPasswordStrength;
        }

        $isMaintenanceModeEnabled = $generalWebService->isMaintenanceModeEnabled();

        $response = array_merge($response,
            array('valid' => $valid,
            'message' => $message,
            'admin_check' => $adminApproveUser,
            'isMobileValid' => $isMobileValid,
            'lastUserMobileSaved' => $lastUserMobileSaved,
            'user' => $userInfo,
            'maintenance' => $isMaintenanceModeEnabled,
            'version' => $versionInfo,
            'unread_notifications_count' => (int) $unread_notifications_count,
            'unread_conversations_count' => (int) $unread_conversations_count,
            'unread_groups_count' => (int) $unread_groups_count,
            'password_change' => $forcedToChangePassword,
            'password_strength' => (int) $passwordStrength
            )
        );
        return $response;
    }

    public function getUserInformation($includeProfileInformation = false, $detailInfo = false){
        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        $userId = null;
        if(isset($_GET['userId'])){
            $userId = $_GET['userId'];
        }else if(isset($_GET['username'])){
            $user = BOL_UserService::getInstance()->findByUsername($_GET['username']);
            if($user != null){
                $userId = $user->getId();
            }
        }else if(OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
        }

        if($userId == null){
            return array();
        }

        return $this->getUserInformationById($userId, $includeProfileInformation, $detailInfo);
    }

    public function getUserInformationById($userId, $includeProfileInformation = false, $detailInfo = false, $params = array()){
        if($userId == null){
            return array();
        }

        $user = null;
        if (isset($params['cache']['users']['id'][$userId])) {
            $user = $params['cache']['users']['id'][$userId];
        }
        if ($user == null) {
            $user = BOL_UserService::getInstance()->findUserById($userId);
        }
        $data = $this->getUserInformationByObject($user, $includeProfileInformation, $detailInfo, $params);

        return $data;
    }

    public function getUsersInfoByIdList($userIds, $useIndex = false) {
        if (sizeof($userIds) == 0) {
            return array();
        }
        $users = array();
        $usersObject = BOL_UserService::getInstance()->findUserListByIdList($userIds);
        $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($userIds);
        foreach ($usersObject as $userObject) {
            $username = null;
            if (isset($usernames[$userObject->id])) {
                $username = $usernames[$userObject->id];
            }

            $avatarUrl = null;
            if (isset($avatars[$userObject->id])) {
                $avatarUrl = $avatars[$userObject->id];
            }
            $userData = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatarUrl, $username);
            if ($useIndex) {
                $users[$userObject->id] = $userData;
            } else {
                $users[] = $userData;
            }
        }
        return $users;
    }

    public function getUserInformationByObject($user, $includeProfileInformation = false, $detailInfo = false, $params = array()){
        if($user == null){
            return array();
        }
        $data = $this->populateUserData($user, null, null, $detailInfo, false, $params);

        $useResponseTypes = false;
        $responseTypes = array();
        if (isset($_POST['data_types'])) {
            $useResponseTypes = true;
            $responseTypes = $_POST['data_types'];
            $responseTypes = explode(",", $responseTypes);
        }

        $blockedViewPermission = true;
        if(OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $user->id) {
            $blockInfo = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getBlockUsersInfo((int)OW::getUser()->getId(), (int)$user->id);
            $blockedViewPermission = $blockInfo['permission'];
            $data['isBlocked'] = $blockInfo['isBlocked'];
            $data['blockedBy'] = $blockInfo['blockedBy'];
        }

        if($blockedViewPermission && $includeProfileInformation){
            $showProfileQuestions = !$useResponseTypes || in_array('profile_info', $responseTypes) ? true : false;
            $showFriends = !$useResponseTypes || in_array('friends', $responseTypes) ? true : false;
            $showPosts = !$useResponseTypes || in_array('posts', $responseTypes) ? true : false;
            $showVideos = !$useResponseTypes || in_array('videos', $responseTypes) ? true : false;
            $showAlbums = !$useResponseTypes || in_array('albums', $responseTypes) ? true : false;
            $showBlogs = !$useResponseTypes || in_array('blogs', $responseTypes) ? true : false;
            $showStories = !$useResponseTypes || in_array('story', $responseTypes) ? true : false;
            $showEvents = !$useResponseTypes || in_array('events', $responseTypes) ? true : false;
            $showGroups = !$useResponseTypes || in_array('groups', $responseTypes) ? true : false;
            $showSessions = !$useResponseTypes || in_array('sessions', $responseTypes) ? true : false;
            $showSuggests = !$useResponseTypes || in_array('suggests', $responseTypes) ? true : false;
            $showMentions = !$useResponseTypes || in_array('mentions', $responseTypes) ? true : false;
            $showMutuals = !$useResponseTypes || in_array('mutuals', $responseTypes) ? true : false;
            $showFollowingFollowers = !$useResponseTypes || in_array('followers', $responseTypes) ? true : false;

            if ($showProfileQuestions) {
                $data['profileInformation'] = $this->getUserProfileInformation($user->id);
                if(FRMSecurityProvider::checkPluginActive('coverphoto', true)){
                    $data['coverPhoto'] = COVERPHOTO_BOL_Service::getInstance()->getCoverURL('user', $user->id);
                }
            }
            if ($showFriends) {
                $data['friends'] = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->getUserFriends($user->id);
            }
            if ($showPosts) {
                $data['posts'] = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->userProfilePosts($user->id);
            }
            if ($showGroups) {
                $data['groups'] = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->getGroupsByUserId($user->id);
            }
            if ($showStories) {
                $data['highlightsList'] = FRMMOBILESUPPORT_BOL_WebServiceHighlight::getInstance()->getUserHighlightsList($user->id);
            }
            if ($showEvents) {
                $data['events'] = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance()->getEventsByUserId($user->id);
            }
            if ($showVideos) {
                $data['videos'] = FRMMOBILESUPPORT_BOL_WebServiceVideo::getInstance()->getUserVideosById($user->id);
            }
            if ($showAlbums) {
                $data['albums'] = FRMMOBILESUPPORT_BOL_WebServicePhoto::getInstance()->getUserAlbumsByUserId($user->id);
            }
            if ($showBlogs) {
                $data['blogs'] = FRMMOBILESUPPORT_BOL_WebServiceBlogs::getInstance()->getUserBlogsWithId($user->id);
            }
            if ($showMentions) {
                $data['mentions'] = FRMMOBILESUPPORT_BOL_WebServiceSearch::getInstance()->searchMentions($user->id);
            }

            if ($showFollowingFollowers && FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                $data['followers'] =  NEWSFEED_BOL_FollowDao::getInstance()->findFollowersCount($user->id);
                $data['followings'] = NEWSFEED_BOL_FollowDao::getInstance()->findFollowingCount($user->id);
                $data['posts_count'] = NEWSFEED_BOL_ActionFeedDao::getInstance()->findActionsCountByFeedId($user->id);
            }
            $data['friends_count'] = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->getUserFriendsCount($user->id);
            $data['user_status'] = $this::getInstance()->getUserStatus($user->id);

            if(OW::getUser()->isAuthenticated() && OW::getUser()->getId() == $user->id){
                if ($showSessions) {
                    $data['session'] = $this->getUserSessionInformation($user->id);
                }
                $data['requests_count'] = $this->getRequestsCount($user->id);
                if ($showSuggests) {
                    $data['suggests'] = FRMMOBILESUPPORT_BOL_WebServiceSuggest::getInstance()->getUserSuggest($user->id);
                }
                if (FRMSecurityProvider::checkPluginActive('frmfilemanager', true)) {
                    $filemanagerService = FRMFILEMANAGER_BOL_Service::getInstance();
                    $data['personalFolders'] = $filemanagerService->getSubfolders('profile', $user->id);
                    $data['personalFiles'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()
                        ->preparedFileListByEntity('profile', $user->id);
                }
            }else if(OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $user->id){
                if ($showMutuals) {
                    $data['mutual'] = FRMMOBILESUPPORT_BOL_WebServiceMutual::getInstance()->getUserMutual(OW::getUser()->getId(), $user->id);
                }

                $data['online'] = $this->isUserOnline($user->id);
                $data['follower'] = $this->isUserFollower($user->id);
                $data['followable'] = $this->isUserFollowable($user->id,  $data['isBlocked'], $data['follower']);
            }

            if(OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('market', true)){
                $data['isSeller'] = MARKET_BOL_Service::getInstance()->getUserRoleStatus($user->id);
                $data['storeName'] = MARKET_BOL_Service::getInstance()->getUserStoreNameById($user->id);
            }

            $data['security'] = $this->getUserProfileSecurityData($user);
        }
        return $data;
    }

    public function isUserFollowable($userId, $blocked = null, $follower = null) {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return false;
        }
        if(!OW::getUser()->isAuthenticated()) {
            return false;
        }
        if ($blocked == null) {
            $blocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());
        }
        if ($blocked) {
            return false;
        }
        return true;
    }

    public function follow() {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $userId = null;

        if(isset($_GET['userId'])){
            $userId = (int) $_GET['userId'];
        }
        if ($userId == null) {
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!$this->isUserFollowable($userId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        NEWSFEED_BOL_Service::getInstance()->addFollow(OW::getUser()->getId(), 'user', $userId);
        return array('valid' => true, 'follow' => true, 'userId' => $userId);
    }

    public function unFollow() {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $userId = null;

        if(isset($_GET['userId'])){
            $userId = (int) $_GET['userId'];
        }
        if ($userId == null) {
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!$this->isUserFollowable($userId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        NEWSFEED_BOL_Service::getInstance()->removeFollow(OW::getUser()->getId(), 'user', $userId);
        return array('valid' => true, 'follow' => false, 'userId' => $userId);
    }

    public function isUserFollower($userId) {
        //return true if current user is follower of $userId
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return false;
        }
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        $currentUserId = OW::getUser()->getId();
        return NEWSFEED_BOL_Service::getInstance()->isFollow($currentUserId, 'user', $userId);
    }

    public function isUserOnline($userId){
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        $privacyEvent = OW::getEventManager()->trigger(new OW_Event('plugin.privacy.check_visibility', array('user_id' => $userId, 'visitor_user_id' => OW::getUser()->getId())));
        if ($privacyEvent->getData()['is_visible']) {
            $onlineObj = BOL_UserService::getInstance()->findOnlineUserById($userId);
            if ($onlineObj != null) {
                return true;
            }
        }
        return false;
    }

    public function searchFriends(){
        $userId = null;
        if(isset($_GET['userId']) && is_numeric($_GET['userId'])){
            $userId = $_GET['userId'];
        }

        if($userId == null && OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
        }

        if($userId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $canSeeFriends = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($userId, 'friends_view', 'friends');
        if(!$canSeeFriends){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $search = '';
        if(isset($_GET['search'])){
            $search = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['search'], true, true);
        }

        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        $first = 0;
        if(isset($_GET['first'])){
            $first = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['first'], true, true);
            $first = (int) $first;
        }

        $param = array(
            'search' => $search,
            'userId' => $userId,
            'count' => $count,
            'first' => $first
        );

        $event = OW::getEventManager()->trigger(new OW_Event('plugin.friends.get_friend_list_by_display_name', $param));
        $friendsIds = $event->getData();
        $users = array();
        if(!empty($friendsIds)) {
            $usersObject = BOL_UserService::getInstance()->findUserListByIdList($friendsIds);
            $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($friendsIds);
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($friendsIds, 2);
            foreach ($usersObject as $userObject) {
                $username = null;
                if (isset($usernames[$userObject->id])) {
                    $username = $usernames[$userObject->id];
                }

                $avatarUrl = null;
                if (isset($avatars[$userObject->id])) {
                    $avatarUrl = $avatars[$userObject->id];
                }
                $users[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatarUrl, $username, false, true);
            }
        }

        return $users;
    }

    public function friendSuggestion() {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!isset($_POST['phones'])) {
            return array('valid' => false, 'message' => 'empty_mobile_number');
        }

        $phones = $_POST['phones'];
        $phones = explode(',', $phones);

        if (empty($phones)) {
            return array('valid' => false, 'message' => 'phone_list_empty');
        }

        $userIds = BOL_QuestionService::getInstance()->findUsersByQuestionAndTextAnswers("mobile_number", $phones);
        $userIds = array_diff($userIds, [OW::getUser()->getId()]);
        $userInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($userIds, true);
        $userFriendshipInfo = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->getFriendshipsInformation(OW::getUser()->getId(), $userIds);
        foreach($userInfo as $userInfoItem) {
            $userId = $userInfoItem['id'];
            $userInfo[$userId]['friendship'] = $userFriendshipInfo[$userId];
        }
        $userInfo = array_values($userInfo);

        return $userInfo;
    }

    public function populateUserData($user, $avatarUrl = null, $displayName = null, $detailInfo = false, $checkOnline = false, $params = array()){
        $data = array();
        if($avatarUrl == null){
            if (isset($params['usersInfo']['avatars'][$user->id])) {
                $avatarUrl = $params['usersInfo']['avatars'][$user->id];
            }
            if ($avatarUrl == null) {
                $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($user->id, 2);
            }
        }
        if($displayName == null){
            $displayName = BOL_UserService::getInstance()->getDisplayName($user->id);
        }

        $data['avatarUrl'] = $avatarUrl;
        $data['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo((int) $user->id, $avatarUrl);
        $data['email'] = $user->getEmail();
        $data['id'] = (int) $user->id;
        $data['username'] = $user->getUsername();
        $data['name'] = $displayName;
        if($detailInfo){
            $data['profileUrl'] = OW::getRouter()->urlForRoute('base_user_profile', array('username' => $user->getUsername()));
        }
        if($checkOnline){
            $data['online'] = $this->isUserOnline($user->id);
        }
        if (isset($params['security'])) {
            $data['security'] = $params['security'];
        }
        return $data;
    }

    public function getRequests(){
        if(!OW::getUser()->isAuthenticated()){
            return array();
        }
        $invitation = array();
        $invitation['items'] = $this->prepareRequests(OW::getUser()->getId());
        return $invitation;
    }

    public function getUserStatus($userId) {
        $settings = BOL_ComponentEntityService::getInstance()->findSettingList('profile-BASE_CMP_AboutMeWidget', $userId, array('content'));
        $content = '';
        if (isset($settings)) {
            $content = empty($settings['content']) ? '' : $settings['content'];
        }
        return $content;
    }

    public function getRequestsCount($userId){
        $listCount = 0;
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if($pluginActive){
            $groupSize = GROUPS_BOL_Service::getInstance()->findInvitedGroupsCount($userId);
            $listCount += (int) $groupSize;
        }

        $pluginActive = FRMSecurityProvider::checkPluginActive('event', true);
        if($pluginActive){
            $eventSize = EVENT_BOL_EventService::getInstance()->findUserInvitedEventsCount($userId);
            $listCount += (int) $eventSize;
        }

        $pluginActive = FRMSecurityProvider::checkPluginActive('friends', true);
        if($pluginActive){
            $listCount += sizeof(FRIENDS_BOL_Service::getInstance()->findRequestList($userId, time(), 0, 100));
        }

        return $listCount;
    }

    public function prepareRequests($userId){
        $data = array();

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $generalService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
        $pluginActive = FRMSecurityProvider::checkPluginActive('friends', true);
        if($pluginActive) {
            $friendsInvitation = FRIENDS_BOL_Service::getInstance()->findRequestList($userId, time(), $first, $count);
            foreach ($friendsInvitation as $friendInvitation) {
                $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($friendInvitation->userId);
                $data[] = array(
                    'title' => $generalService->stripString(BOL_UserService::getInstance()->getDisplayName($friendInvitation->userId)),
                    'description' => '',
                    'image' => $avatarUrl,
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $friendInvitation->userId, $avatarUrl),
                    'type' => 'friend',
                    'invitation' => true,
                    'id' => (int)$friendInvitation->userId,
                );
            }
        }

        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if($pluginActive) {
            $groupsInvitation = GROUPS_BOL_Service::getInstance()->findInvitedGroups($userId, $first, $count);
            foreach ($groupsInvitation as $groupInvitation) {
                $imageUrl = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($groupInvitation);
                $data[] = array(
                    'title' => $generalService->stripString($groupInvitation->title),
                    'description' => $generalService->stripString($groupInvitation->description),
                    'image' => $imageUrl,
                    'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $groupInvitation->id, $imageUrl, 'group'),
                    'type' => 'group',
                    'invitation' => true,
                    'id' => (int)$groupInvitation->id,
                );
            }
        }

        $page = null;
        if(isset($_GET['page'])){
            $page = $_GET['page'];
        }

        if($page == null && $first != null){
            $page = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageNumber($first);
        }

        if($page == null){
            $page= 1;
        }

        $pluginActive = FRMSecurityProvider::checkPluginActive('event', true);
        if($pluginActive) {
            $eventInvitations = EVENT_BOL_EventService::getInstance()->findUserInvitedEvents($userId, $page, null);
            foreach ($eventInvitations as $eventInvitation) {
                $data[] = array(
                    'title' => $generalService->stripString($eventInvitation->title),
                    'description' => $generalService->stripString($eventInvitation->description),
                    'image' => EVENT_BOL_EventService::getInstance()->generateImageUrl($eventInvitation->image),
                    'type' => 'event',
                    'invitation' => true,
                    'id' => (int)$eventInvitation->id,
                );
            }
        }

        return $data;
    }

    public function getUserSessionInformation($userId){
        $sessions = array();
        $loginDetails = array();
        $securityPluginActive = FRMSecurityProvider::checkPluginActive('frmuserlogin', true);
        if($securityPluginActive){
            $details = FRMUSERLOGIN_BOL_Service::getInstance()->getUserLoginDetails($userId);
            if($details != null) {
                foreach ($details as $detail) {
                    $loginDetails[] = array(
                        'time' => $detail->time,
                        'browser' => $detail->browser,
                        'ip' => $detail->ip,
                        'id' => (int) $detail->id,
                    );
                }
            }
            $first = 0;
            $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
            if(isset($_GET['first'])){
                $first = (int) $_GET['first'];
            }
            $page = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageNumber($first);
            $accessToken = null;
            if(isset($_POST['access_token'])){
                $accessToken = $_POST['access_token'];
            }
            if($accessToken == null){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $sessionDetails = FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->getUserActiveDetailsWithoutEmptyLoginCookie($userId, $page, $count);
            $currentSession = FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->getItemByLoginCookie($accessToken);
            if($currentSession == null || $currentSession->userId != OW::getUser()->getId()){
                return array('valid' => false, 'message' => 'authorization_error');
            }

            if($sessionDetails != null) {
                foreach ($sessionDetails as $sessionDetail) {
                    $id = (int) $sessionDetail->id;
                    $sessions[] = array(
                        'time' => $sessionDetail->time,
                        'browser' => $sessionDetail->browser,
                        'ip' => $sessionDetail->ip,
                        'current' => $currentSession->sessionId == $sessionDetail->sessionId,
                        'id' => (int) $id
                    );
                }
            }
        }

        return array('logins' => $loginDetails, 'sessions' => $sessions);
    }

    public function terminateSession(){
        $securityPluginActive = FRMSecurityProvider::checkPluginActive('frmuserlogin', true);
        if(!$securityPluginActive){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $sessionId = null;
        if(isset($_POST['id'])){
            $sessionId = $_POST['id'];
        }

        if($sessionId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $accessToken = null;
        if(isset($_POST['access_token'])){
            $accessToken = $_POST['access_token'];
        }
        if($accessToken == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        $session = FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->findById($sessionId);
        if($session == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $loginCookie = $session->loginCookie;

        if($loginCookie == $accessToken){
            return array('valid' => false, 'message' => 'current_session');
        }

        $userId = OW::getUser()->getId();
        if($session->userId != $userId){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $loginCookies = array();
        if(!isset($session->loginCookie)){
            return array('valid' => false, 'message' => 'authorization_error');
        }else{
            $loginCookies[] = $session->loginCookie;
        }
        if(!empty($loginCookies)){
            BOL_LoginCookieDao::getInstance()->deleteByCookies($loginCookies);
        }
        $result = $service->terminateDevice($session->id, $userId);
        if($result){
            return array('valid' => true, 'id' => (int) $session->id);
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function terminateAllSessions(){
        $securityPluginActive = FRMSecurityProvider::checkPluginActive('frmuserlogin', true);
        if(!$securityPluginActive){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $requestedId = null;
        if(isset($_POST['userId'])){
            $requestedId = $_POST['userId'];
        }

        $accessToken = $this->getAccessTokenFromPost();
        if($accessToken == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $currentSession = $this->getCurrentSessionUsingAccessToken($accessToken);
        if($currentSession == null || $currentSession->userId != OW::getUser()->getId()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if($requestedId == null || $requestedId != OW::getUser()->getId()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $this->deleteExpiredLoginCookies($requestedId, $currentSession->sessionId);

        $result = FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->deleteAllOtherDevices($requestedId, $currentSession->sessionId);
        if($result){
            return array('valid' => true);
        }else{
            return array('valid' => true, 'message' => 'there_is_no_session');
        }
    }

    public function deleteExpiredLoginCookies($requestedId, $currentSessionId){
        $allSessions = FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->getAllOtherDevices($requestedId, $currentSessionId);
        $loginCookies = array();
        foreach ($allSessions as $allSession){
            if(!empty($allSession->loginCookie)){
                $loginCookies[] = $allSession->loginCookie;
            }
        }
        if(!empty($loginCookies)){
            BOL_LoginCookieDao::getInstance()->deleteByCookies($loginCookies);
        }
    }

    public function getCurrentSessionUsingAccessToken($accessToken){
        return FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->getItemByLoginCookie($accessToken);
    }

    public function getAccessTokenFromPost(){
        $accessToken = null;
        if(isset($_POST['access_token'])){
            $accessToken = $_POST['access_token'];
        }

        return $accessToken;
    }

    public function getUserProfileSecurityData($user){
        if($user == null){
            return null;
        }
        $securityData = array();
        $securityData['view'] = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->canUserSeeFeed(OW::getUser()->getId(), $user->id);
        $securityData['view_friends'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'friends_view', 'friends');
        $securityData['view_videos'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'video_view_video', 'video');
        $securityData['view_events'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'event_view_attend_events', 'event');
        $securityData['view_albums'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'photo_view_album', 'photo');
        $securityData['view_groups'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'view_my_groups', 'groups');
        $securityData['view_blogs'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($user->id, 'blogs_view_blog_posts', 'blogs');
        $securityData['send_post'] = $securityData['view'] && FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->canUserSendPostOnFeed(OW::getUser()->getId(), $user->id);
        $securityData['send_post_privacy'] = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->getDefaultPrivacyOfUsersPosts($user);
        $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->isUserApproved($user->id);
        if ($user->id != OW::getUser()->getId()) {
            $securityData['friendship'] = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->getFriendshipInformation(OW::getUser()->getId(), $user->id);
        }
        if (!$adminApproveUser && $user->id != OW::getUser()->getId()) {
                $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($user->id);
                if ($hasAccessToApproveUser['valid']) {
                    $securityData['access_approve_user'] = true;
                }
            // notes
            if($user->id == OW::getUser()->getId() || $hasAccessToApproveUser['valid']) {
                $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($user->id);
                if (!empty($moderator_note)) {
                    $securityData['moderator_note'] = $moderator_note['admin_message'];
                }
            }
        }
        $securityData['add_group'] = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->canUserCreateGroup();
        $securityData['manage_group_rss'] = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->canManageRssGroup();
        $securityData['add_event'] = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance()->canUserCreateEvent();
        $securityData['add_photo'] = FRMMOBILESUPPORT_BOL_WebServicePhoto::getInstance()->canUserCreatePhoto();
        $securityData['add_video'] = FRMMOBILESUPPORT_BOL_WebServiceVideo::getInstance()->canUserCreateVideo();
        $securityData['add_blog'] = FRMMOBILESUPPORT_BOL_WebServiceBlogs::getInstance()->canUserCreateBlog();
        $securityData['add_news'] = FRMMOBILESUPPORT_BOL_WebServiceNews::getInstance()->canUserManageNews();
        return $securityData;
    }

    public function approveUser() {
        $userId = null;
        if (isset($_GET['userId'])) {
            $userId = UTIL_HtmlTag::stripTagsAndJs($_GET['userId']);
        }
        $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->isUserApproved($userId);
        if (!$adminApproveUser) {
            $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
            if ($hasAccessToApproveUser['valid']) {
                BOL_UserService::getInstance()->approve($userId);
                return array('valid' => true, 'userId' => $userId);
            }
        }
        return array('valid' => false, 'message' => 'invalid_data');
    }

    public function requestChangeFromUser(){
        $userId = UTIL_HtmlTag::stripTagsAndJs($_GET['userId']);
        $message = UTIL_HtmlTag::stripTagsAndJs($_POST['message']);

        $adminApproveUser = FRMMOBILESUPPORT_BOL_Service::getInstance()->isUserApproved($userId);
        if (!$adminApproveUser) {
            BOL_UserService::getInstance()->requestChangeFromUser($userId, $message);
        }

        return array('valid' => true, 'userId' => $userId);
    }

    public function getUserProfileInformation($userId){
        if(!FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->canUserSeeFeed(OW::getUser()->getId(), $userId)){
            return array();
        }
        $validQuestions = array();
        $questionsSectionsFetch = BOL_UserService::getInstance()->getUserViewQuestions($userId);
        $questionsFetch = array();
        $questionsData = array();
        foreach($questionsSectionsFetch['questions'] as $questionsSectionFetch){
            foreach($questionsSectionFetch as $questionSectionFetch){
                $questionsFetch[] = $questionSectionFetch;
            }
        }
        $securityPluginActive = FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true);
        if((OW::getUser()->isAuthenticated() && $userId == OW::getUser()->getId()) || !$securityPluginActive){
            $validQuestions = $questionsFetch;
        }else{
            $service = FRMSECURITYESSENTIALS_BOL_Service::getInstance();
            foreach ($questionsFetch as $question) {
                $privacy = $service->getQuestionPrivacy($userId, $question['id']);
                if ($privacy == null) {
                    $validQuestions[] = $question;
                } else if ($service->checkPrivacyOfObject($privacy, $userId, null, false)) {
                    $validQuestions[] = $question;
                }
            }
        }

        foreach ($validQuestions as $question){
            $label = OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label');
            $value = "";
            if(isset($questionsSectionsFetch['data'][$userId][$question['name']])){
                $value = $questionsSectionsFetch['data'][$userId][$question['name']];
                if(is_array($value)){
                    foreach ($value as $item){
                        $value = $item;
                    }
                }
            }
            $value = strip_tags($value);
            $questionsData[] = $this->prepareQuestion($question, $value, array());
        }

        return $questionsData;
    }

    public function getYearFromString($year) {
        $yearChangedEvent =  OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_DEFAULT_DATE_VALUE_SET, array('yearRange' => true, 'year' => $year)));
        if($yearChangedEvent->getData() && isset($yearChangedEvent->getData()['year']) && isset($yearChangedEvent->getData()['persian_year'])){
            $year = $yearChangedEvent->getData()['persian_year'];
        }
        return $year;
    }

    /***
     * @param $question
     * @param null $userValue
     * @param array $availableValues
     * @return array
     */
    public function prepareQuestion($question, $userValue = null, $availableValues = array()) {
        $label = OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label');
        if(isset($question['queryAccountType'])){
            if($question['queryAccountType'] == 'base_sign_up'){
                $account_type = "base_sign_up";
            }else{
                $account_type = $question['queryAccountType'];
            }
        }else{
            $account_type = "not_set";
        }
        $fromDateYear = null;
        $ToDateYear = null;
        if (isset($question['type']) && $question['type'] == 'datetime') {
            $fromDateYear = $this->getYearFromString(1930);
            $ToDateYear = $this->getYearFromString(2001);
        }
        if (isset($question['custom'])) {
            $customData = $question['custom'];
            $customData = json_decode($customData);
            if (isset($customData->year_range)) {
                if (isset($customData->year_range->from)) {
                    $fromDateYear = $this->getYearFromString($customData->year_range->from);
                }
                if (isset($customData->year_range->to)) {
                    $ToDateYear = $this->getYearFromString($customData->year_range->to);
                }
            }
        }
        $required = false;
        if (isset($question['required']) && $question['required'] == "1") {
            $required = true;
        }
        $resp = array(
            'name' => $question['name'],
            'type' => $question['type'],
            'label' => $label,
            'required' => $required,
            'presentation' => $question['presentation'],
            'values' => $availableValues,
            'user_value' => $userValue,
            'value' => $userValue,
            'fromDateYear' => $fromDateYear,
            'toDateYear' => $ToDateYear,
            'accountType' => $account_type
        );
        if(!empty($question['condition'])){
            $resp['condition'] = json_decode($question['condition'], true);
        }
        return $resp;
    }

    public function useOptionalFields() {
        // disable only required fields and get all fields (optional + required)
        if (isset($_POST['optional_fields']) && $_POST['optional_fields'] == 'true') {
            return true;
        }
        return false;
    }

    /***
     * @param $questionsFetch
     * @param array $questionsData
     * @return array
     */
    public function prepareQuestions($questionsFetch, $questionsData = array(), $keepAll = false) {
        $questions = array();
        $questionValues = array();
        $questionNames = array();
        $questionsUnique = array();
        foreach ($questionsFetch as $questionFetch){
            if($keepAll){
                $questionNames[] = $questionFetch['name'];
                $questionsUnique[] = $questionFetch;
            }
            else{
                if(!in_array($questionFetch['name'], $questionNames)) {
                    $questionNames[] = $questionFetch['name'];
                    $questionsUnique[] = $questionFetch;
                }
            }
        }
        $questionValuesFetch = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNames);
        foreach ($questionValuesFetch as $key => $questionValueFetch){
            $questionValue = array();
            $values = $questionValueFetch['values'];
            foreach ($values as $value){
                $questionOptionValue['value'] = $value->value;
                $questionOptionValue['label'] = BOL_QuestionService::getInstance()->getQuestionValueLang($key, $value->value);
                $questionValue[] = $questionOptionValue;
            }
            $questionValues[$key] = $questionValue;
        }
        foreach ($questionsUnique as $questionUnique){
            if($questionUnique['required'] == 1 || $this->useOptionalFields()){
                $values = array();
                if(isset($questionValues[$questionUnique['name']])){
                    $values = $questionValues[$questionUnique['name']];
                } else if ($questionUnique['type'] === 'boolean') {
                    $values[] = array(
                        'label' => OW::getLanguage()->text('admin', 'permissions_index_yes'),
                        'value' => '1'
                    );
                }

                $user_value = null;
                if (!empty($questionsData) && isset($questionsData[$questionUnique['name']])) {
                    $user_value = $questionsData[$questionUnique['name']];
                }
                $questions[] = $this->prepareQuestion($questionUnique, $user_value, $values);
            }
        }
        // multi-label for account type
        $questionNamesAccountType = array();
        $questionsUnique = array();
        $questionNames = array();
        if($keepAll){
            foreach ($questions as $question){
                if(!in_array($question['name'], $questionNames)) {
                    $questionNamesAccountType[$question['name']] = array($question['accountType']);
                    $questionNames[] = $question['name'];
                    $questionsUnique[] = $question;
                }else{
                    array_push($questionNamesAccountType[$question['name']], $question['accountType']);
                }
            }
            foreach ($questionsUnique as &$questionUnique){
                $questionUnique['accountType'] = $questionNamesAccountType[$questionUnique['name']];
            }
            $questions = $questionsUnique;
        }
        return $questions;
    }

    public function getJoinFields($getUserPhotoQuestion = true){
        $questionsFetch = BOL_QuestionService::getInstance()->findSignUpQuestionsForAllAccountTypes();

        $questions = $this->prepareQuestions($questionsFetch, array(), true);

        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');

        if ($getUserPhotoQuestion) {
            if ($displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD || $this->useOptionalFields()) {
                $userPhotoQuestion = array(
                    'name' => 'user_photo',
                    'type' => 'file',
                    'presentation' => 'image',
                    'required' => true,
                );
                $userPhotoQuestion = $this->prepareQuestion($userPhotoQuestion, null, array());
                $userPhotoQuestion['accountType'] = array("not_set");
                $questions[] = $userPhotoQuestion;
            }
        }

        $event = new OW_Event('join.get_captcha_field');
        OW::getEventManager()->trigger($event);
        $captchaField = $event->getData();

        $enableCaptcha = OW::getConfig()->getValue('base', 'enable_captcha');

        if ( $enableCaptcha && !empty($captchaField) && $captchaField instanceof FormElement ){
            $captcha = array(
                'name' => $captchaField->getAttribute('name'),
                'type' => 'captchaTextField',
                'label' => $captchaField->getAttribute('placeholder'),
                'required' => true,
                'presentation' => $captchaField->getAttribute('name'),
                'values' => [],
                'user_value' => null,
                'captchaImageSource' => OW_URL_HOME . 'captcha.php',
                'fromDateYear' => null,
                'toDateYear' => null,
                'accountType' => array("not_set"),
            );

            $questions[] = $captcha;
        }

        return $questions;
    }


    public function getSearchableFieldsForAllAccountTypes(){
        $questionsFetch = BOL_QuestionService::getInstance()->findAllSearchQuestionsForAccountType();

        $questions = $this->prepareQuestions($questionsFetch, array(), true);

        return $questions;
    }


    public function getAccountLabelTypes(){
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
        $labels = array();
        foreach ($accountTypes as $accountType){
            $labels[$accountType->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $accountType->name);
        }
        return $labels;
    }

    public function getDefaultAccountLabels(){
        $labels = array();
        $labels[] = 'base_sign_up';
        $labels[] = 'not_set';
        return $labels;
    }

    public function getEditProfileForm($userId, $returnForm = true) {
        $userDto = BOL_UserService::getInstance()->findUserById($userId);
        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($userDto->accountType);
        if(empty($accountType)){
            $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType();
        }

        $questions = BOL_QuestionService::getInstance()->findEditQuestionsForAccountType($accountType->name);

        $onBeforeProfileEditFormBuildEventResults = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PROFILE_EDIT_FORM_BUILD, array('questions' => $questions)));
        if(isset($onBeforeProfileEditFormBuildEventResults->getData()['questions'])){
            $questions = $onBeforeProfileEditFormBuildEventResults->getData()['questions'];
        }

        $editForm = new EditQuestionForm('editForm', $userId);
        $editForm->setId('editForm');

        $questionArray = array();
        $section = null;
        $questionNameList = array();

        $questionsUnique = array();
        $questionsKeyUnique = array();

        foreach ($questions as $sort => $question) {
            if ($section !== $question['sectionName']) {
                $section = $question['sectionName'];
            }
            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
            if (!in_array($questions[$sort]['name'], $questionsKeyUnique)) {
                $questionsUnique[] = $questions[$sort];
                $questionsKeyUnique[] = $questions[$sort]['name'];
            }
        }
        $questionData = BOL_QuestionService::getInstance()->getQuestionData(array($userId), $questionNameList);
        $questionValues = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);
        if($returnForm) {
            $editForm->addQuestions($questionsUnique, $questionValues, !empty($questionData[$userId]) ? $questionData[$userId] : array());
            return array('form' => $editForm, 'questionsData' => $questionData, 'questionValues' => $questionValues, 'questionArray' => $questionArray, 'questions' => $questions);
        }
        return array('questionsData' => $questionData, 'questionValues' => $questionValues, 'questionArray' => $questionArray, 'questions' => $questions);
    }

    public function getEditProfileFields(){
        if(!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authenticated_before');
        }
        $userId = OW::getUser()->getId();

        $editFormData = $this->getEditProfileForm($userId, false);
        $questionsFetch = $editFormData['questions'];
        $questionsData = $editFormData['questionsData'];
        if (isset($questionsData[$userId])) {
            $questionsData = $questionsData[$userId];
        } else {
            $questionsData = array();
        }
        return $this->prepareQuestions($questionsFetch, $questionsData);
    }

    public function changeToGregDate($day, $month, $year, $format="") {
        $changeToGregorianDateEventParams['faYear'] = $year;
        $changeToGregorianDateEventParams['faMonth'] = $month;
        $changeToGregorianDateEventParams['faDay'] = $day;
        $changeToGregorianDateEventParams['changeNewsJalaliToGregorian'] = true;
        $changeToGregorianDateEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::CHANGE_DATE_FORMAT_TO_GREGORIAN, $changeToGregorianDateEventParams));
        if($changeToGregorianDateEvent->getData()!=null && sizeof($changeToGregorianDateEvent->getData())>0) {
            $newDateData = $changeToGregorianDateEvent->getData();
            if (isset($newDateData['gregorianYearNews']) && isset($newDateData['gregorianMonthNews']) && isset($newDateData['gregorianDayNews'])) {
                if (!empty($format)) {
                    return date($format, mktime(0, 0, 0, $newDateData['gregorianMonthNews'], $newDateData['gregorianDayNews'], $newDateData['gregorianYearNews']));
                } else {
                    return $newDateData['gregorianYearNews'] . '/' . $newDateData['gregorianMonthNews'] . '/' . $newDateData['gregorianDayNews'];
                }
            }
        }
        return null;
    }


    private function fixBirthdate($birthdate){
        $date = UTIL_DateTime::parseDate($birthdate, UTIL_DateTime::DEFAULT_DATE_FORMAT);
        if ( $date === null ){
            return false;
        }
        $changeToGreg = $this->changeToGregDate($date[UTIL_DateTime::PARSE_DATE_DAY],
            $date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_YEAR]);

        if ($changeToGreg != null) {
            $realDate = explode('/', $changeToGreg);
            if ( !UTIL_Validator::isDateValid($realDate[1], $realDate[2], $realDate[0]) ){
                return false;
            }
        } else {
            if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH],
                $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) ){
                return false;
            }
        }

        $changeToGreg = $this->changeToGregDate($date[UTIL_DateTime::PARSE_DATE_DAY],
            $date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_YEAR]);
        if (empty($changeToGreg)) {
            return false;
        }

        $date = new DateTime($changeToGreg);
        $date = $date->getTimestamp();
        if (time() - $date < 365 * 24 * 60 * 60) {
            return false;
        }

        return $changeToGreg;
    }

    /***
     * Join user process
     * @return array
     */
    public function joinAction(){
        if(OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authenticated_before');
        }

        if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            $code = 'invalid_text_code';
            if ( isset($_GET['code']) )
            {
                $code = strip_tags($_GET['code']);
            }

            try
            {
                $event = new OW_Event(OW_EventManager::ON_JOIN_FORM_RENDER, array('code' => $code));
                OW::getEventManager()->trigger($event);
                return array('valid' => false, 'message' => 'only_invitation_join');
            }
            catch ( JoinRenderException $ex )
            {
                //ignore;
            }
        }

        if(empty($_POST['email']) || !UTIL_Validator::isEmailValid($_POST['email'])){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'email');
        }

        $minLength = 8;
        if(FRMSecurityProvider::checkPluginActive('frmpasswordstrengthmeter', true)){
            $configs =  OW::getConfig()->getValues('frmpasswordstrengthmeter');
            $minLength = $configs['minimumCharacter'];
        }
        if(empty($_POST['password']) || (strlen($_POST['password']) < $minLength)){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'password');
        }

        if(empty($_POST['username']) || !UTIL_Validator::isUserNameValid($_POST['username'])){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'username');
        }

        $enableCaptcha = OW::getConfig()->getValue('base', 'enable_captcha');

        if($enableCaptcha &&
            (empty($_POST['captchaField']) || !UTIL_Validator::isCaptchaValid($_POST['captchaField']))){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'captchaField', 'captchaImageAddress' => OW_URL_HOME . 'captcha.php');
        }

        $_POST['username'] = UTIL_HtmlTag::convertPersianNumbers($_POST['username']);
        $_POST['email'] = UTIL_HtmlTag::convertPersianNumbers($_POST['email']);

        if (isset($_POST['birthdate'])) {
            $_POST['birthdate'] = $this->fixBirthdate($_POST['birthdate']);
            if(empty($_POST['birthdate'])) {
                return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
            }
        }

        $accountType = BOL_QuestionAccountTypeDao::getInstance()->getDefaultAccountType()->name;
        if(isset($_POST['accountType'])) {
            $accountType2 = BOL_QuestionAccountTypeDao::getInstance()->findAccountTypeByNameList(array($_POST['accountType']));
            if (!empty($accountType2)) {
                $accountType = $accountType2->name;
            }
        }
        $_POST['accountType'] = $accountType;

        $questions = $this->getJoinFields(false);
        $formValidator = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()
            ->checkDataFormValid($questions, false, $accountType);

        if(!$formValidator['valid']) {
            return array('valid' => false, 'message' => 'error_input_Data', 'errors' => $formValidator['errors']);
        }
        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');
        if ($displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD && !isset($_FILES['user_photo'])) {
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'user_photo');
        }
        $result = $this->createUser($questions);
        if($result['user'] == null){
            return array('valid' => false, 'message' => 'error_input_Data');
        }
        $user = $result['user'];
        $userData = array(
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
        );

        $this->changeAvatar(false, $user->id, 'user_photo');
        return array(
            'valid' => true,
            'message' => 'user_created',
            'user' => $userData
        );
    }

    /***
     * @return array
     * @throws Redirect404Exception
     */
    public function editProfile(){
        if(!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authenticated_need');
        }
        $userId = OW::getUser()->getId();
        $result = array('valid' => false, 'message' => 'invalid_data');

        if(isset($_POST['email']) && !UTIL_Validator::isEmailValid($_POST['email'])){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'email');
        }

        if(isset($_POST['username']) && !UTIL_Validator::isUserNameValid($_POST['username'])){
            return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'username');
        }

        $_POST['username'] = UTIL_HtmlTag::convertPersianNumbers($_POST['username']);
        $_POST['email'] = UTIL_HtmlTag::convertPersianNumbers($_POST['email']);

        if(isset($_POST['birthdate']) && !UTIL_Validator::isUserNameValid($_POST['birthdate'])){
            $date = UTIL_DateTime::parseDate($_POST['birthdate'], UTIL_DateTime::DEFAULT_DATE_FORMAT);
            if ( $date === null ){
                return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
            }

            $changeToGreg = $this->changeToGregDate($date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_YEAR]);
            if ($changeToGreg != null) {
                $realDate = explode('/', $changeToGreg);
                if ( !UTIL_Validator::isDateValid($realDate[1], $realDate[2], $realDate[0]) ){
                    return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
                }
            } else {
                if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) ){
                    return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
                }
            }

            $date = new DateTime($_POST['birthdate']);
            $date = $date->getTimestamp();
            if (time() - $date < 365 * 24 * 60 * 60) {
                return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
            }
        }
        $editFormData = $this->getEditProfileForm($userId);
        $questionArray = $editFormData['questionArray'];
        $editForm = $editFormData['form'];
        if ( $editForm->getElement('csrf_token') != null){
            $editForm->deleteElement('csrf_token');
        }
        if ( !isset($_POST['parentEmail'])){
            $_REQUEST['parentEmail'] = '';
        }

        foreach ( $questionArray as $section ) {
            foreach ($section as $key => $question) {
                if (isset($question['presentation']) && isset($_POST[$question['name']]) && $question['presentation'] == 'multicheckbox') {
                    $_POST[$question['name']] = array_filter(explode(',', $_POST[$question['name']]));
                }
            }
        }

        if ( $editForm->isValid($_POST) ) {
            $data = $editForm->getValues();
            $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $userId, 'method' => 'native', 'params' => $data,'forEditProfile'=>true));
            OW::getEventManager()->trigger($event);
            foreach ( $questionArray as $section )
            {
                foreach ( $section as $key => $question )
                {
                    switch ( $question['presentation'] )
                    {
                        case 'multicheckbox':

                            if ( is_array($data[$question['name']]) )
                            {
                                $answer = array();
                                foreach ($data[$question['name']] as $key => $value )
                                {
                                    $answer[] = (int)$value;
                                }
                                $data[$question['name']] = json_encode($answer);
                            }
                            else
                            {
                                $data[$question['name']] = 0;
                            }

                            break;
                    }
                }
            }
            $changesList = BOL_QuestionService::getInstance()->getChangedQuestionList($data, $userId);
            if ( BOL_QuestionService::getInstance()->saveQuestionsData($data, $userId) )
            {
                $isNeedToModerate = BOL_QuestionService::getInstance()->isNeedToModerate($changesList);
                $event = new OW_Event(OW_EventManager::ON_USER_EDIT, array('userId' => $userId, 'method' => 'native', 'moderate' => $isNeedToModerate));
                OW::getEventManager()->trigger($event);

                if ( BOL_UserService::getInstance()->isApproved($userId) )
                {
                    $changesList = array();
                }

                BOL_PreferenceService::getInstance()->savePreferenceValue('base_questions_changes_list', json_encode($changesList), $userId);
                $unverifiedMobileNumber = '';
                if (FRMSecurityProvider::checkPluginActive('frmsms', true)) {
                    $unverifiedMobileNumber = OW::getSession()->get(FRMSMS_BOL_Service::UNVERIFIED_MOBILE_NUMBER);
                    if ($unverifiedMobileNumber == null) {
                        $unverifiedMobileNumber = '';
                    }
                }
                $user=$this->getUserInformationById($userId);
                $user['postedQuestionData'] = $this->postedQuestionsData($_POST, UTIL_DateTime::DATETIME_SEPRATED_BY_DASH_DATE_FORMAT);
                $result = array('valid' => true, 'message' => 'edited', 'unverifiedMobileNumber' => $unverifiedMobileNumber, 'user' => $user);
            }
            if (isset($_POST['user_status'])) {
                $userStatus = UTIL_HtmlTag::stripTagsAndJs($_POST['user_status']);
                $userStatus = trim($userStatus);
                if (!empty($userStatus)) {
                    BOL_ComponentEntityService::getInstance()->saveComponentSettingList('profile-BASE_CMP_AboutMeWidget', $userId, array('content' => $userStatus));
                    BOL_ComponentEntityService::getInstance()->clearEntityCache(BOL_ComponentEntityService::PLACE_PROFILE, $userId);
                }
            }
        } else {
            $result = array('valid' => false, 'message' => 'invalid_data', 'errors' => $editForm->getErrors());
        }

        return $result;
    }

    private function postedQuestionsData($data, $format="")
    {
        if(isset($data['birthdate']) && !UTIL_Validator::isUserNameValid($data['birthdate'])) {
            $date = UTIL_DateTime::parseDate($data['birthdate'], UTIL_DateTime::DEFAULT_DATE_FORMAT);
            $changeToGreg = $this->changeToGregDate(
                $date[UTIL_DateTime::PARSE_DATE_DAY],
                $date[UTIL_DateTime::PARSE_DATE_MONTH],
                $date[UTIL_DateTime::PARSE_DATE_YEAR],
                $format
            );
            $data['birthdate']=$changeToGreg;
        }
        return $data;
    }

    
    public function fillAccountType(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authentication_need');
        }
        $user = OW::getUser()->getUserObject();
        if (!$user) {
            return array('valid' => false, 'message' => 'authentication_need');
        }

        if(empty($_POST['accountType']))
        {
            return array('valid' => false, 'message' => 'required_accountType');
        }
        $userAccountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

        if ( OW::getRequest()->isPost() ) {
            if (empty($userAccountType) && !empty($_POST['accountType'])) {
                $postAccountType = BOL_QuestionService::getInstance()->findAccountTypeByName($_POST['accountType']);
                $accountTypeDto = !empty($postAccountType) ? $postAccountType : BOL_QuestionService::getInstance()->getDefaultAccountType();
                if ($accountTypeDto) {
                    $user->accountType = $accountTypeDto->name;
                    BOL_UserService::getInstance()->saveOrUpdate($user);
                }
                return array('valid' => true, 'message' => 'account_type_updated', 'account_type'=>$accountTypeDto);
            }
        }
    }


    /***
     * @return array
     * @throws Redirect404Exception
     */
    public function fillProfileQuestion(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authentication_need');
        }
        $result = array('valid' => false, 'message' => 'invalid_data');

        if(isset($_POST['email']) || isset($_POST['password']) || isset($_POST['username'])){
            return $result;
        }

        if(isset($_POST['birthdate']) && !UTIL_Validator::isUserNameValid($_POST['birthdate'])){
            $date = UTIL_DateTime::parseDate($_POST['birthdate'], UTIL_DateTime::DEFAULT_DATE_FORMAT);
            if ( $date === null ){
                return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
            }

            $changeToGreg = $this->changeToGregDate($date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_YEAR]);
            if ($changeToGreg != null) {
                $realDate = explode('/', $changeToGreg);
                if ( !UTIL_Validator::isDateValid($realDate[1], $realDate[2], $realDate[0]) ){
                    return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
                }
            } else {
                if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) ){
                    return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
                }
            }


            $date = new DateTime($_POST['birthdate']);
            $date = $date->getTimestamp();
            if (time() - $date < 365 * 24 * 60 * 60) {
                return array('valid' => false, 'message' => 'error_input_Data', 'field' => 'birthdate');
            }
        }

        $user = OW::getUser()->getUserObject();
        if (!$user) {
            return array('valid' => false, 'message' => 'authentication_need');
        }

        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

        if ( empty($accountType) )
        {
            return array('valid' => false, 'message' => 'authentication_need');
        }

        try{
            $event = new OW_Event( OW_EventManager::ON_BEFORE_USER_COMPLETE_PROFILE, array( 'user' => $user ) );
            OW::getEventManager()->trigger($event);
        } catch (Exception $e){
            return array('valid' => false, 'message' => 'input_data');
        }

        $form = new EditQuestionForm('requiredQuestionsForm', $user->id);
        $questions = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList($user->id);

        if (empty($questions) && isset($_POST['field_mobile']) && OW::getUser()->isAuthenticated()) {
            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());
            if (!empty($user) ) {
                $questionsList = BOL_QuestionService::getInstance()->findRequiredQuestionsForAccountType($user->accountType);
                if (!empty($questionsList)) {
                    foreach ($questionsList as $question) {
                        if (isset($question['name']) && $question['name'] === 'field_mobile') {
                            $questions[$question['name']] = $question;
                        }
                    }
                }
            }
        }

        if ( empty($questions) )
        {
            return array('valid' => false, 'message' => 'empty_questions');
        }

        $section = null;
        $questionArray = array();
        $questionNameList = array();

        foreach ( $questions as $sort => $question )
        {
            if ( $section !== $question['sectionName'] )
            {
                $section = $question['sectionName'];
            }

            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
            if ($questions[$sort]['presentation'] == 'multicheckbox' && isset($_POST[$questions[$sort]['name']])) {
                $_POST[$questions[$sort]['name']] = array_filter(explode(',', $_POST[$questions[$sort]['name']]));
            }
        }

        $questionValues = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);

        $form->addQuestions($questions, $questionValues, array());
        if ( $form->getElement('csrf_token') != null){
            $form->deleteElement('csrf_token');
        }
        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                if ( BOL_QuestionService::getInstance()->saveQuestionsData($form->getValues(), $user->getId()) ){
                    $event = new OW_Event(OW_EventManager::ON_AFTER_USER_COMPLETE_PROFILE, array( 'userId' => $user->getId() ));
                    OW::getEventManager()->trigger($event);

                    $unverifiedMobileNumber = '';
                    if (FRMSecurityProvider::checkPluginActive('frmsms', true)) {
                        $unverifiedMobileNumber = OW::getSession()->get(FRMSMS_BOL_Service::UNVERIFIED_MOBILE_NUMBER);
                        if ($unverifiedMobileNumber == null) {
                            $unverifiedMobileNumber = '';
                        }
                    }
                    return array('valid' => true, 'unverifiedMobileNumber' => $unverifiedMobileNumber,'message' => 'edited', 'user' => $this->getUserInformationById($user->getId()));
                }
            } else {
                return array('valid' => false, 'message' => 'input_error', 'form_error' => $form->getErrors());
            }
        }

        return array('valid' => false, 'message' => 'input_error');
    }

    public function checkVerificationCode() {
        if(!FRMSecurityProvider::checkPluginActive('frmsms',true)){
            return array("valid" => false, 'message' => 'input_error');
        }

        $isUserLogin = OW::getUser()->isAuthenticated();

        if(!$isUserLogin && !isset($_POST['mobileNumber'])) {
            return array("valid" => false, 'message' => 'input_error');
        }

        if (!isset($_POST['code'])) {
            return array("valid" => false, 'message' => 'input_error');
        }

        if (!$isUserLogin || isset($_POST['mobileNumber'])) {
            $mobileNumber = trim($_POST['mobileNumber']);
        } else {
            $mobileNumber = FRMSMS_BOL_Service::getInstance()->getUserQuestionsMobile(OW::getUser()->getId());
        }
        $code = trim($_POST['code']);
        $limit = false;

        $eventPhoneCheck = new OW_Event('frmsms.phone_number_check', array('number' => $mobileNumber));
        OW_EventManager::getInstance()->trigger($eventPhoneCheck);
        $eventPhoneCheckData = $eventPhoneCheck->getData();

        $validCode = false;
        if (!isset($eventPhoneCheckData)) {
            return array("valid" => false, 'message' => 'not_valid');
        } else if (isset($eventPhoneCheckData['userPhone_notIn_ValidList'])) {
            return array("valid" => false, 'message' => 'mobile_number_not_valid');
        } else {
            $verifyCodeEvent = new OW_Event('frmsms.verify_code_event', array('mobileNumber' => $mobileNumber, 'code' => $code));
            OW_EventManager::getInstance()->trigger($verifyCodeEvent);
            $verifyCodeEventData = $verifyCodeEvent->getData();
            if (isset($verifyCodeEventData['valid']) && $verifyCodeEventData['valid']) {
                $validCode = true;
            }
            if (isset($verifyCodeEventData['limit']) && $verifyCodeEventData['limit']) {
                $limit = true;
            }
        }

        if ($validCode) {
            $userId = null;
            if (!isset($eventPhoneCheckData['user_id'])) {
                if (!$isUserLogin) {
                    // new user
                    $verifyCodeEventNonUser = new OW_Event('frmsms.verify_code_event_non_user', array('mobileNumber' => $mobileNumber));
                    OW_EventManager::getInstance()->trigger($verifyCodeEventNonUser);
                    $verifyCodeEventNonUserData = $verifyCodeEventNonUser->getData();
                    if (isset($verifyCodeEventNonUserData['user_id'])) {
                        $userId = $verifyCodeEventNonUserData['user_id'];
                    }

                    if ($userId != null) {
                        OW_User::getInstance()->login($userId);

                        $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $userId, 'method' => 'service'));
                        OW::getEventManager()->trigger($event);
                        $usersImportEvent = OW::getEventManager()->trigger(new OW_Event('on.users.import.register', ['mobile' => $mobileNumber]));
                        $adminVerified = isset($usersImportEvent->getData()['verified']) ? (boolean)$usersImportEvent->getData()['verified'] : false;
                        if ($adminVerified && !BOL_UserService::getInstance()->isApproved($userId)) {
                            BOL_UserService::getInstance()->approve($userId);
                        }
                    }
                } else {
                    $userId = OW::getUser()->getId();
                    OW_User::getInstance()->login($userId);
                }
            } else {
                $userId = $eventPhoneCheckData['user_id'];
                OW_User::getInstance()->login($userId);
            }

            if ($userId != null) {
                FRMSMS_BOL_Service::getInstance()->validateMobileToken($userId, $mobileNumber);
                if ($isUserLogin) {
                    return array("valid" => true, 'message' => 'code_is_valid', 'limit' => false, 'admin_check' => true, 'login_status' => 'before');
                } else {
                    return $this->processLoginUser($userId);
                }
            }
        }

        OW::getEventManager()->trigger(new OW_Event('base.bot_detected', array('isBot' => false)));
        return array("valid" => $validCode, 'message' => 'code_not_valid', 'limit' => $limit);
    }

    public function inviteUser() {
        if(!FRMSecurityProvider::checkPluginActive('frminvite', true)){
            return array("valid" => false, 'message' => 'plugin_not_found');
        }

        $emails = null;
        $smss = null;
        if(isset($_POST['email'])){
            $emails = $_POST['email'];
        }
        if(FRMSecurityProvider::checkPluginActive('frmsms', true) && isset($_POST['number'])) {
            $smss = $_POST['number'];
        }

        $language = OW::getLanguage();
        $emptyError = $language->text('admin', 'invite_members_min_limit_message');

        if (!isset($emails) && !isset($smss)) {
            return array('valid' => false, 'message' => $emptyError);
        }

        $result = FRMINVITE_BOL_Service::getInstance()->sendInvitation($emails, $smss);

        if (isset($result['valid'])) {
            if (!$result['valid']) {
                if (isset($result['email'])) {
                    $error = $language->text('frminvite', 'wrong_email_format_error', array('email' => trim($result['email'])));
                    return array('valid' => false, 'error' => array('email' => $error));
                }
                if (isset($result['number'])) {
                    $error = $language->text('frminvite', 'wrong_mobile_format_error', array('phone' => trim($result['number'])));
                    return array('valid' => false, 'error' => array('number' => $error));
                }
                if (isset($result['limit'])) {
                    return array('valid' => false, 'message' => $result['limit']);
                }
            } else if(isset($result['registered_users']) && isset($result['invalidNumbers']) && isset($result['sentInvitationsNumber'])) {
                return array('valid' => true);
            }
        }

        return array('valid' => false, 'message' => $emptyError);
    }

    public function createUser($questions = array())
    {
        $username = null;
        $email = null;
        $password = null;
        $verifyMobile = false;

        if(isset($_POST['email'])){
            $email = $_POST['email'];
        }

        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }

        if(isset($_POST['username']) && !empty($_POST['username'])){
            $username = $_POST['username'];
        }else if(isset($_POST['field_mobile'])){
            $username = $_POST['field_mobile'];
            $verifyMobile = true;
        }

        if(empty($username) || empty($email) || empty($password)){
            return array('valid' => false, 'message' => 'error_input_Data', 'user' => null);
        }

        $user = BOL_UserService::getInstance()->findByUsername($username);
        if ($user == null) {
            $user = BOL_UserService::getInstance()->findByEmail($email);
            if ($user == null) {
                if (count(BOL_QuestionService::getInstance()->findAllAccountTypes())==1) {
                    $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
                }else{
                    if(isset($_POST['accountType']))
                    {
                        $accountType = $_POST['accountType'];
                    }
                }
                if(!isset($accountType)){
                    return array('valid' => false, 'message' => 'no_account_type', 'user' => null);
                }
                $user = BOL_UserService::getInstance()->createUser($username, $password, $email, $accountType, true);

                $this->saveUserQuestionData($questions, $user->getId());
                $savedUserQuestionDataOnce = true;

                OW_User::getInstance()->login($user->getId());
                $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $user->getId(), 'method' => 'service'));
                OW::getEventManager()->trigger($event);
                if(FRMSecurityProvider::checkPluginActive('frmsms', true) && $verifyMobile){
                    FRMSMS_BOL_Service::getInstance()->renewUserToken($user->getId(), $_POST['field_mobile']);
                    OW_User::getInstance()->login($user->getId());
                }
            }else{
                return array('valid' => false, 'message' => 'email_exists', 'user' => null);
            }
        }else{
            return array('valid' => false, 'message' => 'username_exists', 'user' => null);
        }

        if (!$savedUserQuestionDataOnce) {
            $this->saveUserQuestionData($questions, $user->getId());
        }

        return array('valid' => true, 'message' => 'create_user', 'user' => $user);
    }

    public function saveUserQuestionData($questions, $userId) {
        $questionService = BOL_QuestionService::getInstance();
        $data = array();
        foreach ($questions as $question){
            if(isset($question['name']) && !in_array($question['name'], array('password')) && isset($_POST[$question['name']])){
                if (isset($question['presentation']) && $question['presentation'] == 'multicheckbox' && isset($_POST[$question['name']])) {
                    $_POST[$question['name']] = array_filter(explode(',', $_POST[$question['name']]));
                }
                $data[$question['name']] = $_POST[$question['name']];
            }
        }

        $questionService->saveQuestionsData($data, $userId);
    }

    public function processForgotPassword()
    {
        if(OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $emailOrPhone = null;
        if(isset($_GET['email'])){
            $emailOrPhone = trim($_GET['email']);
        }
        if(!isset($emailOrPhone)){
            return array('valid' => false, 'email_exist' => false, 'message' => 'input_error');
        }
        return $this->processSendForgotPassword($emailOrPhone);
    }


    public function processSendForgotPassword($emailOrPhone)
    {
        try{
            $event = new OW_Event('base.forgot_password.form_process', array('data' => array('email' => $emailOrPhone)));
            OW_EventManager::getInstance()->trigger($event);
            $result = $event->getData();
            if(!isset($result) || !isset($result['processed']) || !$result['processed']) {
                if(!UTIL_Validator::isEmailValid($emailOrPhone)) {
                    return array('valid' => false, 'email_exist' => false);
                }
                $userService=BOL_UserService::getInstance();
                $user = $userService->findByEmail($emailOrPhone);

                if ( $user === null )
                {
                    return array('valid' => false, 'user_exists' => false);
                }
                $userService->processResetForm(array('email' => $emailOrPhone));
            }
            return array('valid' => true);
        } catch (LogicException $e){
            return array('valid' => false, 'remaining_block_time' => 10);
        }
    }

    public function verifyResetPasswordCode()
    {
        $emailOrPhone = null;
        if(isset($_POST['email'])){
            $emailOrPhone = trim($_POST['email']);
        }
        if(!isset($emailOrPhone) && !UTIL_Validator::isEmailValid($emailOrPhone)){
            return array('valid' => false, 'email_exist' => false, 'message' => 'input_error');
        }

        if (!isset($_POST['code'])) {
            return array("valid" => false, 'message' => 'input_error');
        }

        if(!isset($_POST['password'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        $password = UTIL_HtmlTag::stripJs($_POST['password']);
        if (FRMSecurityProvider::checkPluginActive('frmpasswordstrengthmeter', true)) {
            $passwordValidator = FRMPASSWORDSTRENGTHMETER_BOL_Service::getInstance()->checkPasswordValid($password);
            if (!$passwordValidator['valid']) {
                return array('valid' => false, 'error_data' => $passwordValidator['error']);
            }
        }

        $resetCode = BOL_UserService::getInstance()->findResetPasswordByCode($_POST['code']);

        if ( $resetCode != null )
        {
            BOL_UserService::getInstance()->updatePassword( $resetCode->getUserId(), $password );
            return array('valid' => true, 'message' => 'code_is_valid');
        }

        OW::getEventManager()->trigger(new OW_Event('base.bot_detected', array('isBot' => false)));
        return array("valid" => false, 'message' => 'code_is_not_valid');
    }

    public function getChatAndGroups(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('frmmainpage', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view')) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        $checkPrivacy = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($userId, 'view_my_groups', 'groups');
        if(!$checkPrivacy){
            return array();
        }

        $data = array();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $search = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->prepareGetSearchValue();

        $dataList = FRMMAINPAGE_BOL_Service::getInstance()->findUserChatsAndGroups($userId, $first, $count, $search);
        $dataList = FRMMAINPAGE_BOL_Service::getInstance()->prepareChatGroupData($dataList, array(), $search, false);

        if (isset($dataList['tplList'])) {
            $dataList = $dataList['tplList'];
        }

        $groupIds = array();
        $conversationIds = array();

        foreach ($dataList as $item) {
            $dataItem = null;
            if (isset($item['groupId'])) {
                $groupIds[] = $item['groupId'];
            }else if(isset($item['type']) && $item['type'] == 'group') {
                $groupIds[] = $item['id'];
            } else if (isset($item['conversationId'])) {
                $conversationIds[] = $item['conversationId'];
            }
        }

        $groupsList = GROUPS_BOL_Service::getInstance()->findGroupListByIds($groupIds);
        $groups = array();
        $conversations = array();
        foreach ($groupsList as $groupsItem) {
            $groups[$groupsItem->id] = $groupsItem;
        }

        $conversationsList = MAILBOX_BOL_ConversationService::getInstance()->getConversationsItem($conversationIds);
        foreach ($conversationsList as $conversationsItem) {
            $conversations[$conversationsItem['conversationId']] = $conversationsItem;
        }

        $groupsAdditionalInfo = array(
            'checkCanView' => false,
            'checkUserExistInGroup' => false,
        );
        $groupsInfo = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->getGroupsInformation($groups, 0, 10, array(), $groupsAdditionalInfo);

        foreach ($dataList as $item) {
            $dataItem = null;
            if (isset($item['groupId'])) {
                if (isset($groups[$item['groupId']])) {
                    $dataItem = array();
                    $text = $item['content'];
                    foreach ($groupsInfo as $groupInfo) {
                        if ($groupInfo['id'] == $item['groupId']) {
                            $dataItem = $groupInfo;
                        }
                    }
                    $dataItem['type'] = 'group';
                    $dataItem['lastActivityString'] = $text;

                }
            }else if (isset($item['type']) && $item['type'] == 'group') {
                if (isset($groups[$item['id']])) {
                    $dataItem = array();
                    foreach ($groupsInfo as $groupInfo) {
                        if ($groupInfo['id'] == $item['id']) {
                            $dataItem = $groupInfo;
                        }
                    }
                    $dataItem['type'] = 'group';
                }
            } else if (isset($item['conversationId'])) {
                if (isset($conversations[$item['conversationId']])) {
                    $conv = $conversations[$item['conversationId']];
                    $text = $item['text'];
                    $dataItem = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->preparedConversation($conv, $count, false);
                    $dataItem['type'] = 'chat';
                    $dataItem['conversation_info']['preview_text'] = $text;

                }
            }
            if ($dataItem != null) {
                $data[] = $dataItem;
            }
        }

        return $data;
    }

    public function getUserChats(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('mailbox', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        $data = array();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $search = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->prepareGetSearchValue();

        $dataList = FRMMAINPAGE_BOL_Service::getInstance()->findUserChats($userId, $first, $count, $search);
        $dataList = FRMMAINPAGE_BOL_Service::getInstance()->prepareChatGroupData($dataList, array(), $search, false);

        if (isset($dataList['tplList'])) {
            $dataList = $dataList['tplList'];
        }
        $conversationIds = array();

        foreach ($dataList as $item) {
            $dataItem = null;
             if (isset($item['conversationId'])) {
                 $conversationIds[] = $item['conversationId'];
             }
        }
        $conversations = array();
        $conversationsList = MAILBOX_BOL_ConversationService::getInstance()->getConversationsItem($conversationIds);
        foreach ($conversationsList as $conversationsItem) {
            $conversations[$conversationsItem['conversationId']] = $conversationsItem;
        }
        foreach ($dataList as $item) {
            $dataItem = null;
             if (isset($item['conversationId'])) {
                if (isset($conversations[$item['conversationId']])) {
                    $conv = $conversations[$item['conversationId']];
                    $text = $item['text'];;
                    if($text == OW::getLanguage()->text('mailbox', 'attachment')){
                        $text .= ' 📎';
                    }
                    $dataItem = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->preparedConversation($conv, $count, false);
                    $dataItem['type'] = 'chat';
                    $dataItem['conversation_info']['preview_text'] = $text;

                }
            }
            if ($dataItem != null) {
                $data[] = $dataItem;
            }
        }
        return $data;
    }


    /**
     * @return array
     */
    public function setupParametersQuestionsToInvite()
    {
        $first = 0;
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $questions = array();
        if(isset($_POST['questions']))
        {
            $questions= json_decode($_POST['questions'],true);
        }

        $accountType = null;
        if (isset($_POST['accountType']) && $_POST['accountType'] !== BOL_QuestionService::ALL_ACCOUNT_TYPES )
        {
            $accountType = $_POST['accountType'];
            $questions['accountType'] = $_POST['accountType'];
        }

        return array($first,$accountType,$questions);
    }


    private function checkForwardSearchAccess($userId)
    {
        $pluginActive = FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true);
        if(!$pluginActive){
            return array( false,'plugin_not_found');
        }
        if ( !OW::getUser()->isAuthenticated())
        {
            return array(false,'authorization_error');
        }
        return array(true,'');
    }

    private function checkGroupSearchAccess($groupId)
    {
        $pluginActive = FRMSecurityProvider::checkPluginActive('groups', true);
        if(!$pluginActive){
            return array( false,'plugin_not_found');
        }
        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            return array(false,'authorization_error');
        }
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if($group == null){
            return array( false,'group_not_found_error');
        }
        if(!OW::getUser()->isAdmin()){
            if(!GROUPS_BOL_Service::getInstance()->isCurrentUserInvite($group->id,true,true,$group)) {
                return array(false, 'authorization_error');
            }
        }
        return array(true,'');
    }


    private function checkCurrentUserCanPostToThisUser($userId)
    {
        $canSendPost = true;
        if(OW::getUser()->isAdmin())
        {
            return $canSendPost;
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $whoCanPostPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy('who_post_on_newsfeed', $userId);
            if (isset($whoCanPostPrivacy) && $whoCanPostPrivacy == 'only_for_me') {
                $canSendPost = false;
            }else if(isset($whoCanPostPrivacy) && $whoCanPostPrivacy == 'friends_only'){
                $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship(OW::getUser()->getId(), $userId);
                if (!isset($isFriends) || $isFriends->status != 'active')
                {
                    $canSendPost = false;
                }
            }
        }
        return $canSendPost;
    }

    /**
     * @param array $questions
     * @param int $first
     * @param int $count
     * @param $entityId
     * @return array
     */
    public function getForwardSearchedUsersByQuestions($questions = array(),$first = 0, $count, $entityId)
    {
        $users = array();
        if (empty($count)) {
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        }
        $userService = BOL_UserService::getInstance();
        $additionalParameters = array();
        $queryParams = array();
        $additionalParameterEvent = OW_EventManager::getInstance()->trigger(new OW_Event('search.additional.parameter',['entityType'=>'newsfeed', 'entityId' => $entityId]));
        if(isset($additionalParameterEvent->getData()['where'])) {
            $additionalParameters['where'] = $additionalParameterEvent->getData()['where'];
            $queryParams = $additionalParameterEvent->getData()['whereParams'];
        }
        $key = '';
        $idList = $userService->findUserIdListByQuestionValues($questions, $first, $count, true,$additionalParameters,$queryParams);
        $idList = BOL_UserService::getInstance()->filterUnapprovedStatusForUserList($idList);
        $usersObject = BOL_UserService::getInstance()->findUserListByIdList($idList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($idList);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList);
        /* @var $user BOL_User */
        foreach ($usersObject as $user){
            $params['security']['send_post'] = $this->checkCurrentUserCanPostToThisUser($user->id);
            $avatarUrl = null;
            if(isset($avatars[$user->getId()])){
                $avatarUrl = $avatars[$user->id];
            }
            $displayName = null;
            if(isset($displayNames[$user->id])){
                $displayName = $displayNames[$user->id];
            }
            $users[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($user, $avatarUrl, $displayName, false, true, $params);
        }
        return $users;
    }

    /**
     * @param array $questions
     * @param int $first
     * @param int $count
     * @param $entityId
     * @return array
     */
    public function getGroupSearchedUsersByQuestions($questions = array(),$first = 0,$count, $entityId)
    {
        if(empty($count)) {
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        }
        $userService = BOL_UserService::getInstance();
        $additionalParameters = array();
        $queryParams = array();
        $additionalParameterEvent = OW_EventManager::getInstance()->trigger(new OW_Event('search.additional.parameter',['entityType'=>'groups', 'entityId' => $entityId]));
        if(isset($additionalParameterEvent->getData()['where'])) {
            $additionalParameters['where'] = $additionalParameterEvent->getData()['where'];
            $queryParams = $additionalParameterEvent->getData()['whereParams'];
        }

        $key = '';
        $idList = $userService->findUserIdListByQuestionValues($questions, $first, $count, true,$additionalParameters,$queryParams);

        $users = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->populateInvitableUserList($idList, $key, 0, $count);
        return $users;
    }

    public function getSearchedUsersByQuestions()
    {

        if ( !OW::getUser()->isAuthenticated() )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if ( !isset($_POST['entityType']) || !isset($_POST['entityId']))
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        list($first,$accountType,$questions) = $this->setupParametersQuestionsToInvite();
        $users = array();
        $entityType = $_POST['entityType'];
        $entityId = $_POST['entityId'];
        switch ($entityType) {
            case 'groups':
                list($valid,$message) = $this->checkGroupSearchAccess($entityId);
                if(!$valid)
                {
                    return array('valid' => $valid, 'message' => $message);
                }
                $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
                $users = $this->getGroupSearchedUsersByQuestions($questions,$first,$count,$entityId);
                break;
            case 'newsfeed':
                list($valid,$message) = $this->checkForwardSearchAccess($entityId);
                if(!$valid)
                {
                    return array('valid' => $valid, 'message' => $message);
                }
                $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
                $users = $this->getForwardSearchedUsersByQuestions($questions,$first,$count,$entityId);
                break;
            default:
                return  array('valid' => false, 'message' => 'invalid entityType');
        }

        return $users;
    }


   public function getInviteEntitySearchableQuestions()
   {
       if(!OW::getUser()->isAuthenticated()){
           return array('valid' => false, 'message' => 'authorization_error');
       }
       $data['searchable_fields'] = $this->getSearchableFieldsForAllAccountTypes();
       $data['account_type_labels'] = $this->getAccountLabelTypes();
       $data['default_account_type_labels'] = $this->getDefaultAccountLabels();
       return $data;
   }


    public function getChatsAndChannelsAndGroups(){
      $data['chatsAndGroupsAndChannels']= $this->getChatAndGroups();
      if(isset($data['chatsAndGroupsAndChannels']['valid']) && $data['chatsAndGroupsAndChannels']['valid'] ==false)
      {
          return $data;
      }

      $searchValue = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->prepareGetSearchValue();
      $data["groups"] = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->getGroups('latest','group',$searchValue);
      if(isset($data['groups']['valid']) && $data['groups']['valid'] ==false) {
          return $data;
      }


      $data["channels"] = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance()->getGroups('latest','chanel',$searchValue);
      if(isset($data['channels']['valid']) && $data['channels']['valid'] ==false) {
          return $data;
      }

      $data['chats'] = $this->getUserChats();
      if(isset($data['chats']['valid']) && $data['chats']['valid'] ==false) {
            return $data;
      }

      return $data;
    }

    public function getBlockedUsers() {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        $userIds = BOL_UserService::getInstance()->findBlockedUserList(OW::getUser()->getId(), $first, $count);
        $userInfo = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($userIds);
        
        return $userInfo;
    }

    public function sendVerificationCodeToMobile() {
        $request['type']='send_verification_code_to_mobile';
        $request['mobileNumber'] = $_POST['mobileNumber'];
        $message_event = OW::getEventManager()->trigger(new OW_Event("frmsms.check_received_message", array('data' => $request)));
        $message_event = json_decode($message_event->getData());
        if (!$message_event) {
            $message_event = array();
        }

        return $message_event;
    }

    /************ personal files *************/
    private function hasAccessById($id, $editItself = false){
        $third_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($id);
        if(!isset($third_level_row))
        {
            return false;
        }
        $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
        $level = 3;
        while($second_level_row->parent_id > 1){
            $third_level_row = $second_level_row;
            $second_level_row = FRMFILEMANAGER_BOL_FileDao::getInstance()->findById($third_level_row->parent_id);
            $level += 1;
        }
        $entity_type =  FRMFILEMANAGER_BOL_Service::getInstance()->getEntityTypeFromName($third_level_row->name);
        $entity_id = FRMFILEMANAGER_BOL_Service::getInstance()->getEntityIdFromName($third_level_row->name);

        if ( ($editItself && $level <=3) or $level <=2 )
        {
            return false;
        }
        if ($entity_type == 'profile'){
            return $entity_id == OW::getUser()->getId();
        }
        if ($entity_type == 'groups'){
            $g = GROUPS_BOL_Service::getInstance()->findGroupById($entity_id);
            return GROUPS_BOL_Service::getInstance()->isCurrentUserCanView( $g );
        }
        return false;
    }

    public function addFile(){
        if ( isset($_POST['parent_id']) )
        {
            if(!$this->hasAccessById($_POST['parent_id'])){
                return array('valid' => false, 'message' => 'authorization_error');
            }
        }

        if (isset($_FILES['file']) && isset($_FILES['file']['tmp_name'])) {
            $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
            if (!$isFileClean) {
                return array('valid' => false, 'message' => 'virus_detected');
            }
        }

        $resultArr = BOL_UserService::getInstance()->manageAddFile($_FILES['file']);
        if(!isset($resultArr) || !$resultArr['result']){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $uId = OW::getUser()->getId();
        OW::getEventManager()->call('frmfilemanager.after_file_upload',
            array('entityType'=>'profile', 'entityId'=>$uId, 'dto'=>$resultArr['dtoArr']['dto'], 'file' => $_FILES['file']));

        $filesInformation = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->preparedFileListByEntity('profile', $uId);
        return array('valid' => true, 'files' => $filesInformation);
    }

    public function deleteFile(){
        if ( !isset($_POST['id']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $attachmentId = $_POST['id'];
        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        if($attachment->userId != OW::getUser()->getId()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        try {
            BOL_AttachmentService::getInstance()->deleteAttachmentById($attachmentId);
            return array('valid' => true, 'id' => (int) $attachmentId);
        }
        catch (Exception $e){
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function editFile(){
        if ( !isset($_POST['id']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $attachmentId = $_POST['id'];
        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        if($attachment->userId != OW::getUser()->getId()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        try {
            $new_name = isset($_POST['new_name'])?$_POST['new_name']:null;

            $new_parent_id = null;
            if(isset($_POST['new_parent_id'])) {
                $new_parent_id = $_POST['new_parent_id'];
                if (!$this->hasAccessById($_POST['new_parent_id'])) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }
            }

            BOL_AttachmentService::getInstance()->editAttachmentById($attachmentId, $new_name, $new_parent_id);
            return array('valid' => true, 'id' => (int) $attachmentId);
        }
        catch (Exception $e){
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function addDir(){
        if ( !isset($_POST['name']) )
        {
            return array('valid' => false, 'message' => 'input_error');
        }

        $uId = OW::getUser()->getId();
        $myProfile = FRMFILEMANAGER_BOL_Service::getInstance()
            ->getByPath('frm:profile/frm:profile:'.$uId);
        if(empty($myProfile)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $parent_id = $myProfile->id;
        if(isset($_POST['parent_id']) && $this->hasAccessById((int)$_POST['parent_id'])){
            $parent_id = (int) $_POST['parent_id'];
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $service->insert($_POST['name'], $parent_id, 'directory', time(), '', true, false);
        $subFolders = $service->getSubfolders('profile', (int) $uId);
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    public function editDir(){
        if (!isset($_POST['id'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!$this->hasAccessById($_POST['id'], true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if( !isset($_POST['new_name']) && !isset($_POST['new_parent_id']) ) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $new_name = isset($_POST['new_name'])?$_POST['new_name']:null;

        $new_parent_id = isset($_POST['new_parent_id'])?$_POST['new_parent_id']:null;
        if(isset($_POST['new_parent_id']) && !$this->hasAccessById($_POST['new_parent_id'])){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $service->editDirById($_POST['id'], $new_name, $new_parent_id);
        $subFolders = $service->getSubfolders('profile', OW::getUser()->getId());
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    public function deleteDir(){
        if (!isset($_POST['id'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!$this->hasAccessById($_POST['id'], true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $service = FRMFILEMANAGER_BOL_Service::getInstance();
        $service->deleteDirById($_POST['id']);
        $subFolders = $service->getSubfolders('profile', OW::getUser()->getId());
        return array('valid' => true, 'subfolders' => $subFolders);
    }

    public function saveFileToProfile(){
        if (!isset($_POST['id'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        $attachmentId = $_POST['id'];
        $file = FRMFILEMANAGER_BOL_Service::getInstance()->findByAttachmentId($attachmentId);

        if(!isset($file) || !$this->hasAccessById($file->id, true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $res = FRMFILEMANAGER_BOL_Service::getInstance()->moveToMyProfile($file->id);
        if (!$res) {
            return array('valid' => false, 'message' => 'input_error');
        }
        return array('valid' => true);
    }

    /********** end of personal files ************/
}