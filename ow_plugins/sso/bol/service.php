<?php
/**
 * 
 * All rights reserved.
 */
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.sso.bol
 * @since 1.0
 */

class SSO_BOL_Service
{
    private static $classInstance;
    const SSO_SESSION_KEY = 'sso-session';
    const SSO_COOKIE_KEY = 'sso-session';
    const LAST_REQUEST_PATH_KEY = 'lrp';
    const SSO_USERNAME = 'sso-username';
    const SSO_EMAIL = 'sso-email';

    private function __construct()
    {
        $this->loggedoutTicketDao = SSO_BOL_LoggedoutTicketDao::getInstance();
    }

    public function addLoggedoutTicket($ticket)
    {
        return $this->loggedoutTicketDao->addLoggedoutTicket($ticket);
    }

    public function deleteLoggedoutTicket($ticket)
    {
        return $this->loggedoutTicketDao->deleteLoggedoutTicket($ticket);
    }

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function isLoggedInMember(){
        return OW::getUser()->isAuthenticated();
    }
    public function isSSOLoggedIn(){
        return OW::getSession()->isKeySet(SSO_BOL_Service::SSO_USERNAME);
    }
    public function setSSOLoggedIn($username, $email){
        OW::getSession()->set(SSO_BOL_Service::SSO_USERNAME, $username);
        OW::getSession()->set(SSO_BOL_Service::SSO_EMAIL, $email);

    }
    public function removeSSOLoggedInSession(){
        if (OW::getSession()->isKeySet(SSO_BOL_Service::SSO_USERNAME)){
            OW::getSession()->delete(SSO_BOL_Service::SSO_USERNAME);
        }
        if (OW::getSession()->isKeySet(SSO_BOL_Service::SSO_EMAIL)){
            OW::getSession()->delete(SSO_BOL_Service::SSO_EMAIL);
        }

    }
    public function loginUser($user)
    {
        $userId = $user->id;
        $result = new OW_AuthResult(OW_AuthResult::SUCCESS, $userId);

        if ( $result->isValid() )
        {
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

            $_POST['access_token'] = $loginCookie->getCookie();
            if(FRMSecurityProvider::checkPluginActive('frmuserlogin', true)){
                FRMUSERLOGIN_BOL_Service::getInstance()->updateActiveDetails();
            }
            OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.success'));
            return  true;
        }

    }

    public function loginUserIfSSOLoggedIn(OW_Event $event)
    {
        if (!isset($_GET['code'])) {
            $this->removeSSOLoggedInSession();
            return;
        }
        $ssoCode = $_GET['code'];
        $loginResult = true;
        if (!OW::getUser()->isAuthenticated()) {
            $loginResult = SSO_BOL_Service::getInstance()->checkUserAndAuthenticate($ssoCode);
        }
        if ($loginResult) {
            setcookie(SSO_BOL_Service::SSO_COOKIE_KEY, trim($_COOKIE[SSO_BOL_Service::SSO_COOKIE_KEY]), (time() + $cookie_age),
                '/', OW::getConfig()->getValue('sso', 'ssoSharedCookieDomain'), false, true);
        }
    }

    public function validateSSOCookieSignature($ssoCookie)
    {
        $splittedCookie = explode("-", $ssoCookie);
        if (sizeof($splittedCookie) <= 3)
            return false;
        $signature = $splittedCookie[sizeof($splittedCookie) - 1];
        $cookieWithoutSignature = str_replace("-" . $signature, "", $ssoCookie);
        $newSignature = hash_hmac('sha1', $cookieWithoutSignature, OW::getConfig()->getValue('sso', 'ssoServerSecret'));
        if ($newSignature != $signature)
            return false;
        return true;
    }

    public function logoutUserIfRequired(OW_Event $event)
    {
        $ssoTicket = OW::getSession()->get(SSO_BOL_Service::SSO_SESSION_KEY);
        if (empty($ssoTicket)) {
            if($this->isLoggedInMember()){
                $this->logout();
            }

            return;
        }
        $loggedoutTicket = SSO_BOL_Service::getInstance()->getLoggedoutTicket($ssoTicket);
        if ($loggedoutTicket) {
            $this->logout();
            SSO_BOL_Service::getInstance()->deleteLoggedoutTicket($ssoTicket);
        }
    }

    public function logout()
    {
        OW::getUser()->logout();
        if (isset($_COOKIE['ow_login'])) {
            setcookie('ow_login', '', time() - 3600, '/');
        }

        OW::getSession()->set('no_autologin', true);
        if ($this->isSsoSameDomainActive() && isset($_COOKIE[SSO_BOL_Service::SSO_COOKIE_KEY])){
            setcookie(SSO_BOL_Service::SSO_COOKIE_KEY, '', time() - 3600, '/');
        }
        OW::getSession()->delete(SSO_BOL_Service::SSO_SESSION_KEY);
    }

    private function getLoggedoutTicket($ssoTicket)
    {
        return $this->loggedoutTicketDao->getLoggedoutTicket($ssoTicket);
    }

    public function signInByAuthenticationCode($code, $redirectUrl){

        $params = array(
            'grant_type'=>'authorization_code',
            'code' => $code,
            'client_id' => 'mlocalClient',
            'client_secret' => 'mlocalSecret',
            'redirect_uri' => $redirectUrl,
            'code_verifier' => '',
        );
        $tokenUrl = OW::getConfig()->getValue('sso', 'ssoGetToken');

        // get token:

        $output = $this->askSSOServer($tokenUrl, $params, null);
        if(isset($output) && $output != null && $output['error'] == null){
            $accessToken = $output['access_token'];

            // get user information
            $header =  array(
                'Authorization: Bearer ' . $accessToken
            );
            $userInfoUrl = OW::getConfig()->getValue('sso', 'usersDetailsUrl');
            $userInfo = $this->askSSOServer($userInfoUrl, null, $header);

            // find user in local database
            if(isset($userInfo)) {
                $userById = BOL_UserService::getInstance()->findByJoinIp($userInfo['id']);
                $userByUsername = BOL_UserService::getInstance()->findByUsername($userInfo['username']);

                // If has foreign key:
                if (isset($userById) && $userById != null) {
                    $this->loginUser($userById);
                    $redirectTo = OW::getRouter()->getBaseUrl();
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userById->getId());
                    UTIL_Url::redirect($redirectTo);

                    // If hasn't foreign key search for username:
                } elseif (isset($userByUsername) && $userByUsername != null) {
                    $this->loginUser($userByUsername);
                    $userByUsername->joinIp = $userInfo['id'];
                    BOL_UserService::getInstance()->saveOrUpdate($userByUsername);
                    BOL_QuestionService::getInstance()->saveQuestionsData(array('mobile_number' => $userInfo['username']), $userByUsername->getId());
                    $redirectTo = OW::getRouter()->getBaseUrl();
                    UTIL_Url::redirect($redirectTo);

                    // else register user:
                } else {
                    $mobileNumber = $params['mobileNumber'];
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
                        $data['realname'] = $username;
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
                    $this->loginUser($user);
                    $redirectTo = OW::getRouter()->getBaseUrl();
                    UTIL_Url::redirect($redirectTo);
                }
            }else{
                OW::getFeedback()->info('No response from SSO login server!');
                throw new Redirect404Exception('No response from SSO login server!');
            }
        }
    }

    public function mobileSSOVerifyCodeUpdateUser($code, $redirectUrl){

        $params = array(
            'grant_type'=>'authorization_code',
            'code' => $code,
            'client_id' => 'mlocalClient',
            'client_secret' => 'mlocalSecret',
            'redirect_uri' => $redirectUrl,
            'code_verifier' => '',
        );
        $tokenUrl = OW::getConfig()->getValue('sso', 'ssoGetToken');

        // get token:

        $output = $this->askSSOServer($tokenUrl, $params, null);
        if(isset($output) && $output != null && $output['error'] == null) {
            $accessToken = $output['access_token'];

            // get user information
            $header = array(
                'Authorization: Bearer ' . $accessToken
            );
            $userInfoUrl = OW::getConfig()->getValue('sso', 'usersDetailsUrl');
            $userInfo = $this->askSSOServer($userInfoUrl, null, $header);
            $userInfo['refresh_token'] = $output['refresh_token'];
            $userInfo['access_token'] = $accessToken;

            if (isset($userInfo)) {
                return $userInfo;
            }else{
                return null;
            }
        }elseif ($output['error'] == 'invalid_grant'){
            return "invalid_grant_error";
        }
    }

    public function checkUserAndAuthenticate($ssoCode)
    {
        $params = array('ticket' => $ssoCode);
        $output = $this->askSSOServer('ssoTicketValidationUrl', $params);
        if ($output['status'] != 'valid') {
            return false;
        }
        $username = $output['username'];
        $email = $output['email'];
        $session_age = $output['session_age_seconds'];
        $user = BOL_UserService::getInstance()->findByUsername($username);
        if ($this->isSsoAutoRegisterUsersActive()) {
            if (!$user) {
                $user = $this->addSSOUserToSocialNetwork($username);
            }
            SSO_BOL_Service::getInstance()->loginUser($user, $ssoCode, $output['session_age_seconds']);
        }else {
            if (!$user) {
                $this->setSSOLoggedIn($username, $email);
            } else {
                if (OW::getUser()->isAuthenticated()) {
                    if (OW::getUser()->getId() !== $user->getId()) {
                        OW::getUser()->logout();
                        return false;
                    }
                }
                SSO_BOL_Service::getInstance()->loginUser($user, $ssoCode, $output['session_age_seconds']);
            }
        }
        OW::getSession()->set('frm_session_age', $session_age);
        return true;

    }
    public function popLastRequestPath(){
        if (isset($_COOKIE['lrp'])) {
            $path = $_COOKIE['lrp'];
            unset($_COOKIE['lrp']);
            setcookie('lrp', null, -1, '/', null, false, true);
            return $path;
        }
        return null;
    }

    public function changeSignInButton(OW_Event $event)
    {
        $show_sign_up_button = true;
        $show_sign_out_button = false;
        $show_sign_in_button = true;
        $sign_in_button = null;
        if ($this->isSSOLoggedIn()){
            $show_sign_out_button = true;
            $show_sign_in_button = false;
        }else{
            if ($this->isLoggedInMember() || (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS ){
                $show_sign_up_button = false;
            }
            $sign_in_button = new BASE_CMP_ConsoleButton(OW::getLanguage()->text('base', 'sign_in_submit_label'), OW::getRouter()->urlForRoute('static_sign_in').'?back-uri='.OW::getRequest()->getRequestUri());
        }
        $event->setData(array(
            'sso_enabled' => true,
            'sign_in_button' => $sign_in_button,
            'show_sign_out_button' => $show_sign_out_button,
            'show_sign_up_button' => $show_sign_up_button,
            'show_sign_in_button' => $show_sign_in_button,
        ));
    }

    public function switchChangePasswordComponent(OW_Event $event)
    {
        $params = $event->getParams();
        $component = $params['component'];
        if ($component->getComponent('changePassword')) {
            $component->addComponent('changePassword', new SSO_CMP_ChangePassword());
        }
    }
    public function setLastRequestPath($path){
        setcookie('lrp', $path, (time() + 10000000), '/', null, false, true);
    }
    public function getLastRequestPath(){
        if(OW::getRequest()->isAjax() || OW::getRequest()->isPost()){
            return OW_URL_HOME;
        }

        $lastRequestPath = $this->popLastRequestPath();
        if ($lastRequestPath) {
            if (!$this->isRedirectURLValid($lastRequestPath)){
                $lastRequestPath = OW_URL_HOME;
            }
            return $lastRequestPath;
        }else{
            return OW_URL_HOME;
        }
    }
    public function isRedirectURLValid($url){
        if (substr( $url, 0, 4 ) === "http"){
            if (!substr($url, 0, strlen(OW_URL_HOME)) === OW_URL_HOME){
                return false;
            }
        }
        return true;
    }
    public function beforeJoinControllerStart(){
        if ($this->isLoggedInMember()){
            throw new RedirectException(OW_URL_HOME);
        }
        if (!$this->isSSOLoggedIn()){
            $loginUrl = OW::getConfig()->getValue('sso', 'ssoUrl') .
                OW::getConfig()->getValue('sso', 'ssoLoginUrl') .
                "&redirect_uri=" . OW::getRouter()->getBaseUrl() . '&scope=openid&response_type=code&response_mode=query&nonce=avtt5u79xe4';
            $this->setLastRequestPath(OW::getRouter()->urlForRoute('base_join'));
            throw new RedirectException($loginUrl);
        }
    }
    public function beforeSendVerificationEmail(OW_Event $event){
        $params = $event->getParams();
        $user = $params['user'];
        if (!empty($params['SSOEmail'])) {
            if ($user->email == $params['SSOEmail']){
                $event->setData(array('send_verification_email' => false));
                $user->emailVerify = true;
                BOL_UserService::getInstance()->saveOrUpdate($user);
            }
            if (!$this->isSsoAutoRegisterUsersActive()) {
                $this->setSSOLoggedIn($user->getUsername(), $params['SSOEmail']);
            }
        }
    }
    public function setUsernameUsingSSOSession(OW_Event $event){
        if (!$this->isSSOLoggedIn()){
            return;
        }
        $params = $event->getParams();
        $params['username'] = OW::getSession()->get(SSO_BOL_Service::SSO_USERNAME);
        $params['password'] = '-';
        $data = array("isSSOEnabled" => true, 'joinData'=>$params, 'SSOEmail' => OW::getSession()->get(SSO_BOL_Service::SSO_EMAIL));
        $event->setData($data);

    }
    public function setEmailAndDisableUsername(OW_Event $event){
        if (!$this->isSSOLoggedIn()){
            return;
        }
        $params = $event->getParams();
        $form = $params['form'];
//        $form->deleteElement('password');
//        $form->deleteElement('repeatPassword');
        $shouldDrop = [];
        foreach($form->sortedQuestionsList as $i => $question) {
            if (empty($question['realName'])) {
                continue;
            }
            if ($question['realName'] == 'password') {
                $form->deleteElement($question['name']);
                array_push($shouldDrop,$i);
            }

            if ($question['realName'] == 'username') {
                $form->deleteElement($question['name']);
                array_push($shouldDrop,$i);
            }

            if ($question['realName'] == 'email') {
                $form->getElement($question['name'])->addAttribute("class", "ow_email_validator");
                $form->getElement($question['name'])->setValue(OW::getSession()->get(SSO_BOL_Service::SSO_EMAIL));
                $form->getElement($question['name'])->setDescription(OW::getLanguage()->text("sso", "emailComesFromSSO"));
            }
        }
        foreach ($shouldDrop as $item){
            unset($form->sortedQuestionsList[$item]);
        }
        $newQuestionListBySection = [];
        foreach ($form->questionListBySection as $sectionIndex => $section){
            $shouldDrop2 = [];
            foreach ($section as $i => $item){
                if (in_array($item['name'], $shouldDrop)){
                    array_push($shouldDrop2, $i);
                }
            }
            foreach ($shouldDrop2 as $index){
                unset($section[$index]);
            }
            $newQuestionListBySection[$sectionIndex] = $section;
        }
        $form->questionListBySection = $newQuestionListBySection;
        $questionIndex = null;
        foreach($form->questions as  $i => $question){
            if($question['name'] == 'password'){
                $questionIndex = $i;
            }
        }
        if ($questionIndex !== null){
            unset($form->questions[$questionIndex]);
        }
        $form->deleteElement("repeatPassword");
    }
    function onBeforeProfileEditFormBuild(OW_Event $event){
        $params = $event->getParams();
        $questions = $params['questions'];
        $questionNamelistIndex = null;
        foreach($questions as  $i => $question){
            if($question['name'] == "username"){
                $questionNamelistIndex = $i;
            }
        }
        if ($questionNamelistIndex !== null){
            unset($questions[$questionNamelistIndex]);
        }
        $data = array("questions" => $questions);
        $event->setData($data);
    }
    public function askSSOServer($path, $params = null, $header = null){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);

        if(isset($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if(isset($params)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $output;

    }
    public function isSsoSameDomainActive(){
        return OW::getConfig()->getValue('sso', 'ssoSameDomain') == '1';
    }
    public function isSsoAutoRegisterUsersActive(){
        return OW::getConfig()->getValue('sso', 'autoRegisterUsers') == '1';
    }

    private function addSSOUserToSocialNetwork($username)
    {
        $output = $this->askSSOServer('usersDetailsUrl', array('username'=>$username));
        if ($output['status'] != 'valid'){
            throw new LogicException("Could not fetch details of user from SSO");
        }
        $user = BOL_UserService::getInstance()->createUser($username, "-",$output['email'],1, true);
        $questionService = BOL_QuestionService::getInstance();
        $data = array();
        $data['realname'] = $output['full_name'];
        $data['sso-phoneNumber'] = $output['phone_number'];
        $questionService->saveQuestionsData($data, $user->getId());
        return $user;
    }
    public function createMobileField(){
        if (!$this->isSsoAutoRegisterUsersActive()) {
            return;
        }
        $username = BOL_QuestionService::getInstance()->findQuestionByName('username');
        $usernameAccountTypes = BOL_QuestionToAccountTypeDao::getInstance()->findByQuestionName('username');
        $usernameAccountTypeNames = array();
        foreach ($usernameAccountTypes as $item){
            array_push($usernameAccountTypeNames,$item->accountType);
        }
        $QUESTION_NAME = 'sso-phoneNumber';
        $question = BOL_QuestionService::getInstance()->findQuestionByName('sso-phoneNumber');
        if ($question == null){
            $question = new BOL_Question();
            $question->name = $QUESTION_NAME;
            $question->required = true;
            $question->onJoin = true;
            $question->onEdit = true;
            $question->onSearch = false;
            $question->onView = false;
            $question->presentation = 'text';
            $question->type = 'text';
            $question->columnCount = 0;
            $question->sectionName = $username->sectionName;
            $question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($question->sectionName) ) + 1;
            $question->custom = json_encode(array());
            $question->removable = false;
            $questionValues = false;
            $name = OW::getLanguage()->text('sso', 'field_mobile_label');
            $description = OW::getLanguage()->text('sso', 'field_mobile_description');
            BOL_QuestionService::getInstance()->createQuestion($question, $name, $description, $questionValues, true);
            BOL_QuestionService::getInstance()->addQuestionToAccountType($QUESTION_NAME, array_unique($usernameAccountTypeNames));
        }else{
            $question->required = true;
            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
        }

        $questions = BOL_QuestionService::getInstance()->findAllQuestions();
        foreach($questions as  $question){
            if (!in_array($question->name, array('username', 'email', 'password', 'realName', 'password', $QUESTION_NAME))){
                $question->required = false;
                BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
            }
        }
    }

    public function onBeforeFormSigninRender(OW_Event $event)
    {
        $params = $event->getParams();

        $form = new Form('ssoForm');
        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
        $form->addElement($submit);
        $form->setAction(OW::getRouter()->urlForRoute('static_sign_in').'?back-uri='.OW::getRequest()->getRequestUri());
        $joinButton = new BASE_MCMP_JoinButton();
        $event->setData(array(
            'ssoForm' => $form,
            'joinButton'=>$joinButton
        ));
    }
}
