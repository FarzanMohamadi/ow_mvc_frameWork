<?php
class FRMMOBILEACCOUNT_CTRL_Account extends OW_ActionController
{

    public function index($params)
    {
    }

    public function join($param){
        if(OW::getUser()->isAuthenticated()){
            $this->redirect(OW_URL_HOME);
        }

        $code = null;

        if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            if(!isset($param['code'])){
                $this->redirect('404');
            }else{
                $code = $param['code'];
                $info = BOL_UserService::getInstance()->findInvitationInfo($code);
                if ( $info == null )
                {
                    $this->redirect('404');
                }
            }
        }

        $mobileNumberPosted = null;
        $usernamePosted = null;
        $emailPosted = null;
        if(isset($param['mobile_number'])){
            $mobileNumberPosted = $param['mobile_number'];
            $mobileNumberPosted = urldecode($mobileNumberPosted);
            $mobileNumberPosted = rawurldecode($mobileNumberPosted);
        }

        if(isset($param['username'])){
            $usernamePosted = $param['username'];
            $usernamePosted = urldecode($usernamePosted);
            $usernamePosted = urldecode($usernamePosted);
        }

        if(isset($param['email'])){
            $emailPosted = $param['email'];
            $emailPosted = urldecode($emailPosted);
            $emailPosted = urldecode($emailPosted);
        }

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            //nothing to do
        }else{
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
        }

        $service = FRMMOBILEACCOUNT_BOL_Service::getInstance();
        if(!$service->checkChangeLoginUrl()){
            $this->redirect(OW_URL_HOME);
        }

        $form = $service->getJoinForm($usernamePosted, $mobileNumberPosted, $emailPosted);
        $this->addForm($form);
        
        $service->processJoinForm($code);

        $service->addStaticFilesToDocument();

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->assign('type_class', 'mobile_version');
        }else{
            $this->assign('type_class', 'desktop_version');
        }

        $this->assign('backUrl', OW::getRouter()->urlForRoute('frmmobileaccount.login'));
    }

    public function login($param){
        if(OW::getUser()->isAuthenticated()){
            $this->redirect(OW_URL_HOME);
        }

        $mobileNumberPosted = null;
        $usernamePosted = null;
        if(isset($param['mobile_number'])){
            $mobileNumberPosted = $param['mobile_number'];
            $mobileNumberPosted = urldecode($mobileNumberPosted);
            $mobileNumberPosted = rawurldecode($mobileNumberPosted);
        }else if(isset($param['username'])){
            $usernamePosted = $param['username'];
            $usernamePosted = urldecode($usernamePosted);
            $usernamePosted = urldecode($usernamePosted);
            OW::getDocument()->addOnloadScript('showLoginWithUsernameTab();');
        }

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            //nothing to do
        }else{
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
        }

        $service = FRMMOBILEACCOUNT_BOL_Service::getInstance();
        if(!$service->checkChangeLoginUrl()){
            $this->redirect(OW_URL_HOME);
        }

        $getLoginSmsForm = $service->getLoginSmsForm($mobileNumberPosted);
        $this->addForm($getLoginSmsForm);


        $getLoginUsernamePasswordForm = $service->getLoginUsernamePasswordForm($usernamePosted);
        OW::getEventManager()->trigger(new OW_Event("frmmobileaccount.before_sign_in_render", array('form' => $getLoginUsernamePasswordForm,'BASE_CMP_SignIn' => $this)));
        $this->addForm($getLoginUsernamePasswordForm);

        $service->processLoginForm();
        $this->assign('forgotPasswordnUrl', OW::getRouter()->urlForRoute('base_forgot_password'));
        $service->addStaticFilesToDocument();

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->assign('type_class', 'mobile_version');
        }else{
            $this->assign('type_class', 'desktop_version');
        }

        $isNewTheme = FRMSecurityProvider::themeCoreDetector() ? true : false;
        $this->assign("isNewTemplate", $isNewTheme);

        $this->setDocumentKey("base_sign_in login_mobile_account");
    }

    public function code($param){
        if(OW::getUser()->isAuthenticated()){
            $this->redirect(OW_URL_HOME);
        }

        $mobileNumber = null;
        if(!isset($param['mobileNumber'])){
            $this->redirect('404');
        }else{
            $mobileNumber = $param['mobileNumber'];
        }

        $username = null;
        if(isset($param['username'])){
            $username = $param['username'];
        }

        $email = null;
        if(isset($param['email'])){
            $email = $param['email'];
            $email = urldecode($email);
        }

        $code = null;
        if(isset($param['code'])){
            $code = $param['code'];
        }

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            //nothing to do
        }else{
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
        }

        $service = FRMMOBILEACCOUNT_BOL_Service::getInstance();
        if(!$service->checkChangeLoginUrl()){
            $this->redirect(OW_URL_HOME);
        }

        $form = $service->getCodeForm($mobileNumber, $username, $email, $code);
        $service->processCodeForm($mobileNumber, $username, $email, $code);
        $this->addForm($form);

        $resendUrl = OW::getRouter()->urlForRoute('frmmobileaccount.resend', array('mobileNumber' => $param['mobileNumber']));
        $this->assign('resend_function', "resendMobileCodeAccount('".$resendUrl."')");

        $this->assign('backUrl', OW::getRouter()->urlForRoute('frmmobileaccount.login'));

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->assign('type_class', 'mobile_version');
        }else{
            $this->assign('type_class', 'desktop_version');
        }

        $isNewTheme = FRMSecurityProvider::themeCoreDetector() ? true : false;
        $this->assign("isNewTemplate", $isNewTheme);

        $service->addStaticFilesToDocument();
        $this->setDocumentKey("base_sign_in login_mobile_account login_mobile_account_code_verify");
    }

    public function resendCode($param){
        if(OW::getUser()->isAuthenticated()){
            $this->redirect(OW_URL_HOME);
        }

        if(!isset($param['mobileNumber']) || !OW::getRequest()->isAjax()){
            exit(json_encode(array('valid' => 'false')));
        }

        $service = FRMMOBILEACCOUNT_BOL_Service::getInstance();
        if(!$service->checkChangeLoginUrl()){
            $this->redirect(OW_URL_HOME);
        }
        $frmsmsEvent = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.check.request.time.interval',['mobileNumber'=>$param['mobileNumber']]));
        if(isset($frmsmsEvent->getData()['validTimeInterval']) && !$frmsmsEvent->getData()['validTimeInterval'])
        {
            $valid=false;
            $message = $frmsmsEvent->getData()['errorMessage'];
            exit(json_encode(array('valid' => $valid,'message'=>$message)));
        }
        $service->handleBruteForce();
        $service->resendCode($param['mobileNumber']);
        exit(json_encode(array('valid' => 'true')));
    }
}