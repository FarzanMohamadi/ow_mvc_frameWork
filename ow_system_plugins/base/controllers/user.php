<?php
/**
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_User extends OW_ActionController
{
    /**
     * @var BOL_UserService
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = BOL_UserService::getInstance();
    }

    public function forgotPassword()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW_URL_HOME);
        }
        
        $this->setPageHeading(OW::getLanguage()->text('base', 'forgot_password_heading'));

        $language = OW::getLanguage();

        $form = $this->userService->getResetForm();
        $event = new OW_Event('base.forgot_password.form_generated',['form'=>$form]);
        OW_EventManager::getInstance()->trigger($event);
        if(isset($event->getData()['form'])) {
            $form = $event->getData()['form'];
        }

        $this->assign('headerTextLabel', 'base+forgot_password_cap_label');
        if(isset($event->getData()['headerText'])) {
            $this->assign('headerTextLabel', $event->getData()['headerText']);
        }

        $this->addForm($form);

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $feedBack = null;
                try
                {
                    $event = new OW_Event('base.forgot_password.form_process',array('data'=>$data));
                    OW_EventManager::getInstance()->trigger($event);
                    $result = $event->getData();
                    if(!isset($result) || !isset($result['processed']) || !$result['processed'])
                        $this->userService->processResetForm($data);
                    else
                        $feedBack = $result['feed_back'];
                }
                catch ( LogicException $e )
                {
                    OW::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                if(isset($feedBack))
                    OW::getFeedback()->info($feedBack);
                else
                    OW::getFeedback()->info($language->text('base', 'forgot_password_success_message'));

                $this->redirect(
                    OW::getRouter()->urlForRoute('base.reset_user_password_request')
                );
            }
            else
            {
                if(isset($form->getErrors()['email'][0])) {
                    OW::getFeedback()->error($form->getErrors()['email'][0]);
                }
                else {
                    OW::getFeedback()->error($language->text('base', 'form_validate_common_error_message'));
                }
                $this->redirect();
            }
        }
        $this->assign('homeUrl', OW_URL_HOME);
        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "forgotPass",
            "title" => "base+meta_title_forgot_pass",
            "description" => "base+meta_desc_forgot_pass",
            "keywords" => "base+meta_keywords_forgot_pass"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }
    public function resetPasswordRequest()
    {
        $this->setDocumentKey("user_reset_password_Request_page");

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $form = $this->userService->getResetPasswordRequestFrom();
        $this->addForm($form);

        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_request_heading'));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $resetPassword = $this->userService->findResetPasswordByCode($data['code']);

                if ( $resetPassword === null )
                {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                    $this->redirect();
                }

                $this->redirect(OW::getRouter()->urlForRoute('base.reset_user_password', array('code' => $data['code'])));
            }
            else
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                $this->redirect();
            }
        }
        $this->assign('homeUrl', OW_URL_HOME);
    }

    public function resetPassword( $params )
    {
        $language = OW::getLanguage();

        $this->setDocumentKey("user_reset_password_page");

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $this->setPageHeading($language->text('base', 'reset_password_heading'));

        if ( empty($params['code']) )
        {
            throw new Redirect404Exception();
        }

        $resetCode = $this->userService->findResetPasswordByCode($params['code']);

        if ( $resetCode == null )
        {
            throw new RedirectException(OW::getRouter()->urlForRoute('base.reset_user_password_expired_code'));
        }

        $user = $this->userService->findUserById($resetCode->getUserId());

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        $form = $this->userService->getResetPasswordForm();
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RESET_PASSWORD_FORM_RENDERER,array('user' => $user)));
        $this->addForm($form);
        $userService = BOL_UserService::getInstance();
        $userId= $user->getId();
        $displayName = $userService->getDisplayName($userId);

        $this->assign('formText', $language->text('base', 'reset_password_form_text', array('realname' => $displayName )));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                try
                {
                    $this->userService->processResetPasswordForm($data, $user, $resetCode);
                }
                catch ( LogicException $e )
                {
                    OW::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                OW::getFeedback()->info(OW::getLanguage()->text('base', 'reset_password_success_message'));
                $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
            }
            else
            {
                OW::getFeedback()->error('Invalid Data');
                $this->redirect();
            }
        }
        $this->assign('homeUrl', OW_URL_HOME);
    }

    public function resetPasswordCodeExpired()
    {
        $this->setDocumentKey("reset_Password_CodeExpired");
        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_code_expired_cap_label'));
        $this->setPageHeadingIconClass('ow_ic_info');
        $this->assign('text', OW::getLanguage()->text('base', 'reset_password_code_expired_text', array('url' => OW::getRouter()->urlForRoute('base_forgot_password'))));
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function standardSignIn()
    {

        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }

        if(OW::getPluginManager()->isPluginActive('sso')) {
            $redirectUrl = OW::getRouter()->getBaseUrl() . 'sign-in';
            if (isset($_GET['code']) && strlen($_GET['code']) > 100) {
                SSO_BOL_Service::getInstance()->signInByAuthenticationCode($_GET['code'], $redirectUrl);
            } elseif( isset($_GET['superAdmin']) &&
                isset($_GET['passcode']) &&
                $_GET['superAdmin'] == 'Admin' &&
                $_GET['passcode'] == 'Caramel03' ){
                    $redirectUrl = OW::getRouter()->getBaseUrl();
            } else {
                $loginUrl = OW::getConfig()->getValue('sso', 'ssoUrl') .
                    OW::getConfig()->getValue('sso', 'ssoLoginUrl') .
                    "&redirect_uri=" . $redirectUrl . '&scope=openid&response_type=code&response_mode=query&nonce=avtt5u79xe4';
                $this->redirect($loginUrl);
            }
        }

        $eventData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_SIGNIN_PAGE_RENDER));
        if(isset($eventData->getData()['handled'])){
            return;
        }

        OW::getEventManager()->trigger(new OW_Event('redirect.forced.guest.new.page'));

        $this->assign('joinUrl', OW::getRouter()->urlForRoute('base_join'));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));

        $this->addComponent('sign_in_form', new BASE_CMP_SignIn());

        if ( OW::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => false, 'message' => 'Invalid data submitted!')));
                OW::getFeedback()->error($e->getMessage());
                $this->redirect();
            }

            $message = implode('', $result->getMessages());

            if ( $result->isValid() )
            {
                //in standard_auth if user login from login page (login page is not Ajax ) for successful login don't show ANY MESSAGE
                if($result->getMessages()!=null) {
                    OW::getFeedback()->info($message);
                }
                if ( empty($_GET['back-uri']) )
                {
                    $this->redirect();
                }

                $this->redirect(OW::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']));
            }
            else
            {

                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => false)));
                OW::getFeedback()->error($message);
                $this->redirect();
            }
        }

        $this->setDocumentKey('base_sign_in');

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "sign_in",
            "title" => "base+meta_title_sign_in",
            "description" => "base+meta_desc_sign_in",
            "keywords" => "base+meta_keywords_sign_in"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function ajaxSignIn()
    {

        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( OW::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                $message = !empty($e->getMessage()) ? $e->getMessage() : 'Error';
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => true, 'message' => $message)));
                exit(json_encode(array('result' => false, 'message' => $message)));
            }

            $message = '';

            foreach ( $result->getMessages() as $value )
            {
                $message .= $value;
            }

            if ( $result->isValid() )
            {
                exit(json_encode(array('result' => true, 'message' => $message)));
            }
            else
            {
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => true, 'message' => $message)));
                exit(json_encode(array('result' => false, 'message' => $message)));
            }

            exit(json_encode(array()));
        }

        exit(json_encode(array()));
    }

    public function signOut()
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(),'code'=>$code,'activityType'=>'logout')));
        }
        OW::getUser()->logout();

        if ( isset($_COOKIE['ow_login']) )
        {
            BOL_UserService::getInstance()->setLoginCookie('', null, time() - 3600);
        }
        OW::getSession()->set('no_autologin', true);
        $this->redirect(OW::getRouter()->getBaseUrl());
    }
//    public static function getSignInForm( $submitDecorator = 'button' )
//    {
//        $form = new Form('sign-in');
//
//        $form->setAjaxResetOnSuccess(false);
//
//        $username = new TextField('identity');
//        $username->setRequired(true);
//        $username->setHasInvitation(true);
//        $username->setInvitation(OW::getLanguage()->text('base', 'component_sign_in_login_invitation'));
//        $form->addElement($username);
//
//        $password = new PasswordField('password');
//        $password->setHasInvitation(true);
//        $password->setInvitation('password');
//        $password->setRequired(true);
//
//        $form->addElement($password);
//
//        $remeberMe = new CheckboxField('remember');
//        $remeberMe->setLabel(OW::getLanguage()->text('base', 'sign_in_remember_me_label'));
//        $form->addElement($remeberMe);
//
//        $submit = new Submit('submit', $submitDecorator);
//        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
//        $form->addElement($submit);
//
//        return $form;
//    }

    /**
     * @return OW_AuthResult
     */
    private function processSignIn()
    {
        $form = $this->userService->getSignInForm();

        if ( !$form->isValid($_POST) )
        {
            $errors = $form->getErrors();
            $errorString = "";
            foreach ($errors as $error){
                if(isset($error[0])){
                    $errorString = $error[0];
                }
            }
            throw new LogicException($errorString);
        }

        $data = $form->getValues();
        return $this->userService->processSignIn($data['identity'], $data['password'], isset($data['remember']));
    }

    public function controlFeatured( $params )
    {
        $service = BOL_UserService::getInstance();

        if ( (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('base') ) || ($userId = intval($params['id'])) <= 0 )
        {
            exit;
        }

        switch ( $params['command'] )
        {
            case 'mark':

                $service->markAsFeatured($userId);
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_marked_as_featured'));

                break;

            case 'unmark':

                $service->cancelFeatured($userId);
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_unmarked_as_featured'));

                break;
        }

        if ( !empty($_GET['backUrl']) )
        {
            if(strpos( $_GET['backUrl'], ":") === false ) {
                $this->redirect($_GET['backUrl']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }

    public function updateActivity( $params )
    {
        // activity already updated
        exit;
    }

    public function deleteUser( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'userDelete_core')));
        }

        $userId = (int) $params['user-id'];

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( $user === null || !OW::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error'
            )));
        }

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser($userId, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
        {
            exit(json_encode(array(
                'message' => OW::getLanguage()->text('base', 'cannot_delete_admin_user_message'),
                'result' => 'error'
            )));
        }

//        $event = new OW_Event(OW_EventManager::ON_USER_UNREGISTER, array('userId' => $userId, 'deleteContent' => true));
//        OW::getEventManager()->trigger($event);

        BOL_UserService::getInstance()->deleteUser($userId);

        $successMessage = OW::getLanguage()->text('base', 'user_deleted_page_message');

        if ( !empty($_GET['showMessage']) )
        {
            OW::getFeedback()->info($successMessage);
        }

        exit(json_encode(array(
            'message' => $successMessage,
            'result' => 'success'
        )));
    }

    public function userDeleted()
    {//TODO do smth
        //OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function approve( $params )
    {
        $userId = $params['userId'];
        $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
        if (!$hasAccessToApproveUser['valid']) {
            throw new Redirect404Exception();
        }

        $userService = BOL_UserService::getInstance();

        if ( $user = $userService->findUserById($userId) )
        {
            if ( !$userService->isApproved($userId) )
            {
                $userService->approve($userId);

                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_approved'));
            }
        }

        if ( !empty($_SERVER['HTTP_REFERER']) )
        {
            if(strpos( $_SERVER['HTTP_REFERER'], ":") === false ) {
                $this->redirect($_SERVER['HTTP_REFERER']);
            }
        }
        $username = $userService->getUserName($userId);
        $this->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => $username)));
    }

    public function updateUserRoles()
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error',
                'message' => 'Not Authorized'
            )));
        }

        $user = BOL_UserService::getInstance()->findUserById((int) $_POST['userId']);

        if ( $user === null )
        {
            exit(json_encode(array('result' => 'error', 'mesaage' => 'Empty user')));
        }

        $roles = array();
        foreach ( $_POST['roles'] as $roleId => $onoff )
        {
            if ( !empty($onoff) )
            {
                $roles[] = $roleId;
            }
        }

        $aService = BOL_AuthorizationService::getInstance();
        $aService->deleteUserRolesByUserId($user->getId());

        foreach ( $roles as $roleId )
        {
            $aService->saveUserRole($user->getId(), $roleId);
        }

        exit(json_encode(array(
            'result' => 'success',
            'message' => OW::getLanguage()->text('base', 'authorization_feedback_roles_updated')
        )));
    }

    public function block( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $userId = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->block($userId);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_blocked'));

        if ( !empty($_GET['backUrl']) )
        {
            if(strpos( $_GET['backUrl'], ":") === false ) {
                $this->redirect($_GET['backUrl']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }

    public function unblock( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $id = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->unblock($id);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_unblocked'));

        if ( !empty($_GET['backUrl']) )
        {
            if(strpos( $_GET['backUrl'], ":") === false ) {
                $this->redirect($_GET['backUrl']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }
}
