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
class FRMSMS_BOL_Service
{
    //we had to use hard coded persian text here
    const DEFAULT_TEXT = 'کد فعال‌سازی: %s';

    /**
     * @var FRMSMS_BOL_TokenDao
     */
    private $tokenDao;

    /**
     * @var FRMSMS_BOL_WaitlistDao
     */
    private $waitlistDao;

    /**
     * @var FRMSMS_BOL_MobileVerifyDao
     */
    private $mobileVerifyDao;

    private static $classInstance;
    public static $MOBILE_FIELD_NAME = 'field_mobile';
    public static $MOBILE_VALIDATOR_PATTERN = "/^(?:09|(00|\+)?989)(?:\d){9}$/m";
    public static $BlockTimePerMinute = 15;
    const ON_GET_USERS_LIST_MENU_IN_ADMIN = 'frmsms.on.get.users.list.menu.in.admin';
    const EVENT_ON_SEND_WITH_CRON = 'frmsms.on_send_with_cron';
    const EVENT_PROCESS_WAITLIST_INCOMPLETE = 'frmsms.process_waitlist_incomplete';
    const QUESTION_SEARCH = 'field_mobile';
    const  UNVERIFIED_MOBILE_NUMBER = 'unverified_mobile';
    /***
     * @return FRMSMS_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * FRMSMS_BOL_Service constructor.
     */
    public function __construct()
    {
        $this->tokenDao = FRMSMS_BOL_TokenDao::getInstance();
        $this->waitlistDao = FRMSMS_BOL_WaitlistDao::getInstance();
        $this->mobileVerifyDao = FRMSMS_BOL_MobileVerifyDao::getInstance();
    }

    /***
     * @param $data
     * @param null $mobileNumber
     * @return mixed
     */
    public function step1_mobileEnteredForLogin($data, $mobileNumber)
    {
        $this->renewUserToken(null, $mobileNumber);
        if (isset($GLOBALS['sms_error'])) {
            // not valid
            $data['valid'] = false;
            $data['send_limit'] = true;
        }
        return $data;
    }

    /***
     * @param $code
     * @param $mobileNumber
     * @return array
     */
    public function step2_checkCode($code, $mobileNumber)
    {
        $sendLimit = false;
        $validCode = false;
        $token = $this->getTokenNumber($mobileNumber);
        $hashCode = FRMSecurityProvider::getInstance()->hashSha256Data($code);
        if (isset($token)) {
            if ($token->try > $this->getMaxTokenPossibleTry()) {
                $sendLimit = true;
            }
            if ($token->token == $hashCode) {
                $this->validateMobileToken(null, $mobileNumber);
                $validCode = true;
            } else {
                $this->tokenDao->increaseTryByMobile($mobileNumber);
            }
        } else {
            $hashCode = FRMSecurityProvider::getInstance()->hashSha256Data(rand(10000, 100000));
            $this->tokenDao->saveOrUpdateToken($hashCode, $mobileNumber);
        }

        return (array('valid' => $validCode, 'limit' => $sendLimit));
    }

    /***
     * @param OW_EVENT $event
     */
    public function verifyCodeEvent(OW_EVENT $event)
    {
        $params = $event->getParams();
        if (!isset($params['mobileNumber']) || !isset($params['code'])) {
            return;
        }
        $code = $params['code'];
        $mobileNumber = $params['mobileNumber'];
        $resp = $this->step2_checkCode($code, $mobileNumber);
        $event->setData($resp);
    }

    /***
     * @param $userId
     * @param $mobile
     * @return FRMSMS_BOL_Token
     */
    public function getUserTokenByVerifiedNumber($userId,$mobile)
    {

        if (isset($userId) && isset($mobile)) {
            $mobile = $this->mobileVerifyDao->getUserMobileByUser($userId);
            if (isset($realMobile)) {
                return $this->tokenDao->getUserTokenByMobile($mobile);
            }
        }
    }

    /**
     * @param null $mobileNumber
     * @return FRMSMS_BOL_Token|null
     */
    public function getTokenNumber($mobileNumber=null)
    {
        $token = null;
        if(!isset($mobileNumber)) {
            $mobileNumber = $this->getMobileNumber();
        }
        if(isset($mobileNumber)) {
            $token = $this->tokenDao->getUserTokenByMobile($mobileNumber);
        }
        return $token;
    }

    /***
     * @param OW_Event $event
     */
    public function deleteToken(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params["userId"])) {
            $this->mobileVerifyDao->deleteByUserId($params["userId"]);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function checkRequestTime(OW_Event $event)
    {
        $params = $event->getParams();
        $tokenResendInterval = OW::getConfig()->getValue('frmsms', 'token_resend_interval');
        $invalidMessage = OW::getLanguage()->text('frmsms', 'token_request_exists_error_message', ['time' => $tokenResendInterval]);
        if (!isset($params['mobileNumber'])) {
            return;
        }
        $mobileNumber = $params['mobileNumber'];
        $tokenDto = $this->tokenDao->getUserTokenByMobile($mobileNumber);
        if (!isset($tokenDto)) {
            return;
        }
        $diff = time() - $tokenDto->time;
        $validTimeIntervalForResend =  ($diff > $tokenResendInterval * 60);
        $event->setData(['validTimeInterval' => $validTimeIntervalForResend, 'errorMessage' => $invalidMessage, 'minute' => $tokenResendInterval]);
    }

    /***
     * @param OW_Event $event
     * @uses when user register and the table of mobile verification needs to be filled
     */
    public function onUserRegister(OW_Event $event)
    {
        $params = $event->getParams();
        /**
         * check if user is registered by frmusersimport
         */
        if(isset($params['userId']) && $params['userId'] != OW::getUser()->getId())
        {
            return;
        }
        $mobileNumber = $this->getMobileNumber();
        if(!isset($params['forEditProfile']) && OW::getUser()->isAuthenticated())
        {
           $this->mobileVerifyDao->saveOrUpdate(OW::getUser()->getId(),$mobileNumber,false);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function renderOldPassword(OW_Event $event)
    {
        $params = $event->getParams();
        if(!FRMSecurityProvider::checkPluginActive('frmmobileaccount', true)) {
            return;
        }
        $mobileNumber = $this->getUserQuestionsMobile(OW::getUser()->getId());
        $resendTokenUrl = OW::getRouter()->urlForRoute('frmsms.resend_token');
        $oldPassword = new TextField($params['inputName']);
        $oldPassword->addValidator(new SMSPasswordValidator($params['inputName']));
        $oldPassword->addAttribute('id','oldPassword');
        $oldPassword->setLabel(OW::getLanguage()->text('frmsms', 'change_password_send_sms_code') .
            ' (<a href="javascript://" onclick="resendToken(\'mobile\',\''.$resendTokenUrl.'\','.$mobileNumber.');">' .
            OW::getLanguage()->text('frmsms', 'change_password_resend_mobile_code') .
            '</a>)');
        $oldPassword->addAttribute('autocomplete','off');
        $oldPassword->setRequired();
        $oldPassword->addAttribute('placeholder',OW::getLanguage()->text('base', 'sms_code_requested'));
        $oldPassword->addAttribute('readonly',true);
        $oldPassword->addAttribute('onfocus',"this.removeAttribute('readonly');");


        OW::getLanguage()->addKeyForJs('frmsms', 'mobile_change_number_title');
        OW::getLanguage()->addKeyForJs('frmsms', 'mobile_change_number_submit');
        OW::getLanguage()->addKeyForJs('frmsms', 'cancel_button');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmsms')->getStaticJsUrl() . 'frmsms.js');
        $event->setData(['input'=>$oldPassword]);
    }

    /**
     * @param $userId
     * @param $value
     * @return bool
     */
    public function checkOldPassword($userId,$value)
    {
        if (empty($userId) || empty($value)) {
            return false;
        }
        $hashCode = FRMSecurityProvider::getInstance()->hashSha256Data($value);
        if($this->hasUserNewUnverifiedNumber())
        {
            $mobile=$this->hasUserNewUnverifiedNumber();
        } else {
            $mobileVerifyObj = $this->mobileVerifyDao->findByUser($userId);
            $mobile = $mobileVerifyObj->mobile;
        }
        $response = false;
        if(isset($mobile)) {
            $token = $this->getTokenNumber($mobile);
            if (isset($token)) {
                $response = ($token->token == $hashCode);
                if (!$response) {
                    $this->tokenDao->increaseTryByMobile($token->mobile);
                }
            }
        }
        return $response;
    }

    /***
     * @return bool
     */
    public function isUserAuthenticatedSuccessfully()
    {
        $newUnverifiedNumber =$this->hasUserNewUnverifiedNumber();
        if (!OW::getUser()->isAuthenticated() || !$this->isUserEmailVerify() || !$this->isUserMobileVerify() || !empty($newUnverifiedNumber)) {
            return false;
        }

        return true;
    }

    /***
     * @return bool
     */
    public function isUserEmailVerify()
    {
        if (!OW::getConfig()->getValue('base', 'confirm_email')) {
            return true;
        }
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        if (!OW::getUser()->getUserObject()->emailVerify) {
            return false;
        }

        return true;
    }

    /***
     * @return bool
     */
    public function isUserMobileVerify()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        $userId = OW::getUser()->getId();

        $item = $this->mobileVerifyDao->findByUser($userId);
        return isset($item) && $item->valid;
    }

    /***
     * @return FRMSMS_BOL_MobileVerify|null
     */
    public function findUserMobileVerifyObject()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }
        $userId = OW::getUser()->getId();

        $item = $this->mobileVerifyDao->findByUser($userId);
        return $item;
    }

    /***
     * @param null $mobile
     * @param null $userId
     * @return mixed
     */
    public function validateMobileToken($userId = null, $mobile = null)
    {
        if ($userId == null && OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }
        if (empty($mobile)) {
            $userItem = $this->mobileVerifyDao->findByUser($userId);
            if (isset($userItem)) {
                $mobile = $userItem->mobile;
            }
        }
        if (isset($mobile)) {
            $this->tokenDao->deleteUserTokenByMobile($mobile);
            $this->deleteUnverifiedUserNumber();
        }
        if(isset($userId)) {
            $this->checkIfMobileQuestionDataMustChange($userId, $mobile);
        }
        return $this->mobileVerifyDao->saveOrUpdate($userId, $mobile, true);

    }

    /**
     * @param $userId
     * @param $mobile
     */
    private function checkIfMobileQuestionDataMustChange($userId,$mobile)
    {
        $changeUserNumber=false;
        $userItem = $this->mobileVerifyDao->findByUser($userId);
        if (empty($userItem) || $userItem->mobile != $mobile) {
            $changeUserNumber = true;
        }
        if(isset($userId) && $this->getUserQuestionsMobile($userId,false) != $mobile)
        {
            $changeUserNumber = true;
        }

        if($changeUserNumber)
        {
            $this->changeUserNumber($userId, $mobile);
        }
    }

    public function deleteUnverifiedUserNumber()
    {
        if(!empty($this->hasUserNewUnverifiedNumber()))
        {
            OW::getSession()->delete(self::UNVERIFIED_MOBILE_NUMBER);
        }
    }

    /**
     * @param $unverifiedNumber
     * @return bool
     * @throws Exception
     */
    public function setUnverifiedNumber($unverifiedNumber)
    {
        OW::getSession()->set(self::UNVERIFIED_MOBILE_NUMBER,$unverifiedNumber);
        return true;
    }
    /**
     * @param $mobileNumber
     * @return bool
     */
    public function isMobileDataRepetitive($mobileNumber)
    {
        $count = (int)$this->findQuestionCountByMobile($mobileNumber);
        if($count>1)
        {
            return true;
        }
        return false;
    }

    /**
     * @param $mobileNumber
     * @return bool
     */
    public function isMobileNumberExists($mobileNumber)
    {
        $count = (int)$this->findQuestionCountByMobile($mobileNumber);
        if($count>0)
        {
            return true;
        }
        return false;
    }


    public function deleteMobileQuestionData($userId,$mobileNumber)
    {
        $mobileNumber = FRMSMS_BOL_Service::normalizeMobileNumber($mobileNumber);
        $questionDao = BOL_QuestionDataDao::getInstance();
        $example = new OW_Example();
        $example->andFieldEqual('questionName', 'field_mobile');
        if(isset($mobileNumber)) {
            $example->andFieldEqual('textValue', $mobileNumber);
        }
        $example->andFieldEqual('userId', $userId);
        $questionDto =  $questionDao->findObjectByExample($example);
        if(isset($questionDto)) {
            $questionDto->textValue = '';
            $questionDao->save($questionDto);
        }
    }

    /***
     * @param $userId
     * @param $token
     * @param $mobile
     * @param $plainToken
     * @return FRMSMS_BOL_Token|mixed
     */
    public function saveOrUpdateToken($userId, $token, $mobile, $plainToken)
    {
        $this->checkToDeleteInvalidData($userId,$mobile);

        $tokenObj = $this->tokenDao->saveOrUpdateToken($token, $mobile);

        OW::getEventManager()->trigger(new OW_Event('frmsms.on_after_sms_token_save', array('userId' => $userId, 'mobile' => $mobile, 'plainToken' => $plainToken)));

        return $tokenObj;
    }


    /**
     * @param $userId
     * @param $mobile
     * @return FRMSMS_BOL_MobileVerify
     */
    // TODO refactor this method Mohammad
    public function addMobileVerifyDto($userId,$mobile)
    {
        $verifyObj = $this->mobileVerifyDao->findByUser($userId);
        if(isset($verifyObj) && $verifyObj->mobile ==$mobile)
        {
            return $verifyObj;
        }
        if (!isset($verifyObj)) {
            $verifyObj = $this->createRawVerifyDto($userId);
        }
        if (isset($userId)) {
            if (isset($mobile)) {
                if ($verifyObj->mobile != $mobile) {

                    if($this->isMobileDataRepetitive($mobile))
                    {
                        $this->deleteMobileQuestionData($userId,$mobile);
                        BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', 0, $userId);
                        OW::getFeedback()->error(OW::getLanguage()->text('frmsms','error_duplication'));
                        OW::getApplication()->redirect(OW_URL_HOME);
                    }
                }
                $verifyObj->mobile = $mobile;
            }
            $this->mobileVerifyDao->save($verifyObj);
            return $verifyObj;
        }
    }

    /**
     * @param $userId
     * @return FRMSMS_BOL_MobileVerify
     */
    private function createRawVerifyDto($userId)
    {
        $verifyObj = new FRMSMS_BOL_MobileVerify();
        $verifyObj->userId = $userId;
        $verifyObj->valid = 0;
        return $verifyObj;
    }
    /**
     * @param $userId
     * @param $mobile
     */
    public function checkToDeleteInvalidData($userId,$mobile)
    {
        $mobileVerifyDto = $this->mobileVerifyDao->findByMobile($mobile);
        if(isset($mobileVerifyDto)) {
            if (!isset($mobileVerifyDto->userId)) {
                $this->mobileVerifyDao->delete($mobileVerifyDto);
            } else if (isset($mobileVerifyDto) && $mobileVerifyDto->userId != $userId) {
                $userDto = BOL_UserService::getInstance()->findUserById($mobileVerifyDto->userId);
                if (!isset($userDto)) {
                    $this->mobileVerifyDao->delete($mobileVerifyDto);
                }
            }
        }
    }
    /***
     * @param null $mobile
     */
    public function renewTimeToken($mobile = null)
    {
        if (empty($mobile)) {
            $mobile = $this->getUserQuestionsMobile(OW::getUser()->getId());
        }
        if (!empty($mobile)) {
            $this->tokenDao->renewTimeToken($mobile);
        }
    }

    /***
     * updateExpiredTokens
     */
    public function updateExpiredTokens()
    {
        $this->tokenDao->updateExpiredTokens();
    }

    /***
     * deleteExpiredTokens
     */
    public function deleteExpiredTokens()
    {
        $this->tokenDao->deleteExpiredTokens();
    }

    /***
     * @param $mobile
     * @return BOL_QuestionData
     */
    public function findQuestionByMobile($mobile)
    {
        $mobile = FRMSMS_BOL_Service::normalizeMobileNumber($mobile);

        $questionDao = BOL_QuestionDataDao::getInstance();
        $example = new OW_Example();
        $example->andFieldEqual('questionName', 'field_mobile');
        $example->andFieldEqual('textValue', $mobile);
        return $questionDao->findObjectByExample($example);
    }

    /***
     * @param $userId
     * @return BOL_QuestionData
     */
    public function findQuestionMobileByUserId($userId)
    {
        $questionDao = BOL_QuestionDataDao::getInstance();
        $example = new OW_Example();
        $example->andFieldEqual('questionName', 'field_mobile');
        $example->andFieldEqual('userId', $userId);
        return $questionDao->findObjectByExample($example);
    }

    /**
     * @param $mobile
     * @return array
     */
    public function findQuestionCountByMobile($mobile)
    {
        $mobile = FRMSMS_BOL_Service::normalizeMobileNumber($mobile);

        $questionDao = BOL_QuestionDataDao::getInstance();
        $example = new OW_Example();
        $example->andFieldEqual('questionName', 'field_mobile');
        $example->andFieldEqual('textValue', $mobile);
        return $questionDao->countByExample($example);
    }

    /**
     * @param $mobiles
     * @return array
     */
    public function findUserIdsByMobiles($mobiles) {
        foreach ($mobiles as &$mobile) {
            $mobile = FRMSMS_BOL_Service::normalizeMobileNumber($mobile);
        }
        $userIds = FRMSMS_BOL_MobileVerifyDao::getInstance()->findUserIdsByMobiles($mobiles);
        return $userIds;
    }


    /***
     * @param $mobile
     * @return BOL_User|null
     */
    public function findUserByQuestionsMobile($mobile)
    {
        $result = $this->findQuestionByMobile($mobile);
        if (!isset($result)) {
            return null;
        }
        return BOL_UserService::getInstance()->findUserById($result->userId);
    }

    /***
     * @param $mobile
     * @return bool
     */
    public function checkQuestionsMobileExist($mobile)
    {
        $result = $this->findQuestionByMobile($mobile);
        return isset($result);
    }

    /***
     * @param $mobile
     * @return bool
     */
    public function checkIsInValidList($mobile)
    {
        $validPhoneNumbers = OW::getConfig()->getValue('frmsms', 'valid_phone_numbers');

        if (empty($validPhoneNumbers) || !isset($validPhoneNumbers)) {
            return true;
        }
        $validPhoneNumbers = json_decode($validPhoneNumbers);
        if (!empty($validPhoneNumbers)) {
            return (in_array($mobile, $validPhoneNumbers));
        }
        return true;
    }

    /***
     * @param $mobile_number
     * @return bool
     */
    public function isMobileValueValid($mobile_number)
    {
        $trimValue = trim($mobile_number);

        if (!preg_match(self::$MOBILE_VALIDATOR_PATTERN, $trimValue)) {
            return false;
        }

        $eventCheckMobileNumber = OW::getEventManager()->trigger(new OW_Event('frmsms.check_mobile_number_validity', array('mobile' => $mobile_number)));
        if (isset($eventCheckMobileNumber->getData()['valid'])) {
            return $eventCheckMobileNumber->getData()['valid'];
        }
        return true;
    }

    /***
     * @param array $mobile_numbers
     * @return bool
     */
    public function isMobilesValueValid($mobile_numbers) {
        foreach($mobile_numbers as $mobile_number) {
            if (!self::getInstance()->isMobileValueValid($mobile_number)) {
                return false;
            }
        }
        return true;
    }

    /***
     * @param OW_Event $event
     */
    public function on_render_join_form(OW_Event $event)
    {
        $param = $event->getParams();
        if ($param['joinForm']) {
            $joinRealFieldNames = OW_Session::getInstance()->get('join.real_question_list');
            foreach ($joinRealFieldNames as $key => $value) {
                if ($value == self::$MOBILE_FIELD_NAME) {
                    /* @var Form $form */
                    $form = $param['joinForm'];

                    $mobileField = $form->getElement($key);
                    $mobileField->addValidator(new MobileValidator());
                    $mobileField->addValidator(new MobileExistenceValidator());
                    $mobileField->addValidator(new MobileIsInValidListValidator());
                    break;
                }
            }
        }
    }

    /***
     * onPluginsInit
     */
    public function onPluginsInit()
    {
        if (OW::getConfig()->getValue('base', 'mandatory_user_approve') && !BOL_AuthorizationService::getInstance()->isSuperModerator(OW::getUser()->getId()) && !BOL_UserService::getInstance()->isApproved()) {
            if (OW::getApplication()->getContext() == OW_Application::CONTEXT_MOBILE) {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_MCTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'resendToken'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_MCTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'block'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_MCTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'checkCode'
                ));
            } else {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_CTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'checkCode'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_CTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'block'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMSMS_CTRL_Manager',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'resendToken'
                ));
            }
        }
    }

    /***
     * @param OW_Event $event
     */
    public function onPhoneNumberCheck(OW_Event $event)
    {
        $params = $event->getParams();
        $number = $params['number'];
        if (isset($number)) {
            $result = $this->findQuestionByMobile($number);
            $data = array();
            if (isset($result)) {
                $data['user_exists'] = true;
                $data['user_id'] = $result->userId;
                // add to mobile_verify if not exists
                $mobileVerifyItem = $this->mobileVerifyDao->findByUser($result->userId);
                if (empty($mobileVerifyItem)) {
                    $this->mobileVerifyDao->saveOrUpdate($result->userId, $number, false);
                }
            } else {
                if (OW::getConfig()->configExists('frmsms', 'valid_phone_numbers')) {
                    $validPhoneNumbers = json_decode(OW::getConfig()->getValue('frmsms', 'valid_phone_numbers'), true);
                    if (!empty($validPhoneNumbers)) {
                        $validPhoneNumbers = array_unique(preg_split('/\n/', $validPhoneNumbers[0]));
                        if (!in_array($number, $validPhoneNumbers)) {
                            $data['userPhone_notIn_ValidList'] = true;
                        }
                    }
                }
                $data['user_exists'] = false;
            }
            $event->setData($data);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function onBeforeQuestionSaveData(OW_Event $event)
    {
        $questionData = $event->getData();
        if (isset($questionData['field_mobile'])) {
            $questionData['field_mobile'] = self::normalizeMobileNumber($questionData['field_mobile']);
        }
        $event->setData($questionData);
    }

    /**
     * @param $mobile
     * @return string
     */
    public static function normalizeMobileNumber($mobile)
    {
        if (strlen($mobile) >= 10) {
            $mobile = substr($mobile, -10);
            $mobile = '0' . $mobile;
        }
        return $mobile;
    }

    /***
     * @param OW_Event $event
     */
    public function onQuestionFieldCreate(OW_Event $event)
    {
        $param = $event->getParams();
        if (isset($param['element']) && isset($param['field_name']) && $param['field_name'] == self::$MOBILE_FIELD_NAME) {
            /* @var FormElement $element */
            $element = $param['element'];
            $element->addValidator(new MobileValidator());
            $validator = new MobileExistenceValidator();
            $validator->setNumber($element->getValue());
            $element->addValidator($validator);

            $validator = new MobileIsInValidListValidator();
            $validator->setNumber($element->getValue());
            $element->addValidator($validator);
        }
    }

    /***
     * @return Form
     */
    public function getJoinCheckCodeForm()
    {
        $checkCodeForm = new Form('codeForm');

        $codeField = new TextField('mobile_code');
        $codeField->setLabel(OW::getLanguage()->text('frmsms', 'mobile_code_label'));
        $codeField->setHasInvitation(false);
        $codeField->addAttribute('placeholder', OW::getLanguage()->text('frmsms', 'mobile_code_label'));
        $codeField->addValidator(new IntValidator());
        $checkCodeForm->addElement($codeField);

        if (OW::getConfig()->getValue('base', 'confirm_email')) {
            $mailField = new TextField('mail_code');
            $mailField->setLabel(OW::getLanguage()->text('frmsms', 'mail_code_label'));
            $mailField->addAttribute('placeholder', OW::getLanguage()->text('frmsms', 'mail_code_label'));
            $mailField->setHasInvitation(false);
            $checkCodeForm->addElement($mailField);
        }

        $element = new Submit('submit');
        $element->setValue(OW::getLanguage()->text('frmsms', 'check_code_submit'));
        $checkCodeForm->addElement($element);

        return $checkCodeForm;
    }

    /***
     * @param null $hashPosted
     * @return bool
     */
    public function mailCheckCodeValue($hashPosted = null)
    {
        if (!OW::getUser()->isAuthenticated() || $hashPosted == null) {
            return false;
        }
        $user = OW::getUser();
        $emailVerifiedData = BOL_EmailVerifyService::getInstance()->findByEmailAndUserId($user->getEmail(), $user->getId(), 'user');
        if ($emailVerifiedData->hash == $hashPosted) {
            return true;
        }

        return false;
    }

    /***
     * @param null $tokenPosted
     * @return bool
     */
    public function isMobileCodeValid($tokenPosted = null)
    {
        if (!OW::getUser()->isAuthenticated() || $tokenPosted == null) {
            return false;
        }
        $hashCode = FRMSecurityProvider::getInstance()->hashSha256Data($tokenPosted);
        $userId = OW::getUser()->getId();
        $token = $this->getTokenNumber();
        if ($token == null) {
            $this->renewUserToken($userId, $this->getUserQuestionsMobile($userId));
            return false;
        } else {
            $this->tokenDao->increaseTryByMobile($token->mobile);
        }
        if ($token->token == $hashCode) {
            return true;
        }

        return false;
    }

    /***
     * @return int
     */
    public function getMaxTokenPossibleTry()
    {
        return (int)OW::getConfig()->getValue('frmsms', 'max_token_request');
    }

    /***
     * redirectToBlockPage
     */
    public function redirectToBlockPage()
    {
        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmsms.mobile_sms_block'));
    }

    /***
     * @param OW_Event $event
     */
    public function onAfterSmsTokenSave(OW_Event $event)
    {
        $params = $event->getParams();
        $userId = null;
        $mobile = null;
        $tokenObj = null;
        $plainToken=null;
        if (isset($params['userId'])) {
            $userId = $params['userId'];
        }
        if (isset($params['mobile'])) {
            $mobile = $params['mobile'];
        }
        if (isset($params['mobile'])) {
            $plainToken = $params['plainToken'];
        }
        if (($userId == null && $mobile == null) || $plainToken == null) {
            return;
        }

        $tokenObj = $this->getTokenNumber($mobile);


        if ($tokenObj != null && $mobile != null) {
            $token = $plainToken;
            $text = OW_Language::getInstance()->text('frmsms', 'sms_content', array('code' => $token), sprintf(self::DEFAULT_TEXT, $token));
            $this->sendSMS($mobile, $text);
        }
    }

    /***
     * @param $userId
     * @param bool $useCache
     * @return null
     */
    public function getUserQuestionsMobile($userId, $useCache = true)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }
        $questionValue = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array(self::$MOBILE_FIELD_NAME), $useCache);
        if (isset($questionValue[$userId][self::$MOBILE_FIELD_NAME])) {
            return $questionValue[$userId][self::$MOBILE_FIELD_NAME];
        }
        return null;
    }

    /***
     * @param OW_Event $event
     */
    public function onQuestionProfileSaveData(OW_Event $event)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }
        $params = $event->getParams();
        $data = $event->getData();
        $userId = (isset($params['userId'])) ? $params['userId'] : OW::getUser()->getId();
        $declaredNumber = (isset($data[self::$MOBILE_FIELD_NAME])) ? $data[self::$MOBILE_FIELD_NAME] : null;
        if (empty($userId)) {
            return;
        }

        if(isset($declaredNumber)) {
            $declaredNumber = UTIL_String::strip_non_numeric($declaredNumber);
        }
        $questionData = $this->findQuestionMobileByUserId($userId);

        /**
         * create verification dto for the first time
         * do not change the number until the new number verified
         */
        if(!isset($questionData))
        {
            $this->mobileVerifyDao->saveOrUpdate($userId,$declaredNumber,false);
        }

        else if(isset($questionData->textValue)&& !empty($declaredNumber) && strcmp($declaredNumber,$questionData->textValue)!=0)
        {
            $this->setUnverifiedNumber($declaredNumber);
            $data[self::$MOBILE_FIELD_NAME] = $questionData->textValue;
        }



        $event->setData($data);
    }

    /***
     * checks to prevent attacks
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     **/
    public function securityChecksBeforeSend($mobileNumber){
        // 1. check IP
        $this->handleBruteForce();

        // 2. check quick re-sends
        $frmsmsEvent = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.check.request.time.interval',
            ['mobileNumber' => $mobileNumber]));
        if (isset($frmsmsEvent->getData()['validTimeInterval']) && !$frmsmsEvent->getData()['validTimeInterval']) {
            $GLOBALS['sms_error'] = 'invalid_time_interval';
            $tokenResendInterval = OW::getConfig()->getValue('frmsms', 'token_resend_interval');
            $GLOBALS['sms_error_message'] = OW::getLanguage()->text('frmsms', 'token_request_exists_error_message', ['time' => $tokenResendInterval]);
            return false;
        }

        // 3. check token tries
        $token = $this->getTokenNumber($mobileNumber);
        if ( isset($token) && $token->try > $this->getMaxTokenPossibleTry()) {
            $GLOBALS['sms_error'] = 'invalid_max_token_try';
            $tokenResendInterval = OW::getConfig()->getValue('frmsms', 'token_resend_interval');
            $GLOBALS['sms_error_message'] = OW::getLanguage()->text('frmsms', 'token_request_exists_error_message', ['time' => $tokenResendInterval]);
            return false;
        }

        return true;
    }

    /***
     * @param $userId
     * @param $mobile
     * @return int
     */
    public function renewUserToken($userId, $mobile)
    {
        if(!$this->securityChecksBeforeSend($mobile)){
            return false;
        }

        $plainToken = rand(100000, 999999);
        $hashCode = FRMSecurityProvider::getInstance()->hashSha256Data($plainToken);
        //$this->addMobileVerifyDto($userId,$mobile);
        $this->saveOrUpdateToken($userId, $hashCode, $mobile, $plainToken);
        return $hashCode;
    }

    /***
     * @param OW_Event $event
     */
    public function onBeforeVerifyEmailPageRedirect(OW_Event $event)
    {
        $event->setData(array('do-not-show' => true));
    }


    /**
     * @return null
     */
    public function hasUserNewUnverifiedNumber()
    {
        $newUnverifiedMobile =  OW::getSession()->isKeySet(self::UNVERIFIED_MOBILE_NUMBER) ? OW::getSession()->get('unverified_mobile') : null;
        if(isset($newUnverifiedMobile))
        {
            return $newUnverifiedMobile;
        }
        return null;
    }
    /***
     * @param OW_Event $event
     */
    public function onBeforeDocumentRenderer(OW_Event $event)
    {


        if (!OW::getUser()->isAuthenticated() || BOL_AuthorizationService::getInstance()->isSuperModerator(OW::getUser()->getId())) {
            return;
        }
        if (!$this->isUserAuthenticatedSuccessfully()) {
            $this->redirectToCheckCode();
        }
    }

    /***
     * @param OW_Event $event
     */
    public function onBeforeRequestHandle(OW_Event $event)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }
        $userId = OW::getUser()->getId();
        $userMobile = $this->getUserQuestionsMobile($userId);
        if ($userMobile == null) {
            /*
             * this code makes unexpected behaviour in join form submission
             */
            // BOL_QuestionService::getInstance()->updateQuestionsEditStamp();
        }
    }

    /***
     * redirectToCheckCode
     */
    public function redirectToCheckCode()
    {
/*        if(OW::getUser()->isAdmin())
        {
            return;
        }*/
        if (strpos($_SERVER['REQUEST_URI'], '/favicon.ico') === false && strpos($_SERVER['REQUEST_URI'], '/join/check/mobile/code') === false && strpos($_SERVER['REQUEST_URI'], '/join/block') === false) {
            $redirect = true;
            $event = OW::getEventManager()->trigger(new OW_Event('before_mobile_validation_redirect'));
            if (isset($event->getData()['not_redirect'])) {
                $redirect = false;
            }
            if ($redirect) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmsms.mobile_code_form'));
            }
        }
    }

    /***
     * @param $mobile
     * @param $text
     */
    public function sendSMS($mobile, $text)
    {
        $text = $this->checkToRemoveLinks($text);
        FRMSMS_CLASS_SmsProvider::getInstance()->send($mobile, $text);
    }

    /***
     * @param $mobile
     * @param $text
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function sendSMSWithCron($mobile, $text)
    {
        $text = $this->checkToRemoveLinks($text);
        $waitlist = new FRMSMS_BOL_Waitlist();
        $waitlist->text = $text;
        $waitlist->phone = $mobile;
        $this->waitlistDao->save($waitlist);
        OW::getEventManager()->trigger(new OW_Event(self::EVENT_ON_SEND_WITH_CRON));
    }


    public function checkToRemoveLinks($text)
    {
        if (OW::getConfig()->configExists('frmsms', 'remove_text_link') && OW::getConfig()->getValue('frmsms', 'remove_text_link')) {
            $text = str_replace('.',' . ',$text);
        }
        return $text;
    }
    /***
     * @param $subject
     * @param $message
     */
    public function sendMailToSiteEmail($subject, $message)
    {
        BOL_MailService::getInstance()->sendMailToSiteEmail($subject, $message);
    }

    /***
     * removeQuestionsMobileField
     */
    public function removeQuestionsMobileField()
    {
        $question = BOL_QuestionService::getInstance()->findQuestionByName(self::$MOBILE_FIELD_NAME);
        BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
        BOL_QuestionService::getInstance()->deleteQuestionToAccountType(self::$MOBILE_FIELD_NAME, array('290365aadde35a97f11207ca7e4279cc'));
    }


    /***
     * @param $controller
     * @throws Redirect404Exception
     */
    public function removeUnverifiedNumberController($controller)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $this->deleteUnverifiedUserNumber();
        exit(json_encode(array('return' => true, 'message' => OW::getLanguage()->text('frmsms', 'user_save_success'), 'reload' => true)));
    }

    /***
     * @param $controller
     * @throws Redirect404Exception
     */
    public function resendTokenController($controller)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $type = $_REQUEST['type'];
        if ($type != 'mail' && $type != 'mobile') {
            throw new Redirect404Exception();
        }
        $user = OW::getUser();
        if (isset( $_REQUEST['unverifiedNumber']) && !empty($_REQUEST['unverifiedNumber']) && is_numeric($_REQUEST['unverifiedNumber'])) {
            if($this->isMobileNumberExists($_REQUEST['unverifiedNumber']))
            {
                $result =  ['type' => 'error', 'userId' => OW::getUser()->getId(), 'message' => OW::getLanguage()->text('frmsms', 'form_validator_mobile_exists_message')];
                exit(json_encode($result));
            }else {
                $this->setUnverifiedNumber($_REQUEST['unverifiedNumber']);
            }
        }
        $reload = false;

        if ($type == 'mail') {
            BOL_EmailVerifyService::getInstance()->sendUserVerificationMail($user->getUserObject());
        } else if ($type == 'mobile') {
            $reload = $this->sendUserToken($user->getId());
            if (isset($GLOBALS['sms_error'])){
                exit(json_encode(array('return' => false, 'type' => 'error',
                    'message' => $GLOBALS['sms_error_message'], 'reload' => false)));
            }
        }

        $reload = isset($_REQUEST['reload']) ? filter_var($_REQUEST['reload'],FILTER_VALIDATE_BOOLEAN) : $reload;

        exit(json_encode(array('return' => true, 'message' => OW::getLanguage()->text('frmsms', 'resend_token_successfully'), 'reload' => $reload)));
    }

    /***
     * @param OW_ActionController $controller
     */
    public function blockPageController($controller)
    {
        if (!OW::getUser()->isAuthenticated()) {
            OW::getApplication()->redirect(OW_URL_HOME);
        }
        if ($this->isUserAuthenticatedSuccessfully()) {
            OW::getApplication()->redirect(OW_URL_HOME);
        }
        $tokenObj = $this->getTokenNumber();
        if ($tokenObj->time + self::$BlockTimePerMinute * 60 < time()) {
            $this->renewTimeToken();
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmsms.mobile_code_form'));
        }

        $release_time = $tokenObj->time + self::$BlockTimePerMinute * 60;
        $release_time = UTIL_DateTime::formatSimpleDate($release_time, false);
        $controller->assign("release_time", $release_time);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js');

        $template = OW_MasterPage::TEMPLATE_BLANK;
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion']) {
            $template = OW_MobileMasterPage::TEMPLATE_BLANK;
        }
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate($template));
    }

    /***
     * handleBruteForce
     */
    public function handleBruteForce()
    {
        $event = new OW_Event('base.bot_detected', array('isBot' => false));
        OW::getEventManager()->trigger($event);
    }

    private function handleAjaxRequest()
    {
        if (isset($_POST['ajaxFunc']) && OW::getRequest()->isAjax()) {
            $callFunc = (string)$_POST['ajaxFunc'];
            $result = null;
            if ($callFunc == 'changeNumber') {
                $newNumber = UTIL_HtmlTag::convertPersianNumbers($_POST['newNumber']);
                if(!$this->isMobileValueValid($newNumber))
                {
                    $result =  ['result' => 'error', 'userId' => OW::getUser()->getId(), 'message' => OW::getLanguage()->text('frmsms', 'mobile_number_not_valid')];
                }
                else if($this->isMobileNumberExists($newNumber))
                {
                    $result =  ['result' => 'error', 'userId' => OW::getUser()->getId(), 'message' => OW::getLanguage()->text('frmsms', 'form_validator_mobile_exists_message')];
                }else {
                    $this->setUnverifiedNumber(UTIL_String::strip_non_numeric($newNumber));
                    $result =   ['result' => 'ok', 'userId' => OW::getUser()->getId(), 'message' => OW::getLanguage()->text('base', 'edit_successfull_edit')];
                }
            }
            exit(json_encode($result));
        }
    }

    private Function setTemplate()
    {
        $template = OW_MasterPage::TEMPLATE_BLANK;
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
        if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion']) {
            $template = OW_MobileMasterPage::TEMPLATE_BLANK;
        }
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate($template));
    }

    public function checkMobileQuestionDataFilled()
    {
        if (OW::getUser()->isAuthenticated()) {
            $questionDto = $this->findQuestionMobileByUserId(OW::getUser()->getId());
            if (!isset($questionDto)) {
                BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', 0, OW::getUser()->getId());
               // OW::getApplication()->redirect(OW_URL_HOME);
            }
        }
    }

    /***
     * @param OW_ActionController $controller
     */
    public function checkJoinCodeController($controller)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        if ($this->isUserAuthenticatedSuccessfully()) {
            OW::getApplication()->redirect(OW_URL_HOME);
        }

        $this->handleAjaxRequest();

        $this->setTemplate();

        /**
         * if user has unverified number get token of this number otherwise
         * get token of other declared number
         */
        $token = $this->getTokenNumber();

        if (isset($token) && $token->try > $this->getMaxTokenPossibleTry()) {
            $this->redirectToBlockPage();
        }

        $mobileNumber = $this->getMobileNumber();

        $this->checkMobileQuestionDataFilled();
        $checkCodeForm = $this->getJoinCheckCodeForm();

        $mobileClass = 'unverify';
        if ($this->isUserMobileVerify() && empty($this->hasUserNewUnverifiedNumber())) {
            $mobileClass = 'verify';
            $checkCodeForm->getElement('mobile_code')->setValue(OW::getLanguage()->text('frmsms', 'verify_sms_placeholder'));
            $checkCodeForm->getElement('mobile_code')->addAttribute('disabled', 'disabled');
            $checkCodeForm->getElement('mobile_code')->addAttribute('class', 'ow_ic_ok');
            $checkCodeForm->getElement('mobile_code')->removeValidators();
        }

        if(isset($mobileNumber) && $mobileClass == 'unverify' ) {
            $this->processCheckCodeForm($checkCodeForm, $mobileNumber);
        }

        $checkCodeForm->getElement('mobile_code')->addAttribute('autocomplete', 'off');
        $controller->assign('mobileClass', $mobileClass);
        $message = OW::getLanguage()->text('frmsms','unverified_number_message',['mobile'=>$this->getMobileNumber()]);
        $controller->assign('unverifiedNumberMessage',$message );
        if(!empty($this->hasUserNewUnverifiedNumber()))
        {
            $controller->assign('removeUnverifiedNumberUrl', OW::getRouter()->urlForRoute('frmsms.remove_unverified_number'));
        }

        if (OW::getConfig()->getValue('base', 'confirm_email')) {
            $mailClass = 'unverify';
            $emailVerifiedData = BOL_EmailVerifyService::getInstance()->findByEmailAndUserId(OW::getUser()->getEmail(), OW::getUser()->getId(), 'user');
            if (!$this->isUserEmailVerify() && $emailVerifiedData == null) {
                BOL_EmailVerifyService::getInstance()->sendUserVerificationMail(OW::getUser()->getUserObject());
            } else if (OW::getUser()->getUserObject()->emailVerify) {
                $checkCodeForm->getElement('mail_code')->setValue(OW::getLanguage()->text('frmsms', 'verify_email_placeholder'));
                $checkCodeForm->getElement('mail_code')->addAttribute('disabled', 'disabled');
                $checkCodeForm->getElement('mail_code')->removeValidators();
                $checkCodeForm->getElement('mail_code')->addAttribute('class', 'ow_ic_ok');
                $mailClass = 'verify';
            }
            $checkCodeForm->getElement('mail_code')->addAttribute('autocomplete', 'off');
            $controller->assign('mailClass', $mailClass);
        }

        $controller->addForm($checkCodeForm);
        $code='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'isPermanent'=>true,'activityType'=>'logout')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $code = $frmSecuritymanagerEvent->getData()['code'];
        }
        $controller->assign('signOutUrl', OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_sign_out'), array('code' => $code)));
        $controller->assign('resendTokenUrl', OW::getRouter()->urlForRoute('frmsms.resend_token'));
        $controller->assign('changeNumberUrl', OW::getRouter()->urlForRoute('frmsms.mobile_code_form'));
        $controller->assign('current_number', !empty($this->hasUserNewUnverifiedNumber()) ? $this->hasUserNewUnverifiedNumber()  : $this->getUserQuestionsMobile(OW::getUser()->getId()));

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmsms')->getStaticCssUrl() . 'frmsms.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmsms')->getStaticJsUrl() . 'frmsms.js');
        
        $controller->setDocumentKey("frmsms_check_code");
    }

    /**
     * @param $checkCodeForm
     * @param $mobile
     * @param $mobileClass
     */
    private function processCheckCodeForm($checkCodeForm, $mobile)
    {
        $mobileVerify = $this->mobileVerifyDao->findByMobile($mobile);
        if (OW::getRequest()->isPost() && $checkCodeForm->isValid($_POST)) {
            $values = $checkCodeForm->getValues();
            $mobileCodeValid = true;
            if (empty($mobileVerify) || !$mobileVerify->valid) {
                $mobileCodeValid = $this->isMobileCodeValid($values['mobile_code']);
                if ($mobileCodeValid) {
                    if (isset($mobile)) {
                        $this->validateMobileToken(null, $mobile);
                    } else {
                        $this->validateMobileToken();
                    }
                    OW::getFeedback()->info(OW::getLanguage()->text('frmsms', 'mobile_token_authenticated'));
                } else if (!empty($values['mobile_code'])) {
                    $this->securityChecksBeforeSend($values['mobile_code']);
                } else if (empty($values['mail_code'])) {
                    OW::getFeedback()->error(OW::getLanguage()->text('frmsms', 'fill_mobile_code'));
                }
            }

            $mailCheckCode = true;
            $needToVerifyEmail = OW::getConfig()->getValue('base', 'confirm_email', false);
            if (!OW::getUser()->getUserObject()->emailVerify && $needToVerifyEmail) {
                $mailCheckCode = $this->mailCheckCodeValue($values['mail_code']);
                if ($mailCheckCode) {
                    BOL_EmailVerifyService::getInstance()->verifyEmail($values['mail_code']);
                }
            }

            if ($mobileCodeValid && $mailCheckCode) {
                OW::getFeedback()->info(OW::getLanguage()->text('frmsms', 'user_authenticated_successful'));
                OW::getApplication()->redirect(OW_URL_HOME);
            } else {
                OW::getFeedback()->error(OW::getLanguage()->text('frmsms', 'fill_correct_data'));
            }
        } else {
            $this->sendUserToken(OW::getUser()->getId());
            if (isset($GLOBALS['sms_error'])){
                OW::getFeedback()->error($GLOBALS['sms_error_message']);
            }
        }
    }

    public function getMobileNumber()
    {
        $mobileNumber = $this->hasUserNewUnverifiedNumber();
        if(empty($mobileNumber) && OW::getUser()->isAuthenticated())
        {
            $mobileVerifyDto = $this->mobileVerifyDao->findByUser(OW::getUser()->getId());
            if(isset($mobileVerifyDto)) {
                $mobileNumber = $mobileVerifyDto->mobile;
            }
        }
        if(empty($mobileNumber) && OW::getUser()->isAuthenticated())
        {
            $mobileNumber = $this->getUserQuestionsMobile(OW::getUser()->getId(),false);
        }
        return $mobileNumber;
    }

    /***
     * @return string|null
     */
    public function getPanelThreshold()
    {
        return OW::getConfig()->getValue('frmsms', 'credit_threshold');
    }

    /***
     * @param $max
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function processWaitList($max)
    {
        $list = $this->waitlistDao->findListByCount($max);
        foreach ($list as $item) {
            /* @var $item FRMSMS_BOL_Waitlist */
            $this->sendSMS($item->phone, $item->text);
            $this->waitlistDao->deleteById($item->id);
        }
        if (count($list) == $max) {
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_PROCESS_WAITLIST_INCOMPLETE));
        }
    }

    /**
     * @param $sectionId
     * @return array
     */
    public function getAdminSections($sectionId)
    {
        $sections = array();

        for ($i = 1; $i <= 2; $i++) {
            $sections[] = array(
                'sectionId' => $i,
                'active' => $sectionId == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frmsms-admin.section-id', array('sectionId' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }
        return $sections;
    }

    /***
     * @param $sectionId
     * @return string|null
     */
    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmsms', 'generalSetting');
        } else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmsms', 'restrictSetting');
        }
        return OW::getLanguage()->text('frmsms', 'generalSetting');
    }

    /***
     * @param OW_Event $event
     */
    public function onGetUsersListMenuInAdmin(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['menuItems']) || !isset($params['order'])) {
            return;
        }
        $menuItems = $params['menuItems'];
        $language = OW::getLanguage();
        $item = new BASE_MenuItem();
        $urlParams = array('list' => 'smsActivation');
        $item->setLabel($language->text('frmsms', 'users_need_sms_code_activation'));
        $item->setUrl(OW::getRouter()->urlForRoute('admin_users_browse', $urlParams));
        $item->setKey('smsActivation');
        $item->setIconClass('ow_ic_mobile ow_dynamic_color_icon');
        $item->setOrder($params['order'] + 1);

        array_push($menuItems, $item);

        $event->setData(array('menuItems' => $menuItems));
    }

    /***
     * @param OW_Event $event
     */
    public function addActivateSMSCodeButton(OW_Event $event)
    {
        $params = $event->getParams();
        $language = OW::getLanguage();
        if (!isset($params['type']) || $params['type'] != 'smsActivation') {
            return;
        }
        $button['smsActivation'] = array('name' => 'smsActivation', 'id' => 'smsActivation_user_btn', 'label' => $language->text('frmsms', 'smsActivation_user_btn'), 'class' => 'ow_mild_green');
        $event->setData(array('buttonSMSActivation' => $button['smsActivation']));
    }

    /***
     * @param OW_Event $event
     */
    public function getUserListAndCountNeedsActivationSMS(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['first']) || !isset($params['count'])) {
            return;
        }
        $userIds = $this->mobileVerifyDao->findNotVerifiedUsers($params['first'], $params['count']);
        $userList = BOL_UserService::getInstance()->findUserListByIdList($userIds);
        $event->setData(array('userCount' => sizeof($userList), 'userList' => $userList));
    }

    /***
     * @param OW_Event $event
     */
    public function findUnverifiedSMSStatusForUserList(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['userIdList'])) {
            return;
        }
        $userUnverifiedSMSList = $this->findUnverifiedStatusForUserList($params['userIdList']);
        $event->setData(array('userUnverifiedSMSList' => $userUnverifiedSMSList));
    }

    /***
     * @param OW_Event $event
     */
    public function activateUserSmsCode(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['postData'])) {
            return;
        }
        $postData = $params['postData'];
        if (!isset($postData['users']) || !isset($postData['smsActivation'])) {
            return;
        }
        $userIds = array();
        foreach ($postData['users'] as $id) {
            $userIds[] = $id;
        }
        $this->mobileVerifyDao->activateUserSMSTokenByUserIds($userIds);
        $event->setData(array('success' => true));
    }

    /***
     * deletes phone numbers in question_data but not in mobile_verify
     */
    public function deleteInvalidQuestionValues(){
        $prefix = OW_DB_PREFIX;

        OW::getDbo()->query("
        UPDATE `{$prefix}base_preference_data`
        SET `value`=0
        WHERE `key`='profile_details_update_stamp'
        AND `userId` in (
            SELECT userId FROM `{$prefix}base_question_data` 
             WHERE`questionName`='field_mobile' AND 
             `userId` not in (SELECT `userId` FROM `{$prefix}frmsms_mobile_verify`)
        );
        ");

        OW::getDbo()->query("
        DELETE FROM `{$prefix}base_question_data` 
         WHERE`questionName`='field_mobile' AND 
         `userId` not in (SELECT `userId` FROM `{$prefix}frmsms_mobile_verify`)
        ;");
    }

    /***
     * @param OW_Event $event
     */
    public function checkReceivedMessage(OW_Event $event)
    {
        $params = $event->getParams();
        $requestData = $params['data'];
        if (!isset($requestData['type'])) {
            return;
        }
        $requestType = $requestData['type'];

        if ($requestType == "send_verification_code_to_mobile") {
            $mobileNumber = (isset($requestData['mobileNumber'])) ? $requestData['mobileNumber'] : '';
            $userId = null;
            if (OW::getUser()->isAuthenticated()) {
                $userId = OW::getUser()->getId();
                if (empty($mobileNumber)) {
                    $mobileNumber = $this->getUserQuestionsMobile($userId, false);
                    $requestData['mobileNumber'] = $mobileNumber;
                }
            }

            if (empty($mobileNumber)) {
                return;
            }
            $eventCheckNumber = new OW_Event('frmsms.phone_number_check', array('number' => $mobileNumber));
            OW_EventManager::getInstance()->trigger($eventCheckNumber);
            $eventCheckNumberData = $eventCheckNumber->getData();

            $data = $requestData;
            $data['valid'] = true;

            if (!isset($eventCheckNumberData)) {
                // error
                $data['valid'] = false;

            } else if (isset($eventCheckNumberData['userPhone_notIn_ValidList'])) {
                // not valid
                $data['valid'] = false;

            } else {
                $frmsmsEvent = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.check.request.time.interval', ['mobileNumber' => $mobileNumber]));
                if (isset($frmsmsEvent->getData()['validTimeInterval']) && !$frmsmsEvent->getData()['validTimeInterval']) {
                    $data['send_limit'] = true;
                    $data['minute'] = $frmsmsEvent->getData()['minute'];
                    $data['valid'] = false;
                } else {
                    // Send token to mobile
                    $data = $this->step1_mobileEnteredForLogin($data, $mobileNumber);
//                    if(isset($requestData['verifyFor']) && $requestData['verifyFor']=='change_password') {
//                    }
                }
            }
        } else {
            return;
        }
        $event->setData(json_encode($data));
    }

    /***
     * @param OW_Event $event
     */
    public function forgotPasswordFormGenerated(OW_Event $event)
    {

        $language = OW::getLanguage();
        /** @var Form $form */
        $data = $event->getData();
        $params = $event->getParams();
        if (!isset($params['form'])) {
            return;
        }
        $form = $params['form'];
        $elements = $form->getElements();
        $email = $elements['email'];
        if (isset($email)) {
            $form->deleteElement('email');
            $emailElement = new TextFieldValidatorDeletable('email');
            $emailElement->setRequired(true);
            $emailElement->addValidator(new EmailAndMobileValidator());
            $emailElement->setHasInvitation(true);
            $emailElement->setInvitation($language->text('frmsms', 'forgot_password_email_and_mobile_invitation_message'));
            $form->addElement($emailElement);
            $deleted = $emailElement->deleteValidatorOfClass('FRMEMAILCONTROLLER_CLASS_EmailProviderValidator');
            if ($deleted){
                $emailElement->addValidator(new EmailSourceValidator());
            }
        }
        $data['headerText'] = 'frmsms+forgot_password_header_message';


        $data['form']=$form;
        $event->setData($data);
    }

    /***
     * @param OW_Event $event
     */
    public function processForm(OW_Event $event)
    {
        $data = $event->getParams()['data'];
        if (isset($data) && isset($data['email'])) {
            $trimValue = trim($data['email']);
            if (preg_match(self::$MOBILE_VALIDATOR_PATTERN, $trimValue)) {
                $this->sendResetUrl($data);
                $result = array('processed' => true, 'feed_back' => OW::getLanguage()->text('frmsms', 'forgot_password_sms_send_success'));
                $event->setData($result);
            }
        }
    }

    /***
     * @param array $data
     */
    public function sendResetUrl(array $data)
    {
        $language = OW::getLanguage();
        $mobile = trim($data['email']);
        $user = $this->findUserByQuestionsMobile($mobile);

        if ($user === null) {
            throw new LogicException($language->text('base', 'forgot_password_no_user_error_message'));
        }

        $resetPassword = BOL_UserService::getInstance()->findResetPasswordByUserId($user->getId());

        if ($resetPassword !== null) {
            if ($resetPassword->getUpdateTimeStamp() > time()) {
                throw new LogicException($language->text('base', 'forgot_password_request_exists_error_message'));
            } else {
                $resetPasswordCode = BOL_UserService::getInstance()->getNewResetPasswordCode($user->getId());
            }
        } else {
            $resetPasswordCode = BOL_UserService::getInstance()->getNewResetPasswordCode($user->getId());
        }


        $vars = array('code' => $resetPasswordCode, 'username' => $user->getUsername(), 'requestUrl' => OW::getRouter()->urlForRoute('base.reset_user_password_request'),
            'resetUrl' => OW::getRouter()->urlForRoute('base.reset_user_password', array('code' => $resetPasswordCode)));

        $text = $language->text('frmsms', 'reset_password_sms_template_content_txt', $vars);

        $this->sendSMS($mobile, $text);
    }

    /***
     * changes mobile number
     *
     * @param $userId
     * @param $newNumber
     * @return array
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function changeUserNumber($userId, $newNumber)
    {
        // change from question table
        if (isset($userId)) {
            $questionDao = BOL_QuestionDataDao::getInstance();
            $example = new OW_Example();
            $example->andFieldEqual('questionName', 'field_mobile');
            $example->andFieldEqual('userId', $userId);
            $item = $questionDao->findObjectByExample($example);
            if (empty($item)) {
                $item = new BOL_QuestionData();
                $item->questionName = 'field_mobile';
                $item->userId = $userId;
            }
            $newNumber = UTIL_String::strip_non_numeric($newNumber);
            $item->textValue = $newNumber;
            $questionDao->save($item);
        }
    }

    /***
     * @param $userId
     * @param $newNumber
     */
    public function changeUserMobileVerify($userId, $newNumber)
    {
        // update mobile verify
        $userItem = $this->mobileVerifyDao->findByUser($userId);
        if (isset($userItem)) {
            if ($userItem->mobile != $newNumber) {
                $this->mobileVerifyDao->updateUserMobile($userItem->mobile, $newNumber, true);
            }
        } else {
            $this->mobileVerifyDao->saveOrUpdate($userId, $newNumber, false);
        }
    }

    /**
     * @param $userId
     * @return bool
     */
    private function sendUserToken($userId)
    {
        $reload = true;
        $userMobile = $this->getMobileNumber();
        $this->renewUserToken($userId, $userMobile);
        return $reload;
    }

    public function onGetSearchQAdmin(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['searchQ']))
        {
            return;
        }
        $searchQ = $params['searchQ'];
        if(isset($data['searchQ']))
        {
            $searchQ = $data['searchQ'];
        }
        $question = self::QUESTION_SEARCH;
        $searchQ[$question] = OW::getLanguage()->text('base', 'questions_question_'.$question.'_label');
        $data['searchQ'] = $searchQ;
        $event->setData($data);
    }

    public function getUserListQuestionValue(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['fieldList']))
        {
            return;
        }
        $fieldList= $params['fieldList'];
        if(isset($data['fieldList']))
        {
            $fieldList = $data['fieldList'];
        }
        $question = self::QUESTION_SEARCH;
        array_push($fieldList,$question);
        $data['fieldList'] = $fieldList;
        $event->setData($data);
    }

    public function getUserMobileNumber(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['userId']))
        {
            return;
        }
        $userItem = FRMSMS_BOL_MobileVerifyDao::getInstance()->findByUser($params['userId']);
        if(!isset($userItem))
        {
            return;
        }
        $mobileNumber = $userItem->mobile;
        $data['mobileNumber'] = $mobileNumber;
        $event->setData($data);
    }

    public function deleteUserSMSData($userId)
    {
        $mobileVerifyData = $this->mobileVerifyDao->findByUser($userId);
        if(isset($mobileVerifyData)) {
            $this->mobileVerifyDao->deleteByUserId($userId);
            if(isset($mobileVerifyData->mobile)) {
                $this->tokenDao->deleteUserTokenByMobile($mobileVerifyData->mobile);
            }
        }
    }

    public function onUnregisterUser(OW_Event $event)
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $this->deleteUserSMSData($userId);
        }
    }

    public function findUnverifiedStatusForUserList( $idList )
    {
        $unverifiedUsers = $this->mobileVerifyDao->findUnverifiedStatusForUserList( $idList );

        $resultArray = array();

        foreach ( $idList as $userId )
        {
            $resultArray[$userId] = in_array($userId, $unverifiedUsers) ? true : false;
        }

        return $resultArray;
    }
}

class MobileValidator extends OW_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'form_validator_mobile_invalid_message');

        if (empty($errorMessage)) {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid($value)
    {
        // doesn't check empty values
        if ((is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->checkValue($val)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->checkValue($value);
        }
    }

    public function setJsObjectName($name)
    {
        if (!empty($name)) {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue($value)
    {
        return FRMSMS_BOL_Service::getInstance()->isMobileValueValid($value);
    }
}

class MobileExistenceValidator extends OW_Validator
{
    protected $jsObjectName = null;
    protected $number = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'form_validator_mobile_exists_message');

        if (empty($errorMessage)) {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function isValid($value)
    {
        // doesn't check empty values
        if ((is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->checkValue($val)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->checkValue($value);
        }
    }

    public function setJsObjectName($name)
    {
        if (!empty($name)) {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue($value)
    {
        if ($this->number !== null) {
            if ($this->number === $value) {
                return true;
            }
        }
        return !FRMSMS_BOL_Service::getInstance()->checkQuestionsMobileExist($value);
    }
}

class MobileIsInValidListValidator extends OW_Validator
{
    protected $jsObjectName = null;
    protected $number = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmsms', 'number_is_not_valid_list');

        if (empty($errorMessage)) {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function isValid($value)
    {
        // doesn't check empty values
        if ((is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->checkValue($val)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->checkValue($value);
        }
    }

    public function setJsObjectName($name)
    {
        if (!empty($name)) {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue($value)
    {
        if ($this->number !== null) {
            if ($this->number === $value) {
                return true;
            }
        }
        return FRMSMS_BOL_Service::getInstance()->checkIsInValidList($value);
    }
}

class EmailAndMobileValidator extends RegExpValidator
{
    private static $PATTERN = '/^(?:09|(00|\+)?989)(?:\d){9}$|^([\w\-\.\+\%]*[\w])@((?:[A-Za-z0-9\-]+\.)+[A-Za-z]{2,})$/m';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(self::$PATTERN);

        $errorMessage = OW::getLanguage()->text('frmsms', 'form_validator_email_or_mobile_error_message');

        if (empty($errorMessage)) {
            $errorMessage = 'Email or Mobile validator error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}

class EmailSourceValidator extends OW_Validator
{
    /***
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (preg_match(FRMSMS_BOL_Service::$MOBILE_VALIDATOR_PATTERN, trim($value)))
            return true;
        if (!FRMSecurityProvider::checkPluginActive('frmemailcontroller', true)) {
            return true;
        }
        $validator = new FRMEMAILCONTROLLER_CLASS_EmailProviderValidator();
        return $validator->isValid($value);
    }
}

class TextFieldValidatorDeletable extends TextField
{
    public function deleteValidatorOfClass($class)
    {
        $validatorArray = array();
        $deleted = false;
        foreach ($this->validators as $index => $validator) {
            if (!is_a($validator, $class)) {
                $validatorArray[] = $validator;
            } else {
                $deleted = true;
            }
        }
        $this->validators = $validatorArray;
        return $deleted;
    }
}

class SMSPasswordValidator extends OW_Validator
{
    private $inputName;

    /***
     * OldPasswordValidator constructor.
     * @param string $inputName
     */
    public function __construct($inputName = 'oldPassword')
    {
        $this->inputName = $inputName;
        $language = OW::getLanguage();
        $this->setErrorMessage($language->text('frmsms', 'invalid_sms_code'));
    }

    /***
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $result = $this->isValidPassword( OW::getUser()->getId(), $value );

        return $result;
    }

    public function isValidPassword( $userId, $value)
    {
        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( $value === null || $user === null )
        {
            return false;
        }

        $currentUser = (OW::getUser()->isAuthenticated())?OW::getUser()->getId():$userId;
        $result = FRMSMS_BOL_Service::getInstance()->checkOldPassword($currentUser,$value);
        return $result;
    }
}