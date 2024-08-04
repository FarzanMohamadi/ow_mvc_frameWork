<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsso.bol
 * @since 1.0
 */
class FRMSSO_BOL_Service
{
    private static $classInstance;
    const SSO_SESSION_KEY = 'sso-session';
    const SSO_COOKIE_KEY = 'sso-session';
    const LAST_REQUEST_PATH_KEY = 'lrp';
    const SSO_USERNAME = 'sso-username';
    const SSO_EMAIL = 'sso-email';

    private function __construct()
    {
        $this->loggedoutTicketDao = FRMSSO_BOL_LoggedoutTicketDao::getInstance();
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
        return OW::getSession()->isKeySet(FRMSSO_BOL_Service::SSO_USERNAME);
    }
    public function setSSOLoggedIn($username, $email){
        OW::getSession()->set(FRMSSO_BOL_Service::SSO_USERNAME, $username);
        OW::getSession()->set(FRMSSO_BOL_Service::SSO_EMAIL, $email);

    }
    public function removeSSOLoggedInSession(){
        if (OW::getSession()->isKeySet(FRMSSO_BOL_Service::SSO_USERNAME)){
            OW::getSession()->delete(FRMSSO_BOL_Service::SSO_USERNAME);
        }
        if (OW::getSession()->isKeySet(FRMSSO_BOL_Service::SSO_EMAIL)){
            OW::getSession()->delete(FRMSSO_BOL_Service::SSO_EMAIL);
        }

    }
    public function loginUser($user, $ticket, $ttl)
    {
        OW_User::getInstance()->login($user->getId());
        if (isset($_COOKIE['ow_login'])){
            BOL_UserService::getInstance()->updateLoginCookie(trim($_COOKIE['ow_login']), (time() + $ttl));
        }else{
            setcookie('ow_login', "1", (time() + $ttl), '/', null, false, true);
        }
        OW::getSession()->set(FRMSSO_BOL_Service::SSO_SESSION_KEY, $ticket);

    }

    public function loginUserIfSSOLoggedIn(OW_Event $event)
    {
        if (OW::getConfig()->getValue('frmsso', 'ssoSameDomain') != '1' || !isset($_COOKIE[FRMSSO_BOL_Service::SSO_COOKIE_KEY])) {
            $this->removeSSOLoggedInSession();
            return;
        }
        $ssoCookie = $_COOKIE[FRMSSO_BOL_Service::SSO_COOKIE_KEY];
        if (!FRMSSO_BOL_Service::getInstance()->validateSSOCookieSignature($ssoCookie)) {
            $this->removeSSOLoggedInSession();
            return;
        }
        $splittedCookie = explode("-", $ssoCookie);
        $signature = $splittedCookie[sizeof($splittedCookie) - 1];
        $cookie_age = $splittedCookie[sizeof($splittedCookie) - 2];
        $ticket = str_replace("-" . $cookie_age . "-" . $signature, "", $ssoCookie);
        $loginResult = true;
        if (!OW::getUser()->isAuthenticated()) {
            $loginResult = FRMSSO_BOL_Service::getInstance()->checkUserAndAuthenticate($ticket);
        }
        if ($loginResult) {
            setcookie(FRMSSO_BOL_Service::SSO_COOKIE_KEY, trim($_COOKIE[FRMSSO_BOL_Service::SSO_COOKIE_KEY]), (time() + $cookie_age),
                '/', OW::getConfig()->getValue('frmsso', 'ssoSharedCookieDomain'), false, true);
        }
    }

    public function validateSSOCookieSignature($ssoCookie)
    {
        $splittedCookie = explode("-", $ssoCookie);
        if (sizeof($splittedCookie) <= 3)
            return false;
        $signature = $splittedCookie[sizeof($splittedCookie) - 1];
        $cookieWithoutSignature = str_replace("-" . $signature, "", $ssoCookie);
        $newSignature = hash_hmac('sha1', $cookieWithoutSignature, OW::getConfig()->getValue('frmsso', 'ssoServerSecret'));
        if ($newSignature != $signature)
            return false;
        return true;
    }

    public function logoutUserIfRequired(OW_Event $event)
    {
        $ssoTicket = OW::getSession()->get(FRMSSO_BOL_Service::SSO_SESSION_KEY);
        if (empty($ssoTicket)) {
            if($this->isLoggedInMember()){
                $this->logout();
            }

            return;
        }
        $loggedoutTicket = FRMSSO_BOL_Service::getInstance()->getLoggedoutTicket($ssoTicket);
        if ($loggedoutTicket) {
            $this->logout();
            FRMSSO_BOL_Service::getInstance()->deleteLoggedoutTicket($ssoTicket);
        }
    }

    public function logout()
    {
        OW::getUser()->logout();
        if (isset($_COOKIE['ow_login'])) {
            setcookie('ow_login', '', time() - 3600, '/');
        }

        OW::getSession()->set('no_autologin', true);
        if ($this->isSsoSameDomainActive() && isset($_COOKIE[FRMSSO_BOL_Service::SSO_COOKIE_KEY])){
            setcookie(FRMSSO_BOL_Service::SSO_COOKIE_KEY, '', time() - 3600, '/');
        }
        OW::getSession()->delete(FRMSSO_BOL_Service::SSO_SESSION_KEY);
    }

    private function getLoggedoutTicket($ssoTicket)
    {
        return $this->loggedoutTicketDao->getLoggedoutTicket($ssoTicket);
    }

    public function checkUserAndAuthenticate($ticket)
    {
        $params = array('ticket' => $ticket);
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
            FRMSSO_BOL_Service::getInstance()->loginUser($user, $ticket, $output['session_age_seconds']);
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
                FRMSSO_BOL_Service::getInstance()->loginUser($user, $ticket, $output['session_age_seconds']);
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
            $component->addComponent('changePassword', new FRMSSO_CMP_ChangePassword());
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
            $loginUrl = OW::getConfig()->getValue('frmsso', 'ssoUrl') .
                OW::getConfig()->getValue('frmsso', 'ssoLoginUrl') .
                "?service=" . OW::getRouter()->getBaseUrl();
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
        $params['username'] = OW::getSession()->get(FRMSSO_BOL_Service::SSO_USERNAME);
        $params['password'] = '-';
        $data = array("isSSOEnabled" => true, 'joinData'=>$params, 'SSOEmail' => OW::getSession()->get(FRMSSO_BOL_Service::SSO_EMAIL));
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
                $form->getElement($question['name'])->setValue(OW::getSession()->get(FRMSSO_BOL_Service::SSO_EMAIL));
                $form->getElement($question['name'])->setDescription(OW::getLanguage()->text("frmsso", "emailComesFromSSO"));
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
    private function askSSOServer($ssoSubPath, $params){
        $ch = curl_init();
        $validationUrl = OW::getConfig()->getValue('frmsso', 'ssoUrl') .
            OW::getConfig()->getValue('frmsso', $ssoSubPath);
        $header = array(
            'Authorization: ' . OW::getConfig()->getValue('frmsso', 'ssoClientSecret')
        );
        curl_setopt($ch, CURLOPT_URL, $validationUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $output;

    }
    public function isSsoSameDomainActive(){
        return OW::getConfig()->getValue('frmsso', 'ssoSameDomain') == '1';
    }
    public function isSsoAutoRegisterUsersActive(){
        return OW::getConfig()->getValue('frmsso', 'autoRegisterUsers') == '1';
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
            $name = OW::getLanguage()->text('frmsso', 'field_mobile_label');
            $description = OW::getLanguage()->text('frmsso', 'field_mobile_description');
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
