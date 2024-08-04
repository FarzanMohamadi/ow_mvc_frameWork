<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontrolkids.bol
 * @since 1.0
 */
class FRMCONTROLKIDS_BOL_Service
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
    
    private $kidsRelationshipDao;
    
    private function __construct()
    {
        $this->kidsRelationshipDao = FRMCONTROLKIDS_BOL_KidsRelationshipDao::getInstance();
    }

    /***
     * @param $kidUserId
     * @param $parentEmail
     * @param bool $checkAuth
     * @return FRMCONTROLKIDS_BOL_KidsRelationship
     */
    public function addRelationship($kidUserId, $parentEmail, $checkAuth = true)
    {
        $this->deleteRelationship($kidUserId);
        return $this->kidsRelationshipDao->addRelationship($kidUserId, $parentEmail, $checkAuth);
    }

    /***
     * @param $kidUserId
     * @param $parentUserId
     * @return bool
     */
    public function isParentExist($kidUserId, $parentUserId){
        return $this->kidsRelationshipDao->isParentExist($kidUserId, $parentUserId);
    }

    /***
     * @param $parentEmail
     * @param $kidUsername
     * @param $kidEmail
     * @param $isForRegistration
     */
    public function sendLinkToParentUser($parentEmail, $kidUsername, $kidEmail, $isForRegistration)
    {
        $mails = array();
        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($parentEmail);
        if($isForRegistration){
            $mail->setSubject(OW::getLanguage()->text('frmcontrolkids', 'email_registration_subject', array('site_name' => OW::getConfig()->getValue('base', 'site_name'))));
            $mail->setHtmlContent($this->getRegistrationEmailContent($parentEmail, $kidUsername, $kidEmail));
            $mail->setTextContent($this->getRegistrationEmailContent($parentEmail, $kidUsername, $kidEmail));
        }else{
            $mail->setSubject(OW::getLanguage()->text('frmcontrolkids', 'parent_email_subject', array('site_name' => OW::getConfig()->getValue('base', 'site_name'))));
            $mail->setHtmlContent($this->getParentEmailContent($parentEmail, $kidUsername, $kidEmail));
            $mail->setTextContent($this->getParentEmailContent($parentEmail, $kidUsername, $kidEmail));
        }
        $mails[] = $mail;
        OW::getMailer()->addListToQueue($mails);
    }
    public function checkUsersParentInfoExists(OW_Event $event)
    {
        if(!OW::getUser()->isAuthenticated()){
            return;
        }

        $attr = OW::getRequestHandler()->getHandlerAttributes();
        if ($attr[OW_RequestHandler::ATTRS_KEY_CTRL] == "BASE_CTRL_Edit" && $attr[OW_RequestHandler::ATTRS_KEY_ACTION]=="index" )
        {
            return;
        }
        $userId = OW::getUser()->getId();
        $birthdays = BOL_QuestionService::getInstance()->getQuestionData(array($userId), array('birthdate'));
        if(isset($birthdays[$userId]['birthdate'])) {
            $parentInfo = $this->getParentInfo($userId);
            if ($this->isInChildhood($birthdays[$userId]['birthdate']) && !isset($parentInfo) && strpos($_SERVER['REDIRECT_URL'], 'frmcontrolkids/enterParentEmail') == false) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmcontrolkids.enter_parent_email'));
            }
        }
    }
    public function isInChildhood($date){
        $userAge = time();
        if(is_array($date)){
            $userAge = time() - date_timestamp_get(date_create($date['birthdate']));
        }else{
            $userAge = time() - date_timestamp_get(date_create($date));
        }
        $marginTime = OW::getConfig()->getValue('frmcontrolkids','marginTime') * 7 * 24 * 60 * 60;
        $minimumAge = OW::getConfig()->getValue('frmcontrolkids','kidsAge') * 365 * 24 * 60 * 60;
        if($userAge + $marginTime < $minimumAge){
            return true;
        }
        return false;
    }

    /***
     * @param $parentEmail
     * @param $kidUsername
     * @param $kidEmail
     * @return mixed|null|string
     */
    public function getRegistrationEmailContent($parentEmail, $kidUsername, $kidEmail){
        $content = OW::getLanguage()->text('frmcontrolkids', 'email_registration_content', array('site_name' => OW::getConfig()->getValue('base', 'site_name'),'kidUsername' => $kidUsername, 'kidEmail' => $kidEmail, 'parentEmail' => $parentEmail));
        $content .= '<br/><br/>';
        $content .= '<a href="'. OW::getRouter()->urlForRoute('base_join').'?parentEmailValue='. $parentEmail .'">'.OW::getLanguage()->text('frmcontrolkids', 'email_registration_link_label').'</a>';
        return $content;
    }

    /***
     * @param $parentEmail
     * @param $kidUsername
     * @param $kidEmail
     * @return mixed|null|string
     */
    public function getParentEmailContent($parentEmail, $kidUsername, $kidEmail){
        $content = OW::getLanguage()->text('frmcontrolkids', 'parent_email_content', array('kidUsername' => $kidUsername, 'kidEmail' => $kidEmail, 'parentEmail' => $parentEmail));
        return $content;
    }

    /***
     * @param $parentUserId
     * @return array
     */
    public function getKids($parentUserId){
        return $this->kidsRelationshipDao->getKids($parentUserId);
    }

    /***
     * @param $kiduserId
     * @return array
     */
    public function getParentInfo($kiduserId){
        return $this->kidsRelationshipDao->getParentInfo($kiduserId);
    }

    /***
     * @param $parentEmail
     * @param $parentUserId
     */
    public function updateParentUserIdUsingEmail($parentEmail, $parentUserId)
    {
        return $this->kidsRelationshipDao->updateParentUserIdUsingEmail($parentEmail, $parentUserId);
    }

    /***
     * @param $kidUserId
     */
    public function deleteRelationship($kidUserId)
    {
        return $this->kidsRelationshipDao->deleteRelationship($kidUserId);
    }


    public function logout(){
        OW::getUser()->logout();
        if ( isset($_COOKIE['ow_login']) )
        {
            BOL_UserService::getInstance()->setLoginCookie('', null, time() - 3600);
        }
        OW::getSession()->set('no_autologin', true);
    }

    public function onAddMainConsoleItem(OW_Event $event){
        if(OW_Session::getInstance()->isKeySet('sl_'.OW::getUser()->getId())){
            //logout from child's account
            $parentUsername = BOL_UserService::getInstance()->findUserById(OW_Session::getInstance()->get('sl_'.OW::getUser()->getId()))->username;
            $label = OW::getLanguage()->text('frmcontrolkids','logoutFromShadowLogin', array('kidUsername' => OW::getUser()->getUserObject()->username, 'parentUsername' => $parentUsername));
            $url = OW::getRouter()->urlForRoute('frmcontrolkids.logout_from_shadow_login');
            $event->add(array('label' => $label, 'url' => $url));

        }
        if(sizeof($this->getKids(OW::getUser()->getId())) > 0){
            //add child item
            $event->add(array('label' => OW::getLanguage()->text('frmcontrolkids','bottom_menu_item'), 'url' => OW::getRouter()->urlForRoute('frmcontrolkids.index')));
        }
    }

    public function onMobileAddItem(BASE_CLASS_EventCollector $event){
        if(OW_Session::getInstance()->isKeySet('sl_'.OW::getUser()->getId())){
            //logout from child's account
            $parentUsername = BOL_UserService::getInstance()->findUserById(OW_Session::getInstance()->get('sl_'.OW::getUser()->getId()))->username;
            $label = OW::getLanguage()->text('frmcontrolkids','logoutFromShadowLogin', array('kidUsername' => OW::getUser()->getUserObject()->username, 'parentUsername' => $parentUsername));
            $url = OW::getRouter()->urlForRoute('frmcontrolkids.logout_from_shadow_login');
            $event->add(array('label' => $label, 'url' => $url));
        }
        if(sizeof($this->getKids(OW::getUser()->getId())) > 0){
            //add child item
            $event->add(array('label' => OW::getLanguage()->text('frmcontrolkids','bottom_menu_item'), 'url' => OW::getRouter()->urlForRoute('frmcontrolkids.index')));
        }
    }

    public function onBeforeUserRegistered(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['birthdate']) && $this->isInChildhood($params['birthdate'])){
            if(!isset($_REQUEST['parentEmail']) || !UTIL_Validator::isEmailValid($_REQUEST['parentEmail'])){
                OW_Session::getInstance()->set('parentEmailValueError', true);
                if (OW::getRequest()->isAjax()) {
                    echo json_encode(array('result' => false));
                    exit;
                }
                OW::getFeedback()->error(OW::getLanguage()->text('frmcontrolkids', 'parentEmailEmpty'));
                OW::getApplication()->redirect();
            }
        }
    }

    public function onBeforeJoinFormRender(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['form']) && isset($params['controller'])){
            if(isset($params['editUserId'])){
                $this->addParentEmailFieldToForm($params['form'],$params['editUserId']);
            }else{
                $this->addParentEmailFieldToForm($params['form']);
            }
            $dateFieldName = null;
            if(isset($params['forEditProfile'])){
                $questionsSectionsList=$params['form']->getElements();
                if (isset($questionsSectionsList['birthdate'])) {
                    $kidsAge = OW::getConfig()->getValue('frmcontrolkids', 'kidsAge');
                    $params['controller']->assign('display_parent_email', true);
                    $params['controller']->assign('kidsAge', $kidsAge);
                    $js = 'function checkKidsAge(){
                        var kidsAge=' . $kidsAge . ';
                         var userYear =  $(\'[name="year_birthdate"]\').val();
                         var today = new Date();
                         if(typeof(getCookie) == "function" && getCookie("frmjalali")==1){
                             var jalaliDate = gregorian_to_jalali(today.getFullYear(),parseInt(today.getMonth()+1),parseInt(today.getDate()));
                             if(parseInt(jalaliDate[0]) - parseInt(userYear) < kidsAge){
                                var parentEmail_input = document.getElementsByName(\'parentEmail\')[0];
                                var displayError = document.getElementById(parentEmail_input.id+\'_error\');
                                $(".parent_email").show();
                             }
                             else{
                                var parentEmail_input = document.getElementsByName(\'parentEmail\')[0];
                                var displayError = document.getElementById(parentEmail_input.id+\'_error\');
                                displayError.innerHTML="";
                                parentEmail_input.value="";
                                $(".parent_email").hide();
                             }
                         } else{
                              if(parseInt(today.getFullYear()) - parseInt(userYear) < kidsAge){
                                $(".parent_email").show();
                             }
                             else{
                                document.getElementsByName(\'parentEmail\')[0].value="";
                                $(".parent_email").hide();

                             }
                         }
                }
                $(\'[name="year_birthdate"]\').change(checkKidsAge);checkKidsAge();';
                    OW::getDocument()->addOnloadScript($js);
                }
            }else {
                $questionsSectionsList = $params['form']->getSortedQuestionsList();
                foreach ($questionsSectionsList as $question) {
                    if (!$question['fake'] && $question['realName'] == 'birthdate') {
                        $dateFieldName = $question['name'];
                    }
                }
                if ($dateFieldName != null) {
                    $kidsAge = OW::getConfig()->getValue('frmcontrolkids', 'kidsAge');
                    $params['controller']->assign('display_parent_email', true);
                    $params['controller']->assign('kidsAge', $kidsAge);
                    $js = 'function checkKidsAge(){
                        var kidsAge=' . $kidsAge . ';
                         var userYear =  $(\'[name="year_' . $dateFieldName . '"]\').val();
                         var today = new Date();
                         if(typeof(getCookie) == "function" && getCookie("frmjalali")==1){
                             var jalaliDate = gregorian_to_jalali(today.getFullYear(),parseInt(today.getMonth()+1),parseInt(today.getDate()));
                             if(parseInt(jalaliDate[0]) - parseInt(userYear) < kidsAge){
                                var parentEmail_input = document.getElementsByName(\'parentEmail\')[0];
                                var displayError = document.getElementById(parentEmail_input.id+\'_error\');
                                $(".parent_email").show();
                             }
                             else{
                                var parentEmail_input = document.getElementsByName(\'parentEmail\')[0];
                                var displayError = document.getElementById(parentEmail_input.id+\'_error\');
                                displayError.innerHTML="";
                                parentEmail_input.value="";
                                $(".parent_email").hide();
                             }
                         } else{
                              if(parseInt(today.getFullYear()) - parseInt(userYear) < kidsAge){
                                $(".parent_email").show();
                             }
                             else{
                                document.getElementsByName(\'parentEmail\')[0].value="";
                                $(".parent_email").hide();

                             }
                         }
                }
                $(\'[name="year_' . $dateFieldName . '"]\').change(checkKidsAge);checkKidsAge();';
                    OW::getDocument()->addOnloadScript($js);
                }
            }
        }
        if($params['form']->getElement('email')!=null && isset($_REQUEST['parentEmailValue'])){
            $params['form']->getElement('email')->setValue($_REQUEST['parentEmailValue']);
            $params['form']->getElement('email')->addAttribute(FormElement::ATTR_READONLY);
        }
    }

    public function addParentEmailFieldToForm($form, $editUserId = null){
        $parentEmail = new TextField("parentEmail");
        $parentEmail->addValidator(new EmailValidator());
        $parentEmail->addValidator(new RequiredParentEmailValidator());
        $parentEmail->setLabel(OW_Language::getInstance()->text('frmcontrolkids', "join_parent_email_header"));

        if(OW_Session::getInstance()->isKeySet('parentEmailValueError')){
            OW_Session::getInstance()->delete('parentEmailValueError');
            $parentEmail->addError(OW::getLanguage()->text('frmcontrolkids', 'parentEmailEmpty'));
        }
        if(OW::getUser()->isAuthenticated()) {
            if(isset($editUserId)){
                $dto = $this->getParentInfo($editUserId);
            }else{
                $dto = $this->getParentInfo(OW::getUser()->getId());
            }
            if(isset($dto->parentEmail)) {
                $parentEmail->setValue($dto->getParentEmail());
            }
        }
        $form->addElement($parentEmail);
    }

    public function onUserRegistered(OW_Event $event){
        $params = $event->getParams();
        $user = null;
        if( isset($params['userId'])){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
        }
        if(isset($params['forEditProfile'])&& $_REQUEST['parentEmail']==""){
           $this->deleteRelationship($user->id);
        }
        if(isset($_REQUEST['parentEmail']) && $_REQUEST['parentEmail']!="" &&
            isset($params['userId']) && UTIL_Validator::isEmailValid($_REQUEST['parentEmail'])){
            if(isset($params['forEditProfile'])&& isset($params['params']['birthdate'])){
                $birthdate = $params['params']['birthdate'];
            }else {
                $birthdate = BOL_QuestionService::getInstance()->getQuestionData(array($params['userId']), array('birthdate'))[$params['userId']];
            }
            if($this->isInChildhood($birthdate)) {
                $this->addRelationship($params['userId'], $_REQUEST['parentEmail']);
            }
        }
        $email = null;
        if(isset($_REQUEST['email'])){
            $email = $_REQUEST['email'];
        }
        if(!isset($email) && isset($user)){
            $email = $user->getEmail();
        }

        if(isset($email)) {
            $this->updateParentUserIdUsingEmail($email, $params['userId']);
        }
    }
    public function removeUserInformation(OW_Event $event)
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $this->kidsRelationshipDao->removeUserInformation((int) $userId);
        }
    }
}

class RequiredParentEmailValidator extends OW_Validator
{
    /**
     * RequiredParentEmailValidator constructor.
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_required_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        return true;
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
        	    if($('.parent_email') && $('.parent_email')[0] && $('.parent_email')[0].style.display != 'none'){
                    if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                    else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
                }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }


}
