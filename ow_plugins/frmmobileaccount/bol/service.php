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
class FRMMOBILEACCOUNT_BOL_Service
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

    const BOTH_VERSION = 1;
    const MOBILE_VERSION = 2;
    const DESKTOP_VERSION = 3;
    const PASSWORD = 1;
    private $email_postfix='';
    private $username_prefix='';
    private function __construct()
    {
        $this->username_prefix = OW::getConfig()->getValue("frmmobileaccount","username_prefix")!=null ? OW::getConfig()->getValue("frmmobileaccount","username_prefix") :'shub_user_';
        $this->email_postfix  = OW::getConfig()->getValue("frmmobileaccount","email_postfix")!=null ? OW::getConfig()->getValue("frmmobileaccount","email_postfix") : '@shub.frmcenter.ir';
    }

    /***
     * @param $mobileNumber
     * @return BOL_User|bool|null
     */
    public function checkLoginMobile($mobileNumber){
        $user = null;
        if($mobileNumber == '' || !is_numeric($mobileNumber)){
            return $user;
        }
        $user = FRMSMS_BOL_Service::getInstance()->findUserByQuestionsMobile($mobileNumber);
        return $user;
    }


    public function isEmailSystematic($email)
    {
        $adminMode = OW::getUser()->isAuthorized('base','edit_user_profile');
        if(strpos($email, $this->email_postfix)!==false && !$adminMode)
        {
            return true;
        }
        return false;
    }
    /**
     * @param OW_Event $event
     */
    public function checkEmailIsSystematic(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['fieldName']) || $params['fieldName']!='email')
        {
            return;
        }
        $email = $params['value'];

        $data['forceNull']=$this->isEmailSystematic($email);

        $event->setData($data);
    }

    /**
     * @param OW_Event $event
     */
    public function checkEmailIsSystematicForEdit(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['questionData']) )
        {
            return;
        }
        $questionData = $params['questionData'];
        foreach ($questionData as $key=>$value) {
            if ($key='email' && strpos($value, $this->email_postfix) !== false) {
                $questionData['email'] = null;
                break;
            }
        }
        $data['questionData']=$questionData;
        $event->setData($data);
    }


    public function processCodeForm($mobileNumber, $username = null, $email = null, $code = null){
        if ( OW::getRequest()->isPost() )
        {
            $form = $this->getCodeForm($mobileNumber, $username, $email, $code);
            $user = null;
            $validCode = false;
            $limit = false;
            if ( !$form->isValid($_POST) )
            {
                $valid = false;
            }else{
                $data = $form->getValues();
                $mobileCode = trim($data['mobile_code']);
                $mobileNumber = trim($mobileNumber);

                $eventPhoneCheck = new OW_Event('frmsms.phone_number_check', array('number' => $mobileNumber));
                OW_EventManager::getInstance()->trigger($eventPhoneCheck);
                $eventPhoneCheckData = $eventPhoneCheck->getData();

                if (!isset($eventPhoneCheckData)) {
                    $this->handleBruteForce();
                    OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'wrong_code'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.code.mobile_number.username', array('mobileNumber' => $mobileNumber, 'username' => $username, 'code' => $code)));
                } else if (isset($eventPhoneCheckData['userPhone_notIn_ValidList'])) {
                    $this->handleBruteForce();
                    OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'wrong_mobile'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.code', array('mobileNumber' => $mobileNumber,'code' => $code)));
                }

                $verifyCodeEvent = new OW_Event('frmsms.verify_code_event', array('mobileNumber' => $mobileNumber, 'code' => $mobileCode));
                OW_EventManager::getInstance()->trigger($verifyCodeEvent);
                $verifyCodeEventData = $verifyCodeEvent->getData();
                if (isset($verifyCodeEventData['valid']) && $verifyCodeEventData['valid']) {
                    $validCode = true;
                }
                else if(isset($verifyCodeEventData['valid']) && !$verifyCodeEventData['valid'])
                {
                    $this->handleBruteForce();
                    OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'wrong_code'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.code', array('mobileNumber' => $mobileNumber)));
                }
                if (isset($verifyCodeEventData['limit']) && $verifyCodeEventData['limit']) {
                    $limit = true;
                }
                if ($validCode) {
                    $userId = null;
                    if (!isset($eventPhoneCheckData['user_id'])) {
                        // new user
                        $verifyCodeEventNonUser = new OW_Event('frmsms.verify_code_event_non_user', array('mobileNumber' => $mobileNumber));
                        OW_EventManager::getInstance()->trigger($verifyCodeEventNonUser);
                        $verifyCodeEventNonUserData = $verifyCodeEventNonUser->getData();
                        if (isset($verifyCodeEventNonUserData['user_id'])) {
                            $userId = $verifyCodeEventNonUserData['user_id'];
                        }
                        if ($userId != null) {
                            $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $userId, 'method' => 'service', 'mobileNumber'=>$mobileNumber));
                            OW::getEventManager()->trigger($event);
                            $usersImportEvent = OW::getEventManager()->trigger(new OW_Event('on.users.import.register',['mobile'=>$mobileNumber]));
                            $adminVerified= isset($usersImportEvent->getData()['verified']) ? (boolean)$usersImportEvent->getData()['verified'] : false;
                            if($adminVerified && !BOL_UserService::getInstance()->isApproved($userId))
                            {
                                BOL_UserService::getInstance()->approve($userId);
                            }

                        }
                    } else {
                        $userId = $eventPhoneCheckData['user_id'];
                    }
                    if ($userId != null) {
                        $remember=false;
                        if(isset($_GET['remember']) && $_GET['remember']==true)
                        {
                            $remember=true;
                        }
                        OW_User::getInstance()->login($userId, true, $remember);
                        return $this->processUserLogin();
                    }else{
                        OW::getFeedback()->error(OW::getLanguage()->text('base', 'join_not_valid_invite_code'));
                        OW::getApplication()->redirect(OW_URL_HOME);
                    }
                }

            }
        }
    }

    public function processUserLogin(){
        OW::getFeedback()->info(OW::getLanguage()->text('base', 'auth_success_message_not_ajax'));
        OW::getApplication()->redirect(OW_URL_HOME);
    }

    public function emailIsMandatory(){
        if(OW::getConfig()->configExists('frmmobileaccount', 'mandatory_email') &&
            OW::getConfig()->getValue('frmmobileaccount', 'mandatory_email') == true){
            return true;
        }

        return false;
    }

    public function handleBruteForce(){
        $event = new OW_Event('base.bot_detected', array('isBot' => false));
        OW::getEventManager()->trigger($event);
    }

    public function validateJoinForm($inputData){
        $this->handleBruteForce();
        $form = $this->getJoinForm();
        $user = null;
        $mobileNumber = null;
        $username = null;
        $email = null;
        if ( !$form->isValid($inputData) )
        {
            $errors = $form->getErrors();
            $errorString = "";
            foreach ($errors as $error){
                if(isset($error[0])){
                    $errorString = $error[0];
                }
            }
            OW::getFeedback()->error($errorString);
            $valid = false;
        }else{
            $data = $form->getValues();
            $mobileNumber = $data['mobile_number'];
            $username = $data['username'];
            if(isset($data['email'])){
                $email = $data['email'];
            }
            $findUserByUsername = BOL_UserService::getInstance()->findByUsername($username);
            if($findUserByUsername != null){
                $valid = false;
                OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'exist_username'));
            }else{
                $user = $this->checkLoginMobile($mobileNumber);
                if($user != false && $user != null){
                    $valid = false;
                    OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'exist_mobile'));
                }else{
                    if($email == null){
                        if($this->emailIsMandatory()){
                            $valid = false;
                            OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'forgot_password_cap_label'));
                        }else{
                            $valid = true;
                        }
                    }else {
                        $findUserByEmail = BOL_UserService::getInstance()->findByEmail($email);
                        if ($findUserByEmail != null) {
                            $valid = false;
                            OW::getFeedback()->error(OW::getLanguage()->text('base', 'join_error_email_already_exist'));
                        }else{
                            $valid = true;
                        }
                    }
                }
            }
        }

        return array('valid' => $valid, 'mobile_number' => $mobileNumber, 'username' => $username, 'user' => $user, 'email' => $email);
    }

    public function processJoinForm($code = null){
        if ( OW::getRequest()->isPost() )
        {
            $result = $this->validateJoinForm($_POST);
            $valid = $result['valid'];
            $mobileNumber = $result['mobile_number'];
            $username = $result['username'];
            $email = $result['email'];
            if($valid){
                FRMSMS_BOL_Service::getInstance()->renewUserToken(null, $mobileNumber);
                if($code != null){
                    if($email == null){
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.code.mobile_number.username', array('mobileNumber' => $mobileNumber, 'username' => $username, 'code' => $code)));
                    }else{
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.code.mobile_number.username.email', array('mobileNumber' => $mobileNumber, 'username' => $username, 'email' => $email, 'code' => $code)));
                    }
                }else{
                    if($email == null){
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.mobile_number.username', array('mobileNumber' => $mobileNumber, 'username' => $username)));
                    }else{
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.mobile_number.username.email', array('mobileNumber' => $mobileNumber, 'username' => $username, 'email' => $email)));
                    }
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmmobileaccount', 'create_successfully'));
            }else{
                if($code == null){
                    if(isset($_POST['mobile_number']) && isset($_POST['username'])){
                        if(isset($_POST['email'])){
                            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.join.username.mobile_number.email', array('username' => urlencode($_POST['username']), 'mobile_number' => urlencode($_POST['mobile_number']), 'email' => urlencode($_POST['email']))));
                        }else{
                            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.join.username.mobile_number', array('username' => urlencode($_POST['username']), 'mobile_number' => urlencode($_POST['mobile_number']))));
                        }
                    }else{
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.join'));
                    }
                }else{
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.join.code', array('code' => $code)));
                }
            }
        }
    }

    /**
     * @param $username
     * @param $mobileNumber
     * @param $email
     * @param $code
     * @param bool $afterJoin
     * @param bool $realName
     * @return BOL_User
     */
    public function processCreateUser($username, $mobileNumber, $email, $code, $realName=false) {
        if (count(BOL_QuestionService::getInstance()->findAllAccountTypes())==1) {
            $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        }else{
            $accountType =  '';
        }
        $password = self::PASSWORD;
        if($email == null){
            $email = $this->generateEmail();
        }
       $this->createUser($username, $email, $password,$accountType,'c0de',$realName);
        $user = BOL_UserService::getInstance()->findByEmail($email);
        BOL_QuestionService::getInstance()->saveQuestionsData(array('field_mobile'=> $mobileNumber), $user->getId());
        $user->emailVerify = true;
        BOL_UserService::getInstance()->saveOrUpdate($user);
        $params = array('not_login' => true);
        if($code != null) {
            BOL_UserService::getInstance()->deleteInvitationCode($code);
            $params['code'] = $code;
        }
        return $user;
    }

    public function createUser($username, $email, $password, $accountType = null, $securityCode = null,$realName = true)
    {
        $user = BOL_UserService::getInstance()->createUser($username, $password, $email, $accountType, true);
        $questionService = BOL_QuestionService::getInstance();
        $data = array();
        $data['username'] = $username;
        $data['email'] = $email;
        if($realName) {
            $data['realname'] = $username;
        }
        $questionService->saveQuestionsData($data, $user->getId());

        if(isset($securityCode)){
            BOL_QuestionService::getInstance()->saveQuestionsData(array('form_name'=>'requiredQuestionsForm', 'securityCode' => $securityCode), $user->getId());
        }

    }

    public function generateEmail(){
        $index = 100;
        while($index > 0){
            $index--;
            $email = $this->randomString(16) . $this->email_postfix;
            $user = BOL_UserService::getInstance()->findByEmail($email);
            if($user == null){
                return $email;
            }
        }

        return null;
    }

    /***
     * @param int $length
     * @return string
     */
    public function generatePassword($length = 4){
        $str = $this->randomString($length);
        $intRand = rand(1000, 10000);
        return $str.$intRand;
    }

    public function randomString($length = 8) {
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $letter = chr(rand(97,122));
        }
        return $str;
    }

    public function processLoginFormUsingUsernameAndPassword(){
        $form = $this->getLoginUsernamePasswordForm();
        OW::getEventManager()->trigger(new OW_Event("frmmobileaccount.before_sign_in_render", array('form' => $form)));
        $user = null;
        $formErrors = null;
        if ( !$form->isValid($_POST) )
        {
            $valid = false;
            $formErrors = $form->getErrors();
        }else{
            $data = $form->getValues();
            $username = $data['username'];
            $username = trim($username);
            $password = $data['password'];
            $result = OW::getUser()->authenticate(new BASE_CLASS_StandardAuth($username, $password));
            if ( $result->isValid() )
            {
                $valid = true;
                if(isset($data['remember']) && ($data['remember']=='on' || $data['remember']== true))
                {
                    BOL_UserService::getInstance()->setLoginCookie(null, OW::getUser()->getId());
                }
            } else{
                $valid = false;
            }
        }

        return array('valid'=>$valid, 'formErrors'=>$formErrors);
    }

    public function processLoginFormUsingVerificationCode(){
        $valid = true;
        $form = $this->getLoginSmsForm();
        $user = null;
        if ( !$form->isValid($_POST) )
        {
            $valid = false;
        }else{
            $data = $form->getValues();
            $mobileNumber = $data['mobile_number'];
            $request['type']='send_verification_code_to_mobile';
            $request['mobileNumber'] = $mobileNumber;
            $message_event = OW::getEventManager()->trigger(new OW_Event("frmsms.check_received_message", array('data' => $request)));
            $data = $message_event->getData();
        }

        return $valid;
    }

    public function processLoginForm(){
        if ( OW::getRequest()->isPost() )
        {
            if(isset($_POST['username'])){
                $result = $this->processLoginFormUsingUsernameAndPassword();
                $valid = $result['valid'];
                $formErrors = $result['formErrors'];
                if($valid){
                    OW::getFeedback()->info(OW::getLanguage()->text('base', 'auth_success_message_not_ajax'));
                    OW::getApplication()->redirect(OW_URL_HOME);
                }else{
                    $this->handleBruteForce();
                    if(isset($formErrors) && isset($formErrors['captchaField'])){
                        OW::getFeedback()->error(OW::getLanguage()->text('base', 'form_validator_captcha_error_message'));
                    }else{
                        OW::getFeedback()->error(OW::getLanguage()->text('base', 'auth_identity_not_found_error_message'));
                    }
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login.username', array('username' => urlencode($_POST['username']))));
                }
            }else if(isset($_POST['mobile_number'])){
                $frmsmsEvent = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.check.request.time.interval', ['mobileNumber' => $_POST['mobile_number']]));
                if (isset($frmsmsEvent->getData()['validTimeInterval']) && !$frmsmsEvent->getData()['validTimeInterval']) {
                    // sms already sent
                    OW::getFeedback()->error($frmsmsEvent->getData()['errorMessage']);
                } else {
                    //send new token
                    $valid = $this->processLoginFormUsingVerificationCode();
                    if ($valid) {
                        OW::getFeedback()->info(OW::getLanguage()->text('frmmobileaccount', 'sent_code'));
                    } else {
                        $this->handleBruteForce();
                        OW::getFeedback()->error(OW::getLanguage()->text('frmmobileaccount', 'wrong_mobile'));
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login.mobile_number', array('mobile_number' => urlencode($_POST['mobile_number']))));
                    }
                }
                $remember = (isset($_POST['remember']) && ($_POST['remember'] == 'on' || $_POST['remember'] == true));
                $url = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmmobileaccount.code', array('mobileNumber' => $_POST['mobile_number'])), array('remember' => $remember));
                OW::getApplication()->redirect($url);
            }

            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login'));
        }
    }

    public function getLoginSmsForm($mobileNumberPosted = null){
        $form = new Form('login_mobile_account_verification_code');

        $mobileField = new TextField('mobile_number');
        //$mobileField->addAttribute('required', "");
        $mobileField->addAttribute('autocomplete', "off");
        $mobileField->setLabel(ow::getLanguage()->text('frmmobileaccount', 'mobile_number'));
        $mobileField->setRequired();
        $mobileField->addValidator(new AccountMobileValidator());
        if($mobileNumberPosted != null){
            $mobileField->setValue($mobileNumberPosted);
        }
        $mobileField->addValidator(new AccountMobileIsInValidListValidator());
        $form->addElement($mobileField);

        $remeberMe = new CheckboxField('remember');
        $remeberMe->setLabel(OW::getLanguage()->text('base', 'sign_in_remember_me_label'));
        $remeberMe->setValue(true);
        $form->addElement($remeberMe);

        $element = new Submit('submit');
        $element->setValue(ow::getLanguage()->text('frmmobileaccount', 'login_label'));
        $form->addElement($element);

        return $form;
    }

    public function getLoginUsernamePasswordForm($usernamePosted = null){
        $form = new Form('login_mobile_account_username_password');

        $field = new TextField('username');
        //$field->addAttribute('required', "");
        $field->addAttribute('autocomplete', "off");
        $field->setRequired();
        $field->setLabel(ow::getLanguage()->text('base', 'component_sign_in_login_invitation'));
        if($usernamePosted != null){
            $field->setValue($usernamePosted);
        }
        $form->addElement($field);

        $field = new PasswordField('password');
        $field->addAttribute('autocomplete', "off");
        $field->setRequired();
        $field->setLabel(ow::getLanguage()->text('base', 'component_sign_in_password_invitation'));
        $form->addElement($field);

        $remeberMe = new CheckboxField('remember');
        $remeberMe->setLabel(OW::getLanguage()->text('base', 'sign_in_remember_me_label'));
        $remeberMe->setValue(true);
        $form->addElement($remeberMe);

        $element = new Submit('submit');
        $element->setValue(ow::getLanguage()->text('frmmobileaccount', 'login_label'));
        $form->addElement($element);

        return $form;
    }

    public function getJoinForm($usernamePosted = null, $mobileNumberPosted = null, $emailPosted = null){
        $form = new Form('base_sign_in login_mobile_account');

        $mobileField = new TextField('mobile_number');
        $mobileField->setLabel(ow::getLanguage()->text('frmmobileaccount', 'mobile_number') . ' (... .. ... ..09) ');
        $mobileField->addValidator(new AccountMobileValidator());
        $mobileField->addValidator(new AccountMobileIsInValidListValidator());
        $mobileField->addValidator(new AccountMobileExistenceValidator());
        $mobileField->setRequired();
        //$mobileField->addAttribute('required', "");
        $mobileField->addAttribute('autocomplete', "off");
        if($mobileNumberPosted != null){
            $mobileField->setValue($mobileNumberPosted);
        }
        $form->addElement($mobileField);

        $usernameField = new TextField('username');
        $usernameField->setLabel(ow::getLanguage()->text('base', 'questions_question_username_label'));
        //$usernameField->addAttribute('required', "");
        $usernameField->addValidator(new FRMMOBILEACCOUNT_CLASS_JoinUsernameValidator());
        $usernameField->setRequired();
        $usernameField->addAttribute('autocomplete', "off");
        if($usernamePosted != null){
            $usernameField->setValue($usernamePosted);
        }
        $form->addElement($usernameField);

        $mandatoryString = ow::getLanguage()->text('frmmobileaccount', 'mandatory');
        if(!$this->emailIsMandatory()){
            $mandatoryString = ow::getLanguage()->text('frmmobileaccount', 'optional');
        }
        $emailField = new TextField('email');
        $emailField->setLabel(ow::getLanguage()->text('base', 'ow_ic_mail') . ' - ' . $mandatoryString);
        if($this->emailIsMandatory()){
            //$emailField->addAttribute('required', "");
            $emailField->setRequired();
        }
        $emailField->addValidator(new EmailValidator());
        $emailField->addValidator(new FRMMOBILEACCOUNT_CLASS_JoinEmailValidator());
        $emailField->addAttribute('autocomplete', "off");
        if($emailPosted != null){
            $emailField->setValue($emailPosted);
        }
        $form->addElement($emailField);

        $element = new Submit('submit');
        $element->setValue(ow::getLanguage()->text('frmmobileaccount', 'join_label'));
        $form->addElement($element);

        return $form;
    }

    public function getCodeForm($mobileNumber = null, $username = null, $email = null, $code = null){
        $form = new Form('code_mobile_account');

        $codeField = new TextField('mobile_code');
        $codeField->setLabel(ow::getLanguage()->text('frmmobileaccount', 'mobile_code'));
        //$codeField->addAttribute('required', "");
        $codeField->setRequired();
        $codeField->addAttribute('autocomplete', "off");
        $codeField->addValidator(new IntValidator());
        $form->addElement($codeField);

        if($username != null){
            $usernameField = new HiddenField('username');
            $usernameField->setValue($username);
            $form->addElement($usernameField);
        }

        if($mobileNumber != null){
            $mobileField = new HiddenField('mobile_number');
            $mobileField->setValue($mobileNumber);
            $form->addElement($mobileField);
        }

        if($email != null){
            $emailField = new HiddenField('email');
            $emailField->setValue($email);
            $form->addElement($emailField);
        }

        if($code != null){
            $codeField = new HiddenField('code');
            $codeField->setValue($code);
            $form->addElement($codeField);
        }

        $element = new Submit('submit');
        $element->setValue(ow::getLanguage()->text('frmmobileaccount', 'check_code'));
        $form->addElement($element);

        return $form;
    }

    public function resendCode($mobileNumber){
        $valid = false;
        $FRMSMSService = FRMSMS_BOL_Service::getInstance();
        $userId=null;
        $user = $this->checkLoginMobile($mobileNumber);
        if($user != false && $user != null)
        {
            $userId=$user->getId();
        }
        $token = null;
        $token = $FRMSMSService->getTokenNumber($mobileNumber);
        if(isset($token) && $token->try > $FRMSMSService->getMaxTokenPossibleTry()) {
            $valid = false;
            $message = OW::getLanguage()->text('frmmobileaccount', 'sms_max_try_met');
            exit(json_encode(array('valid' => $valid, 'message' => $message)));
        }
        if(isset($mobileNumber)) {
            $FRMSMSService->renewUserToken($userId, $mobileNumber);
            $valid = true;
            $message = OW::getLanguage()->text('frmmobileaccount', 'sms_code_resend_message');
            exit(json_encode(array('valid' => $valid, 'message' => $message)));
        }else{
            $failRedirect = OW::getRouter()->urlForRoute('frmmobileaccount.login');
            exit(json_encode(array('valid' => $valid,'failRedirect'=>$failRedirect)));
        }
    }

    public function checkUrlIsMobileAccount(){
        if(!isset($_SERVER['REQUEST_URI'])){
            return false;
        }

        if (strpos($_SERVER['REQUEST_URI'], '/mobile/account/login') !== false ||
            strpos($_SERVER['REQUEST_URI'], '/mobile/account/resend') !== false ||
            strpos($_SERVER['REQUEST_URI'], '/mobile/account/code') !== false) {
            return true;
        }
        return false;
    }

    public function checkPluginSmsIsActive(){
        $active = FRMSecurityProvider::checkPluginActive('frmsms', true);
        return $active;
    }

    public function onPluginsInit(){
        if(!$this->checkUrlIsMobileAccount() || !$this->checkChangeLoginUrl()){
            return;
        }

        if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && !OW::getUser()->isAdmin() && !BOL_UserService::getInstance()->isApproved())
        {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'login'
            ));
            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'code'
            ));
        }
    }

    public function onBeforePostRequestFailForCSRF(OW_Event $event){
        $url = $_SERVER['REQUEST_SCHEME'] . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $passPaths = array();
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobileaccount.login');
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobileaccount.code', array('mobileNumber' => ''));
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobileaccount.resend', array('mobileNumber' => ''));

        foreach ($passPaths as $passPath){
            if(strpos($url, $passPath)==0){
                $event->setData(array('pass' => true));
                return;
            }
        }
    }

    public function onBeforeMobileValidationRedirect(OW_Event $event)
    {
        if($this->checkUrlIsMobileAccount()){
            $event->setData(array('not_redirect' => true));
        }
    }

    public function checkChangeLoginUrl(){
        if(!$this->checkPluginSmsIsActive()){
            return false;
        }
        $configTypeValue = OW::getConfig()->getValue('frmmobileaccount', 'login_type_version');
        if($configTypeValue == self::BOTH_VERSION){
            return true;
        }else{
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                if($configTypeValue == self::MOBILE_VERSION){
                    return true;
                }
            }else{
                if($configTypeValue == self::DESKTOP_VERSION){
                    return true;
                }
            }
        }

        return false;
    }

    public function onAddMaintenanceModeExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'code'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'code'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'join'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'join'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'code'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'resendCode'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'code'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'resendCode'));
    }

    public function checkChangeJoinUrl(){
        if(!$this->checkPluginSmsIsActive()){
            return false;
        }
        $configTypeValue = OW::getConfig()->getValue('frmmobileaccount', 'join_type_version');
        if($configTypeValue == self::BOTH_VERSION){
            return true;
        }else{
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                if($configTypeValue == self::MOBILE_VERSION){
                    return true;
                }
            }else{
                if($configTypeValue == self::DESKTOP_VERSION){
                    return true;
                }
            }
        }

        return false;
    }

    public function changeSignInButton (OW_Event $event){
        if($this->checkChangeLoginUrl()) {
            $item = new BASE_CMP_ConsoleButton(OW::getLanguage()->text('base', 'sign_in_submit_label'), OW::getRouter()->urlForRoute('static_sign_in'));
            $event->setData(array('frmmobileAccountSign-in' => $item));
        }
    }


    /**
     * @param $userId
     * @param $mobileNumber
     * @throws Redirect404Exception
     */
    private function validateMobileToken($userId,$mobileNumber){
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)){
            return;
        }

        FRMSMS_BOL_Service::getInstance()->validateMobileToken($userId,$mobileNumber);
    }

    public function createUserAfterVerifyCode(OW_Event $event) {
        $params = $event->getParams();
        if (!isset($params['mobileNumber'])) {
            return;
        }
        $usersImportEvent = OW::getEventManager()->trigger(new OW_Event('on.users.import.register',['mobile'=>$params['mobileNumber']]));
        if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS && !isset($params['direct_join'])){

            if(!isset($usersImportEvent->getData()['verified']))
            {
                return;
            }
        }
        $mobileNumber = $params['mobileNumber'];

        $userId = null;
        $realName=false;
/*        $username = $this->generateUsername();*/
        $username = $mobileNumber;
        $email = $this->generateEmailUsingMobile($username);
        $user = $this->processCreateUser($username, $mobileNumber, $email, null, $realName);
        if ($user == null) {
            return;
        }

        $this->validateMobileToken($user->getId(),$mobileNumber);
        $event->setData(array('user_id' => $user->getId()));
    }

    /**
     * @param $username
     * @return string
     */
    public function  generateEmailUsingMobile($username) {
        $email = $username . $this->email_postfix;
        $user = BOL_UserService::getInstance()->findByEmail($email);
        $lastUser = BOL_UserService::getInstance()->findLastUser();
        $nextUserId =  ((int)$lastUser->getId())+1;
        while ($user != null){
            $nextUserId=$nextUserId+1;
            $username =  $this->username_prefix . $nextUserId;
            $email = $username . $this->email_postfix;
            $user = BOL_UserService::getInstance()->findByUsername($username);
        }
        return $email;
    }


    /**
     * @return mixed
     */
    public function  generateUsername() {
        $lastUser = BOL_UserService::getInstance()->findLastUser();
        $nextUserId =  ((int)$lastUser->getId())+1;
        $username = $this->username_prefix . $nextUserId;
        $user = BOL_UserService::getInstance()->findByUsername($username);
        while ($user != null){
            $nextUserId=$nextUserId+1;
            $username = $this->username_prefix . $nextUserId;
            $user = BOL_UserService::getInstance()->findByUsername($username);
        }
        return $username;
    }

    public function catchAllRequestsExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'login'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'code'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'join'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_CTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'resendCode'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_MCTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'login'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_MCTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'code'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_MCTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'join'
        ));
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILEACCOUNT_MCTRL_Account',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'resendCode'
        ));
    }

    public function autoLoginCookieUpdate(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['day'])){
            $day = $params['day'];
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                $day = OW::getConfig()->getValue('frmmobileaccount', 'expired_cookie');
            }
            $event->setData(array('day' => $day));
        }
    }

    public function changeSignInPage(OW_Event $event){
        $isUrlSign = false;
        if (strpos(OW::getRequest()->getRequestUri(), 'sign-in' ) !== false)  {
            $isUrlSign = true;
        }
        if(!OW::getUser()->isAuthenticated() && $this->checkChangeLoginUrl() && $isUrlSign) {
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login'));
            $event->setData(array('handled' => true));
        }
    }

    public function redirectGuestToNewSigninPage(OW_Event $event)
    {
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return;
        }

        $baseConfigs = OW::getConfig()->getValues('base');
        //members only
        if (!OW::getUser()->isAuthenticated()) {
            $urlLogin = OW::getRouter()->urlForRoute('frmmobileaccount.login');
            $userRequestUri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if (isset($userRequestUri) && !empty($userRequestUri) && strpos($urlLogin, $userRequestUri) != false) {
                return;
            }
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login'));
        }
    }

    public function changeJoinPage(OW_Event $event){
        if(!OW::getUser()->isAuthenticated() && $this->checkChangeJoinUrl()) {
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobileaccount.login'));
            $event->setData(array('handled' => true));
        }
    }

    public function addStaticFilesToDocument(){
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmmobileaccount')->getStaticJsUrl() . 'frmmobileaccount.js');
        $jsUrl = OW::getPluginManager()->getPlugin('frmmobileaccount')->getStaticJsUrl() . "jquery.steps.js";
        OW::getDocument()->addScript($jsUrl);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmmobileaccount')->getStaticCssUrl() . 'frmmobileaccount.css');
        OW::getLanguage()->addKeyForJs('frmmobileaccount', 'resending_token');
        OW::getLanguage()->addKeyForJs('frmmobileaccount', 'resend_token_successfully');
        OW::getLanguage()->addKeyForJs('frmmobileaccount', 'try_again');
    }

    public function addStylesheetMobile(){
        if(!OW::getUser()->isAuthenticated()) {
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
            if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
                if (strpos($_SERVER['REQUEST_URI'], '/mobile/account/login') !== false ||
                    strpos($_SERVER['REQUEST_URI'], '/mobile/account/resend') !== false ||
                    strpos($_SERVER['REQUEST_URI'], '/mobile/account/code') !== false) {
                    OW::getDocument()->addStyleDeclaration('a#owm_header_right_btn {display: none;}');
                }
            }
        }
    }

    public function onBeforeFormSigninRender(OW_Event $event)
    {

        if (!$this->checkChangeLoginUrl()) {
            return;
        }
        $form = new Form('frmmobileaccount_signin_from');
        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
        $form->addElement($submit);
        $form->setAction(OW::getRouter()->urlForRoute('static_sign_in').'?back-uri='.OW::getRequest()->getRequestUri());
        $event->setData(array(
            'frmmobileaccount_signin_from' => $form
        ));
    }

    public function onImportUsersForSubscription(OW_Event $event){
        $params = $event->getParams();
        $ignoreData = array();
        $adminVerified=false;
        if(isset( $params['adminVerified']) && $params['adminVerified']=='on')
        {
            $adminVerified=true;
        }
        if(isset($params['lines'])){
            $lines = $params['lines'];
            $count = 0;
            $usersData = array();
            $allMobileNumbers = FRMUSERSIMPORT_BOL_AdminVerifiedDao::getInstance()->getAllMobileNumbers();
            foreach($lines as $line){
                if($count != 0){
                    $item = preg_split('/[\t]/', $line);
                    $item = $this->removeEmptyItemsFromArray($item);
                    if(sizeof($item) > 1){
                        if (sizeof($item) > 3) {
                            // if triggering this event outside frmuserimport, handle below line
                            $ignoreData[] = OW::getLanguage()->text('frmusersimport', 'line_error', array('line' => ($count + 1)));
                        } else{
                            if(sizeof($item) == 3)
                            {
                                $mobile = $item[2];
                            }else{
                                $mobile = $item[1];
                            }
                            $email = $item[0];
                            $usersData[$count]['email']=$email;
                            $usersData[$count]['mobile']=$mobile;
                            $usersData[$count]['verified']=$adminVerified;
                            if(UTIL_Validator::isEmailValid($email)) {
                                $this->sendEmail($item[0]);
                            }
                            $name = sizeof($item) == 3 ? $item[1] : $item[0];
                            $site_name = OW::getConfig()->getValue('base', 'site_name');
                            $site_link = OW::getRouter()->getBaseUrl();
                            $text_welcome_name = OW::getLanguage()->text("frmmobileaccount","sms_text_welcome_name_part", array('name' => $name));
                            $text_welcome_invite = OW::getLanguage()->text("frmmobileaccount","sms_text_welcome_invitation_part", array('site' => $site_name));
                            $text_general = OW::getLanguage()->text("frmmobileaccount","sms_text_general_part", array('site' => $site_name));
                            $text_website = OW::getLanguage()->text("frmmobileaccount","sms_text_website_part", array('site' => $site_link));
                            $text = $text_welcome_name . "\n\n " . $text_welcome_invite . "\n " . $text_general . "\n " . $text_website;

                            $eventInvite = OW::getEventManager()->trigger(new OW_Event('frm.before.send.invite', array('text' => $text, 'email' => $item[0])));
                            if(isset($eventInvite->getData()['text'])){
                                $text = $eventInvite->getData()['text'];
                            }
                            if(isset($allMobileNumbers)){
                                if(
                                    !in_array($item[2],$allMobileNumbers)
                                    && !in_array($item[1],$allMobileNumbers)
                                    && !in_array(str_replace('+98','0', $item[2]),$allMobileNumbers)
                                    && !in_array(str_replace('+98','0', $item[2]),$allMobileNumbers)
                                ){
                                    sizeof($item) == 3 ? $this->sendSms($item[2],$text) : $this->sendSms($item[1],$text);
                                }
                            }else{
                                sizeof($item) == 3 ? $this->sendSms($item[2],$text) : $this->sendSms($item[1],$text);
                            }

                        }
                    }
                }
                $count++;
            }

            OW::getEventManager()->trigger(new OW_Event('store.users.import.data', ['usersData' => $usersData]));

            $event->setData(array(
                'ignoreData' => $ignoreData
            ));
        }
    }

    /***
     * @param $number
     * @param $text
     * @throws Redirect404Exception
     */
    public function sendSms($number, $text){
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true) || $number == null || $number == ""){
            return;
        }
        FRMSMS_BOL_Service::getInstance()->sendSMSWithCron($number, $text);
    }

    public function sendEmail($email)
    {
        BOL_UserService::getInstance()->sendAdminInvitation($email);
        OW::getEventManager()->trigger(new OW_Event('frminvite.on.send.invitation', array('senderId' => OW::getUser()->getId(), 'invitedEmail' => $email)));
    }

    /***
     * @return bool
     */
    public function checkSMSPluginActive(){
        if(FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return true;
        }
        return false;
    }

    /***
     * @param $array
     * @return array
     */
    public function removeEmptyItemsFromArray($array){
        $newArray = array();
        foreach ($array as $item){
            if($item != ""){
                $newArray[] = $item;
            }
        }

        return $newArray;
    }

    /**
     * @param OW_Event $event
     */
    public function onBeforeCreateUser(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['username']) || !isset($params['email']) )
        {
            return;
        }
        $username_prefix = OW::getConfig()->getValue("frmmobileaccount","username_prefix");
        $email_postfix  = OW::getConfig()->getValue("frmmobileaccount","email_postfix");
        if(strpos($params['email'], $email_postfix)!==false && strpos($params['username'], $username_prefix)!==false)
        {
            $data['ignoreHashPassword'] = true;
        }
        $event->setData($data);
    }

    /**
     * @param OW_Event $event
     */
    public function passwordChangeIntervalCriteria(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        $user = OW::getUser()->getUserObject();
        if($user->password==self::PASSWORD)
        {
            $data['addToWhiteList'] = true;
        }
        $event->setData($data);
    }

    /**
     * @param BASE_CLASS_EventCollector $e
     */
    public function onNotifyActions(BASE_CLASS_EventCollector $e)
    {
        $e->add(array(
            'section' => 'frmmobileaccount',
            'sectionLabel' => OW::getLanguage()->text('frmmobileaccount', 'notification_section_label'),
            'action' => 'register',
            'description' => OW::getLanguage()->text('frmmobileaccount', 'email_notifications_setting_user_register'),
            'sectionIcon' => 'ow_ic_write',
            'selected' => true
        ));
    }

    public function checkEditProfileMandatoryAction(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['userId']))
        {
            return;
        }
        $user = BOL_UserService::getInstance()->findUserById($params['userId']);
        if ($this->isEmailSystematic($user->email))
        {
            OW::getEventManager()->trigger(new OW_Event('base.mandatory_user_approve.edit', array('userId' => $params['userId'],'newUser'=>true)));
        }
    }

    public function sendInfoAfterUserRegister(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['userId']))
        {
            return;
        }
        $eventMobileNumber = OW::getEventManager()->trigger(new OW_Event('frmsms.get.user.mobile.number', ['userId' => $params['userId']]));
        if(isset($eventMobileNumber->getData()['mobileNumber']))
        {
            $username = $eventMobileNumber->getData()['mobileNumber'];
            $data['username'] = $username;
            $event->setData($data);
        }
    }

    public function onAddPasswordProtectedExceptions( BASE_CLASS_EventCollector $event ) {
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_CTRL_Account', 'action' => 'code'));

        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'login'));
        $event->add(array('controller' => 'FRMMOBILEACCOUNT_MCTRL_Account', 'action' => 'code'));
    }

    public function isSystematicCreatedBySystem(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['email']))
        {
            return;
        }

        $isEmailCreatedBySystem = false;
        $email_postfix  = OW::getConfig()->getValue("frmmobileaccount","email_postfix");
        if(!empty($email_postfix) && strpos($params['email'], $email_postfix)!==false)
        {
            $isEmailCreatedBySystem =  true;
        }
        $event->setData(array('isEmailCreatedBySystem'=>$isEmailCreatedBySystem));
    }

}

class AccountMobileValidator extends OW_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'form_validator_mobile_invalid_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        if(FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return FRMSMS_BOL_Service::getInstance()->isMobileValueValid($value);
        }
        return true;
    }
}

class AccountMobileExistenceValidator extends OW_Validator
{
    protected $jsObjectName = null;
    protected $number = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'form_validator_mobile_exists_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setNumber($number){
        $this->number = $number;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        if($this->number !== null){
            if($this->number === $value){
                return true;
            }
        }
        if(FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return !FRMSMS_BOL_Service::getInstance()->checkQuestionsMobileExist($value);
        }
        return true;
    }
}

class AccountMobileIsInValidListValidator extends OW_Validator
{
    protected $jsObjectName = null;
    protected $number = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'number_is_not_valid_list');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setNumber($number){
        $this->number = $number;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        if($this->number !== null){
            if($this->number === $value){
                return true;
            }
        }
        if(FRMSecurityProvider::checkPluginActive('frmsms', true)) {
            return FRMSMS_BOL_Service::getInstance()->checkIsInValidList($value);
        }
        return true;
    }
}

class FRMMOBILEACCOUNT_CLASS_JoinEmailValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();
        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));

            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));

            return false;
        }

        return true;
    }

}

class FRMMOBILEACCOUNT_CLASS_JoinUsernameValidator extends OW_Validator
{

    /**
     * BASE_CLASS_JoinUsernameValidator constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();
        if ( !UTIL_Validator::isUserNameValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_not_valid'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isRestrictedUsername($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_restricted'));
            return false;
        }

        if ( OW::getConfig()->configExists('base', 'username_chars_min') )
        {
            $config = OW::getConfig();
            $usernameMin = $config->configExists('base', 'username_chars_min')?$config->getValue('base', 'username_chars_min'):1;
            $usernameMax = $config->configExists('base', 'username_chars_max')?$config->getValue('base', 'username_chars_max'):32;
            if (strlen($value)<$usernameMin || strlen($value)>$usernameMax) {
                $this->setErrorMessage($language->text('base', 'join_error_username_length_not_valied', ['min'=>$usernameMin, 'max'=>$usernameMax]));
                return false;
            }
        }

        return true;
    }
}