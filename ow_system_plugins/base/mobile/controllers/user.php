<?php
/**
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_User extends OW_MobileActionController
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

    public function signIn()
    {
        $form = $this->userService->getSignInForm();

        if ( !$form->isValid($_POST) )
        {
            $errors = $form->getErrors();
            $errorString = "Error!";
            foreach ($errors as $error){
                if(isset($error[0])){
                    $errorString = $error[0];
                }
            }
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => OW::getRequest()->isAjax(), 'message' => $errorString)));
            exit(json_encode(array('result' => false, 'message' => $errorString)));
        }

        $data = $form->getValues();
        $result = $this->userService->processSignIn($data['identity'], $data['password'], true);

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
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => OW::getRequest()->isAjax(), 'message' => $message)));
            exit(json_encode(array('result' => false, 'message' => $message)));
        }
    }

    public function standardSignIn()
    {
        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException(OW_URL_HOME);
        }

        if(OW::getPluginManager()->isPluginActive('sso')) {
            $redirectUrl = OW::getRouter()->getBaseUrl() . 'sign-in';
            if (isset($_GET['code']) && strlen($_GET['code']) > 100) {
                SSO_BOL_Service::getInstance()->signInByAuthenticationCode($_GET['code'], $redirectUrl);
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
        if ( OW::getRequest()->isPost() )
        {
            $form = $this->userService->getSignInForm();

            if ( !$form->isValid($_POST) )
            {
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => false, 'message' => 'Invalid data submitted!')));
                OW::getFeedback()->error("Error");
                $this->redirect();
            }

            $data = $form->getValues();
            $result = $this->userService->processSignIn($data['identity'], $data['password'], isset($data['remember']));

            $message = '';

            foreach ( $result->getMessages() as $value )
            {
                $message .= $value;
            }

            if ( $result->isValid() )
            {
                OW::getFeedback()->info($message);

                if ( empty($_GET['back-uri']) )
                {
                    $this->redirect();
                }

                $this->redirect(OW::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']));
            }
            else
            {
                OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_USER_AUTH_FAILED, array('ajax' => OW::getRequest()->isAjax(), 'message' => $message)));
                OW::getFeedback()->error($message);
                $this->redirect();
            }
        }

        OW::getDocument()->getMasterPage()->setRButtonData(array('extraString' => ' style="display:none;"'));
        $this->addComponent('signIn', new BASE_MCMP_SignIn(false));

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

    /**
     * 
     * @param array $params
     * @return BOL_User
     * @throws Redirect404Exception
     * @throws RedirectException
     */
    protected function checkProfilePermissions( $params )
    {
        $userService = BOL_UserService::getInstance();
        /* @var $userDto BOL_User */
        $userDto = $userService->findByUsername($params['username']);     

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }
        

        if ( (OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $userDto->id || !OW::getUser()->isAuthenticated()) )
        {
            if (!OW::getUser()->isAuthorized('base', 'view_profile') && !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin()) {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
                $this->assign('permissionMessage', $status['msg']);
                return null;
            }

            if (OW::getUser()->isAuthenticated() && !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin()) {
                $blocked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userDto->id);
                if ($blocked) {
                    $this->assign('permissionMessage', OW::getLanguage()->text('base', 'authorization_failed_feedback'));
                    return null;
                }
            }
        }

        $isSuspended = $userService->isSuspended($userDto->id);
        
        if ( $isSuspended )
        {   
            $this->assign('permissionMessage', OW::getLanguage()->text('base', 'user_page_suspended'));
            return null;
        }
        
        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => OW::getUser()->getId()
        );

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            throw new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $userDto->username)));
        }

        return $userDto;
    }

    public function privacyMyProfileNoPermission( $params )
    {
        $username = $params['username'];

        $user = BOL_UserService::getInstance()->findByUsername($username);
        $suspendStatus = BOL_UserService::getInstance()->findSupsendStatusForUserList(array($user->getId()));

        if ( $user === null || (isset($suspendStatus) && isset($suspendStatus[$user->getId()]) && $suspendStatus[$user->getId()]))
        {
            throw new Redirect404Exception();
        }
        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $user->id,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
            $this->redirect($backUrl);
        }
        catch ( RedirectException $ex )
        {
        }
        if ( OW::getSession()->isKeySet('privacyRedirectExceptionMessage') )
        {
            $this->assign('message', OW::getSession()->get('privacyRedirectExceptionMessage'));
        }

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = OW::getUser()->getId();

        $userId = $user->id;

        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => BOL_UserService::getInstance()->getDisplayName($userId))));
        $this->setPageHeadingIconClass('ow_ic_user');

        $avatar = $avatarService->getAvatarUrl($userId, 2);
        $this->assign('avatar', $avatar);
        $roles = BOL_AuthorizationService::getInstance()->getLastDisplayLabelRoleOfIdList(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $this->assign('username', $username);
        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));
        $cmp = OW::getClassInstance("BASE_MCMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'user_view_privacy_no_permission.html');
    }

    public function profile( $params )
    {
        $userDto = $this->checkProfilePermissions($params);

        if ( $userDto === null )
        {
            return;
        }

        $this->addComponent("header", OW::getClassInstance("BASE_MCMP_ProfileHeader", $userDto));

        //Profile Info
        $this->addComponent("info", OW::getClassInstance("BASE_MCMP_ProfileInfo", $userDto, true));
        $this->addComponent('contentMenu', OW::getClassInstance("BASE_MCMP_ProfileContentMenu", $userDto));
        $this->addComponent('about', OW::getClassInstance("BASE_MCMP_ProfileAbout", $userDto, 80));

        $this->assign("userId", $userDto->id);

        //files
        if(OW::getUser()->isAuthenticated() && $userDto->id == OW::getUser()->getId()){
            if (FRMSecurityProvider::isNewFileManagerEnabledForMobile()){
                $bcw = new BASE_CLASS_WidgetParameter();
                $bcw->additionalParamList = array(
                    'entityId' => $userDto->id,
                    'entity' => 'user'
                );
                $this->addComponent('filesWidget', new FRMFILEMANAGER_CMP_MainWidget($bcw));
            }
        }

        $userService = BOL_UserService::getInstance();
        $displayName = $userService->getDisplayName($userDto->id);

        // page heading
        $event = new OW_Event('base.on_get_user_status', array('userId' => $userDto->id));
        OW::getEventManager()->trigger($event);
        $status = $event->getData();
        $headingSuffix = "";
        if ( !BOL_UserService::getInstance()->isApproved($userDto->id) && OW::getConfig()->getValue('base', 'mandatory_user_approve') == 1)
        {
            $headingSuffix = ' <span class="ow_remark ow_small">(' . OW::getLanguage()->text("base", "pending_approval") . ')</span>';
        }
        if ( $status !== null )
        {
            $heading = OW::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
            $this->setPageHeading($heading . $headingSuffix);
        }
        else
        {
            $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)) . $headingSuffix);
        }
        $this->setPageHeadingIconClass('ow_ic_user');

        $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);

        // set meta info
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userPage",
            "title" => "base+meta_title_user_page",
            "description" => "base+meta_desc_user_page",
            "keywords" => "base+meta_keywords_user_page",
            "vars" => $vars,
            "image" => BOL_AvatarService::getInstance()->getAvatarUrl($userDto->getId(), 2)
        );
        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        //set JSON-LD
        OW::getDocument()->addJSONLD("Person", $displayName, false, $userService->getUserUrl($userDto->getId()), $params['image'],
            [
                "email"=> "mailto:".$userDto->getEmail(),
            ]
        );
    }

    public function profilePicture( $params )
    {
        $userDto = $this->checkProfilePermissions($params);

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }

        $imageUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userDto->id, 2);
        $imageColor = BOL_AvatarService::getInstance()->getAvatarInfo($userDto->id, $imageUrl);
        if ($imageColor['empty']){
            $this->assign('color', $imageColor['color']);
        }
        $this->assign('url', $imageUrl);

    }

    public function about( $params )
    {
        $userDto = $this->checkProfilePermissions($params);

        if ( $userDto === null )
        {
            return;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        $this->setPageHeading(OW::getLanguage()->text('mobile', 'about'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $this->addComponent("header", OW::getClassInstance("BASE_MCMP_ProfileHeader", $userDto, false));

        //Profile Info
        $this->addComponent("info", OW::getClassInstance("BASE_MCMP_ProfileInfo", $userDto));
        $this->addComponent('about', OW::getClassInstance("BASE_MCMP_ProfileAbout", $userDto));
        $this->assign('backUrl', OW::getRouter()->urlForRoute('base_user_profile', array('username' => $userDto->username)));
        $this->assign("userId", $userDto->id);
        OW::getEventManager()->trigger(new OW_Event('frmwidgetplus.general.before.view.render', array('targetPage' => 'userProfile', 'username' => $userDto->username)));
    }

    public function userDeleted()
    {
        
    }

    public function forgotPassword()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
        }

        $this->setPageHeading(OW::getLanguage()->text('base', 'forgot_password_heading'));

        $language = OW::getLanguage();

        $form = $this->userService->getResetForm();

        $event = new OW_Event('base.forgot_password.form_generated',['form'=>$form]);
        OW_EventManager::getInstance()->trigger($event);
        if(isset($event->getData()['form'])) {
            $form = $event->getData()['form'];
        }

        $this->addForm($form);

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
                if($form->getErrors()['email'][0]!=null) {
                    OW::getFeedback()->error($form->getErrors()['email'][0]);
                }
                else {
                    OW::getFeedback()->error($language->text('base', 'form_validate_common_error_message'));
                }
                $this->redirect();
            }
        }

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "forgot_pass",
            "title" => "base+meta_title_forgot_pass",
            "description" => "base+meta_desc_forgot_pass",
            "keywords" => "base+meta_keywords_forgot_pass"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function resetPasswordRequest()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
        }

        $form = $this->userService->getResetPasswordRequestFrom();
        $this->addForm($form);
        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_request_heading'));

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
    }

    public function resetPassword( $params )
    {
        $language = OW::getLanguage();

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
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

        $this->assign('formText', $language->text('base', 'reset_password_form_text', array('realname' => $user->getUsername())));

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
    }

    public function resetPasswordCodeExpired()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_code_expired_cap_label'));        
        $this->assign('text', OW::getLanguage()->text('base', 'reset_password_code_expired_text', array('url' => OW::getRouter()->urlForRoute('base_forgot_password'))));        
    }
}

