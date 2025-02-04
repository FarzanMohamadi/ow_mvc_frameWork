<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip.bol
 * @since 1.0
 */
class FRMBLOCKINGIP_BOL_Service
{
    CONST TRY_COUNT_BLOCK = 'try_count_block';
    CONST TRY_COUNT_CAPTCHA = 'try_count_captcha';
    CONST EXPIRE_TIME = 'expire_time';
    
    CONST CATCH_REQUESTS_KEY = 'frmblockingip.catch';
    
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $blockIpDao;
    
    private function __construct()
    {
        $this->blockIpDao = FRMBLOCKINGIP_BOL_BlockIpDao::getInstance();
    }
    
    public function bruteforceTrack()
    {
        $this->blockIpDao->addBlockIp();
    }
    
    public function isLocked()
    {
        return $this->blockIpDao->isLocked();
    }

    /**
     * @return FRMBLOCKINGIP_BOL_BlockIp
     */
    public function getCurrentUser()
    {
        return $this->blockIpDao->getCurrentUser();
    }
    
    public function deleteBlockIp()
    {
        return $this->blockIpDao->deleteBlockIp();
    }

    public function deleteBlockCurrentIp()
    {
        return $this->blockIpDao->deleteBlockCurrentIp();
    }

    public function getUserTryCount(){

        return $this->blockIpDao->getUserTryCount();
    }

    public function getCurrentIP(){
        $ip = OW::getRequest()->getRemoteAddress();
        if($ip == '::1' || empty($ip)){
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    public function onUserLogin(OW_Event $event){
        $this->deleteBlockCurrentIp();
    }

    public function onAfterSigninFormCreated( OW_Event $event )
    {
        $params = $event->getParams();
        if($params['form']) {
            $showCaptcha = OW::getConfig()->getValue('frmblockingip', 'loginCaptcha') && $this->getUserTryCount() >= OW::getConfig()->getValue('frmblockingip', 'try_count_captcha');
            if($showCaptcha){ //When user's authentication failed, captcha field should filled in sign-in page.
                $fieldCaptcha = new CaptchaField('captchaField');
                $fieldCaptcha->setLabel(OW::getLanguage()->text('base', 'captcha_value'));
                $params['form']->addElement($fieldCaptcha);
            }
        }
    }

    public function onUserCaptchaValidateFailed( OW_Event $event )
    {
        $this->bruteforceTrack();
        if (OW::getConfig()->getValue('frmblockingip', 'block') && $this->isLocked()){
            exit(json_encode(array('result' => false, 'lock' => true)));
        }
    }

    public function onUserAuthFailed(OW_Event $event){
        $params = $event->getParams();
        $this->bruteforceTrack();
        $showCaptcha = OW::getConfig()->getValue('frmblockingip', 'loginCaptcha') && $this->getUserTryCount() >= OW::getConfig()->getValue('frmblockingip', 'try_count_captcha');

        if($params['ajax']) {
            if (OW::getConfig()->getValue('frmblockingip', 'block') && $this->isLocked()){
                exit(json_encode(array('result' => false, 'message' => $params['message'], 'lock' => true)));
            }else if($showCaptcha){
                exit(json_encode(array('result' => false, 'message' => $params['message'], 'captcha' => true)));
            }
        }else if (OW::getConfig()->getValue('frmblockingip', 'block') && $this->isLocked() ){
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmblockingip.authenticate_fail'));
        }
    }
    public function onBeforeFormSigninMobileRender(OW_Event $event)
    {
        $params = $event->getParams();

        $showCaptcha = OW::getConfig()->getValue('frmblockingip', 'loginCaptcha') && $this->getUserTryCount() >= OW::getConfig()->getValue('frmblockingip', 'try_count_captcha');
        if(isset($params['BASE_CMP_SignIn'])){
            $params['BASE_CMP_SignIn']->assign('display_login_captcha', $showCaptcha);
        }
        if(isset($params['form'])) {
            if($showCaptcha){
                $fieldCaptcha = new CaptchaField('captchaField');
                $fieldCaptcha->setLabel(OW::getLanguage()->text('base', 'captcha_value'));
                $params['form']->addElement($fieldCaptcha);
            }
        }
    }
    public function onBeforeFormSigninRender(OW_Event $event){
        $params = $event->getParams();

        $showCaptcha = OW::getConfig()->getValue('frmblockingip', 'loginCaptcha') && $this->getUserTryCount() >= OW::getConfig()->getValue('frmblockingip', 'try_count_captcha');
        $params['BASE_CMP_SignIn']->assign('display_login_captcha', $showCaptcha);

        if($params['ajax']){
            if ( OW::getApplication()->getContext() == OW_Application::CONTEXT_MOBILE )
            {
                $reload_signInJS = "function(data){if(data.lock){window.location.reload();}else if( data.captcha ){setTimeout(function(){OW.loadComponent(\"BASE_MCMP_SignIn\", {ajax: true, reload: true},{onReady: function( html ){ $(\"form[name='sign-in']\").remove(); $(\"section[class='owm_sidebar_right_cont']\").prepend(html[0]);}});}, 200);}}";
            }else{
                $reload_signInJS = "function(data){if(data.lock){window.location.reload();}else if( data.captcha ){setTimeout(function(){OW.loadComponent(\"BASE_CMP_SignIn\", {ajax: true, reload: true},{onReady: function( html ){ $('#base_cmp_floatbox_ajax_signin').empty().html(html);}});}, 200);}}";
            }
            $params['form']->bindJsFunction(Form::BIND_SUCCESS, $reload_signInJS);
        }
    }

    public function onAfterRoute( OW_Event $event )
    {
        $checkUriEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CHECK_URI_REQUEST));
        if(isset($checkUriEvent->getData()['ignore']) && $checkUriEvent->getData()['ignore']){
            return;
        }
        if (!OW::getConfig()->getValue('frmblockingip', 'block') || OW::getUser()->isAuthenticated() || !$this->isLocked())
        {
            return;
        }

        if ( OW::getApplication()->getContext() == OW_Application::CONTEXT_MOBILE )
        {
            OW::getRequestHandler()->setCatchAllRequestsAttributes(FRMBLOCKINGIP_BOL_Service::CATCH_REQUESTS_KEY, array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMBLOCKINGIP_MCTRL_Iisblockingip',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMBLOCKINGIP_BOL_Service::CATCH_REQUESTS_KEY, 'FRMBLOCKINGIP_MCTRL_Iisblockingip', 'index');
        }else{
            OW::getRequestHandler()->setCatchAllRequestsAttributes(FRMBLOCKINGIP_BOL_Service::CATCH_REQUESTS_KEY, array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMBLOCKINGIP_CTRL_Iisblockingip',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
            OW::getRequestHandler()->addCatchAllRequestsExclude(FRMBLOCKINGIP_BOL_Service::CATCH_REQUESTS_KEY, 'FRMBLOCKINGIP_CTRL_Iisblockingip', 'index');
        }

    }

    public function onTrackAttempt( OW_Event $event )
    {
        $this->bruteforceTrack();
    }

    public function catchAllRequestsExceptions( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getApplication()->getContext() == OW_Application::CONTEXT_MOBILE ) {
            $event->add(array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMBLOCKINGIP_MCTRL_Iisblockingip',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
        }else{
            $event->add(array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMBLOCKINGIP_CTRL_Iisblockingip',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
        }
    }

    public function onWebServiceLoginAttempt(OW_Event $event){
        if (OW::getConfig()->getValue('frmblockingip', 'block') && $this->isLocked()){
            $event->setData(array('lock' => true));
        }
    }
    public function onWebServiceLoginSuccess(OW_Event $event){
        $this->deleteBlockCurrentIp();
    }
    public function onWebServiceLoginFailed(OW_Event $event){
        $this->bruteforceTrack();
    }
}
