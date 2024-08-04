<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmusersimport.bol
 * @since 1.0
 */
class FRMUSERSIMPORT_BOL_Service
{
    private static $classInstance;
    public static $NOTIFICATION_TYPE_EMAIL = 'email';
    public static $NOTIFICATION_TYPE_MOBILE = 'mobile';
    public static $NOTIFICATION_TYPE_ALL = 'all';
    public static $USER_IMPORT_FORM_NAME = 'users_import';
    public static $USER_IMPORT_SETTING_FORM_NAME = 'users_import_setting';

    private $adminVerifiedDao;
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
        $this->adminVerifiedDao = FRMUSERSIMPORT_BOL_AdminVerifiedDao::getInstance();
    }

    /***
     * @return array|null
     */
    public function processFileImported(){
        $language = OW::getLanguage();
        $ignoreData = array();
        if(!((int)$_FILES['file']['error'] !== 0 || !is_uploaded_file($_FILES['file']['tmp_name']))){
            if(UTIL_File::getExtension($_FILES['file']['name']) != 'txt'){
                OW::getFeedback()->error(OW::getLanguage()->text('frmusersimport', 'error_import_extension'));
            }

            $path = $_FILES['file']['tmp_name'];
            $file = fopen($path, 'r');
            $data = fread($file, filesize($path));
            fclose($file);

            $lines = preg_split("/\\r\\n|\\r|\\n/", $data);
            if(sizeof($lines) == 0){
                $ignoreData[] = $language->text('frmusersimport', 'line_error', array('line' => 1));
            }

            $adminVerified = isset($_POST['adminVerified'])?$_POST['adminVerified']:null;
            $ignoreAutoUsernameAndPassword = isset($_POST['ignoreAutoUsernameAndPassword'])?$_POST['ignoreAutoUsernameAndPassword']:null;
            $event = OW::getEventManager()->trigger(new OW_Event("frm.on.users.import.subscription", array("lines" => $lines,'adminVerified'=>$adminVerified)));
            if(isset($event->getData()['ignoreData'])){
                return $event->getData()['ignoreData'];
            }
            $count = 0;
            foreach($lines as $line) {
                if($count != 0){
                    $item = preg_split('/[\t]/', $line);
                    $item = $this->removeEmptyItemsFromArray($item);
                    if(sizeof($item) != 1 || $item[0] != ""){
                        if (sizeof($item) == 0) {
                            //empty line
                        } else if (sizeof($item) != 3) {
                            $ignoreData[] = $language->text('frmusersimport', 'line_error', array('line' => ($count + 1)));
                        } else {
                            $errorAddUser = $this->processAddUser($item[0], $item[1], $item[2],$adminVerified,$ignoreAutoUsernameAndPassword);
                            if(is_array($errorAddUser) && sizeof($errorAddUser) > 0) {
                                $ignoreData = array_merge($ignoreData, $errorAddUser);
                            }
                        }
                    }
                }
                $count++;
            }
            return $ignoreData;
        }else{
            OW::getFeedback()->error(OW::getLanguage()->text('frmusersimport', 'file_empty'));
        }

        return $ignoreData;
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
     * @param $email
     * @param $mobile
     * @param $ignoreAutoUsernameAndPassword
     * @return array
     */
    private function getUsrNamePasswordEmail($email, $mobile, $ignoreAutoUsernameAndPassword)
    {
        if($ignoreAutoUsernameAndPassword)
        {
            $username = $email;
            if(isset($mobile)) {
                $password = $mobile;
            }else{
                $password = $email;
            }
            if ($this->isEmailInvalid($email)) {
                $email = $username.'@'.$username.'.com';
                while ($this->isEmailExist($email)) {
                    $email = $username.rand(1, 10000).'@'.rand(1, 10000).'.com';
                }
            }
        }else{
            $username = $this->generateUsernameUsingEmail($email);
            $password = $this->generatePassword();
        }

        return array($username,$password,$email);
    }


    /**
     * @param $email
     * @param $mobile
     * @throws Redirect404Exception
     */
    private function validateMobileAndEmail($email,$mobile)
    {
        $ignoreData = array();
        if ($this->isEmailExist($email)) {
            $ignoreData[] = OW::getLanguage()->text('frmusersimport', 'email_exist', array('email' => $email));
        } else {
            if ($this->isEmailInvalid($email)) {
                $ignoreData[] = OW::getLanguage()->text('frmusersimport', 'email_invalid', array('email' => $email));
            } else {
                if ($this->isMobileExist($mobile)) {
                    $ignoreData[] = OW::getLanguage()->text('frmusersimport', 'mobile_exist', array('mobile' => $mobile));
                }
            }
        }
      $ignoreData;
    }
    /**
     * @param $email
     * @param $name
     * @param $mobile
     * @param bool $adminVerified
     * @param bool $ignoreAutoUsernameAndPassword
     * @return array
     * @throws Redirect404Exception
     */
    public function processAddUser($email, $name, $mobile,$adminVerified=false, $ignoreAutoUsernameAndPassword=false){
        $ignoreData = array();
        $email = trim($email);
        $mobile = trim($mobile);
        $name = trim($name);

        $ignoreData = $this->validateMobileAndEmail($email,$mobile);
        if(!$ignoreAutoUsernameAndPassword && sizeof($ignoreData)>0)
        {
            return $ignoreData;
        }else {

            list($username,$password,$email) = $this->getUsrNamePasswordEmail($email,$mobile,$ignoreAutoUsernameAndPassword);
            $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
            $user = BOL_UserService::getInstance()->findByUsername($username);
            while ($user != null) {
                $username = $username.rand(0, 100000000);
                $user = BOL_UserService::getInstance()->findByUsername($username);
            }

            $user = BOL_UserService::getInstance()->createUser($username, $password, $email, $accountType, true);
            $questionService = BOL_QuestionService::getInstance();
            $data = array();
            $data['username'] = $username;
            $data['email'] = $email;
            $data['realname'] = $name;
            $data['sex'] = "1";
            $data['birthdate'] = "1969/3/21";
            $questionService->saveQuestionsData($data, $user->getId());
            if(!$ignoreAutoUsernameAndPassword) {
                BOL_QuestionService::getInstance()->saveQuestionsData(
                    array('form_name' => 'requiredQuestionsForm', 'field_mobile' => $mobile),
                    $user->getId()
                );
                $this->validateMobileToken($user->getId());
                $this->sendSms($mobile, $this->getMobileDescriptionForSendingAccount($username, $password));
                $this->sendEmailUsingUsernameAndPassword($email, $username, $password);
            }
            $event = new OW_Event(
                OW_EventManager::ON_USER_REGISTER,
                array('userId' => $user->getId(), 'method' => 'import')
            );
            OW::getEventManager()->trigger($event);
            if ($adminVerified && !BOL_UserService::getInstance()->isApproved($user->getId())) {
                BOL_UserService::getInstance()->approve($user->getId());
            }
        }

        return $ignoreData;
    }

    public function getSleepyUsers(){
        if(!$this->checkUserLoginPluginActivate()){
            return null;
        }

        $userTable = OW_DB_PREFIX."base_user";
        $query = "select ".$userTable.".id from ".$userTable." where ".$userTable.".joinStamp = ".$userTable.".activityStamp";
        $result = OW::getDbo()->queryForColumnList($query);
        return $result;
    }

    /***
     * @param $userIdList
     * @return array
     * @throws Redirect404Exception
     */
    public function sendAccountInformationToUsers($userIdList){
        $emails = array();
        foreach ($userIdList as $userId){
            $user = BOL_UserService::getInstance()->findUserById($userId);
            if(strcmp($user->salt,'')==0)
            {
                $salt = md5(UTIL_String::getRandomString(8, 5));
                BOL_UserDao::getInstance()->updateSaltByUserId((int)$user->id, $salt);
            }
            $password = $this->generatePassword();
            BOL_UserService::getInstance()->updatePassword($userId, $password);
            $emails[] = $user->getEmail();
            if(FRMSecurityProvider::checkPluginActive('frmsms', true)) {
                $userMobile = FRMSMS_BOL_Service::getInstance()->getUserQuestionsMobile($userId);
                $this->sendSms($userMobile, $this->getMobileDescriptionForSendingAccount($user->getUsername(), $password));
            }
            $this->sendEmailUsingUsernameAndPassword($user->getEmail(), $user->getUsername(), $password);
        }
        return $emails;
    }

    /***
     * @return array
     */
    public function sendAccountInformationToSleepyUsers(){
        $userIdList = $this->getSleepyUsers();
        $emails = $this->sendAccountInformationToUsers($userIdList);
        return $emails;
    }

    public function sendEmailUsingUsernameAndPassword($email, $username , $password){
        $this->sendMail($email, $this->getEmailSubjectForSendingAccount(), $this->getEmailDescriptionForSendingAccount($username, $password));
    }

    public function getEmailSubjectForSendingAccount(){
        $language = OW::getLanguage();
        return $language->text('frmusersimport', 'email_subject');
    }

    public function getEmailDescriptionForSendingAccount($username, $password){
        $language = OW::getLanguage();
        return $language->text('frmusersimport', 'email_description', array('username' => $username, 'password' => $password));
    }

    public function getMobileDescriptionForSendingAccount($username, $password){
        $language = OW::getLanguage();
        return $language->text('frmusersimport', 'mobile_description', array('username' => $username, 'password' => $password));
    }

    /***
     * @param $email
     * @return string
     */
    public function getSentEmailMessageToAdmin($email){
        return "Email sent for email: ". $email;
    }

    /***
     * @return string
     */
    public function getEmptyEmailSentListMessage(){
        return "There is not email to sent.";
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

    /***
     * @param $userId
     * @throws Redirect404Exception
     */
    public function validateMobileToken($userId){
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)){
            return;
        }

        FRMSMS_BOL_Service::getInstance()->validateMobileToken($userId);
    }

    /***
     * @param $email
     * @return null|string|string[]
     */
    public function generateUsernameUsingEmail($email){
        $parts = explode("@", $email);
        $username = $parts[0];
        $username = preg_replace("/[^A-Za-z0-9 ]/", '', $username);
        if($username == ""){
            $username = rand(1000, 10000) . "";
        }
        return $username;
    }

    /***
     * @param $mobile
     * @return bool
     * @throws Redirect404Exception
     */
    public function isMobileExist($mobile){
        if(!FRMSecurityProvider::checkPluginActive('frmsms', true)){
            return false;
        }
        return FRMSMS_BOL_Service::getInstance()->checkQuestionsMobileExist($mobile);
    }

    /***
     * @param $email
     * @return bool
     */
    public function isEmailExist($email){
        $user = BOL_UserService::getInstance()->findByEmail($email);
        if($user == null){
            return false;
        }
        return true;
    }

    /***
     * @param $email
     * @return bool
     */
    public function isEmailInvalid($email){
        if ( !preg_match(UTIL_Validator::EMAIL_PATTERN, $email) )
        {
            return true;
        }

        return false;
    }

    /**
     * @param $action
     * @return Form
     */
    public function getUsersImportForm($action){
        $form = new Form(self::$USER_IMPORT_FORM_NAME);
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $file = new FileField('file');
        $form->addElement($file);

        $adminVerified = new CheckboxField('adminVerified');
        $form->addElement($adminVerified);

        $ignoreAutoUsernameAndPassword = new CheckboxField('ignoreAutoUsernameAndPassword');
        $form->addElement($ignoreAutoUsernameAndPassword);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmusersimport', 'upload_file_submit_label'));
        $form->addElement($submit);

        return $form;
    }

    /**
     * @return Form
     */
    public function getUsersImportFormSetting(){
        $form = new Form(self::$USER_IMPORT_SETTING_FORM_NAME);
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmusersimport-admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("'.OW::getLanguage()->text("frmusersimport", "saved_successfully").'");}else{OW.error("Parser error");}}');

        $config = OW::getConfig();
        if(!$config->configExists('frmusersimport', 'notification_type')){
            $config->addConfig('frmusersimport', 'notification_type', self::$NOTIFICATION_TYPE_ALL);
        }

        $optionsTypeList = array();
        $optionsTypeList[self::$NOTIFICATION_TYPE_EMAIL] = OW::getLanguage()->text('frmusersimport', 'email_type');
        $optionsTypeList[self::$NOTIFICATION_TYPE_MOBILE] = OW::getLanguage()->text('frmusersimport', 'mobile_type');
        $optionsTypeList[self::$NOTIFICATION_TYPE_ALL] = OW::getLanguage()->text('frmusersimport', 'all_type');

        $type = new Selectbox('type');
        $type->setLabel(OW::getLanguage()->text('frmusersimport', 'notification_type'));
        $type->setValue($config->getValue('frmusersimport', 'notification_type'));
        $type->setOptions($optionsTypeList);
        $type->setHasInvitation(false);
        $type->setRequired(true);
        $form->addElement($type);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmusersimport', 'save_setting_submit_label'));
        $form->addElement($submit);

        return $form;
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
        $config = OW::getConfig();
        if($config->configExists('frmusersimport', 'notification_type')){
            $notificationType = $config->getValue('frmusersimport', 'notification_type');
            if(in_array($notificationType, array(self::$NOTIFICATION_TYPE_ALL, self::$NOTIFICATION_TYPE_MOBILE))) {
                FRMSMS_BOL_Service::getInstance()->sendSMSWithCron($number, $text);
            }
        }
    }

    /***
     * @return bool
     */
    public function checkUserLoginPluginActivate(){
        if(FRMSecurityProvider::checkPluginActive('frmuserlogin', true)) {
            return true;
        }
        return false;
    }

    /***
     * @param $email
     * @param $subject
     * @param $message
     */
    public function sendMail($email, $subject, $message){
        $config = OW::getConfig();
        if($config->configExists('frmusersimport', 'notification_type')){
            $notificationType = $config->getValue('frmusersimport', 'notification_type');
            if(in_array($notificationType, array(self::$NOTIFICATION_TYPE_ALL, self::$NOTIFICATION_TYPE_EMAIL))) {
                try
                {
                    $mail = OW::getMailer()->createMail()
                        ->addRecipientEmail($email)
                        ->setTextContent($message)
                        ->setSender(OW::getConfig()->getValue('base', 'site_email'))
                        ->setHtmlContent($message)
                        ->setSubject($subject);

                    OW::getMailer()->send($mail);
                }
                catch ( Exception $e )
                {
                    //Skip invalid notification
                }
            }
        }
    }

    /***
     * Return admin email
     * @return null|string
     */
    public function getAdminMail(){
        return OW::getConfig()->getValue('base', 'site_email');
    }

    public function storeUsersImportData(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['usersData']))
        {
            return;
        }
        $usersData = $params['usersData'];
        $fullDtoList = array();
        foreach ( $usersData as $userData )
        {
            $adminVerifiedDto = new FRMUSERSIMPORT_BOL_AdminVerified();

            $adminVerifiedDto->email = $userData['email'];
            $adminVerifiedDto->mobile = '0'.substr($userData['mobile'], -10);
            $adminVerifiedDto->time = time();
            $adminVerifiedDto->verified = $userData['verified'];
            $fullDtoList[] = $adminVerifiedDto;
        }
        if(sizeof($fullDtoList) > 0) {
            $this->adminVerifiedDao->saveList($fullDtoList);
        }
    }

    /**
     * @param $mobile
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function getAdminVerified($mobile)
    {
        return $this->adminVerifiedDao->getAdminVerified($mobile);
    }

    /**
     * @param OW_Event $event
     */
    public function checkAdminVerified(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['mobile']))
        {
            return;
        }
        $adminVerifiedDto = $this->getAdminVerified($params['mobile']);
        if(isset($adminVerifiedDto)) {
            $data['verified'] = $adminVerifiedDto->verified;
            $event->setData($data);
        }
    }
}