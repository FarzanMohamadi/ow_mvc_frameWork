<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordchangeinterval.controllers
 * @since 1.0
 */
class FRMPASSWORDCHANGEINTERVAL_CTRL_Iispasswordchangeinterval extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
    }

    /**
     * @param null $params
     */
    public function index($params = NULL)
    {
        $this->setDocumentKey("password_change_interval");
        if (!OW::getUser()->isAuthenticated() || OW::getUser()->isAdmin()) {
            $this->redirect(OW_URL_HOME);
        }

        $passwordValidation = $this->service->getCurrentUser();
        if ($passwordValidation != null && !$passwordValidation->valid) {
            $this->assign('password_is_invalid', true);
            if ($this->service->isTokenExpired($passwordValidation->tokenTime)) {
                $this->assign('token_is_expired', true);
                $this->assign('resendEmailChangePasswordUrl', OW::getRouter()->urlForRoute('frmpasswordchangeinterval.resend-link-generate-token', array('userId' => $passwordValidation->userId)));
            } else {
                OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));

                $this->assign('token_is_expired', false);
                $code='';
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>rand(1,10000),'isPermanent'=>true,'activityType'=>'resend_passwordLink')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])){
                    $code = $frmSecuritymanagerEvent->getData()['code'];
                }
                $this->assign('resendEmailChangePasswordUrl', OW::getRequest()->buildUrlQueryString(
                    OW::getRouter()->urlForRoute('frmpasswordchangeinterval.resend-link'),array('code'=>$code)));
            }
        } else if ($passwordValidation != null && $passwordValidation->valid && !$this->service->isChangable($passwordValidation)) {
            $this->redirect(OW_URL_HOME);
        } else {
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
            if($passwordValidation==null){
                $this->service->updateTimePasswordChanged(OW::getUser()->getUserObject()->getJoinStamp());
            }
            $changePassword = BOL_UserService::getInstance()->getResetPasswordForm('change-user-password', true);
            $changePassword->setAction(OW::getRouter()->urlForRoute('frmpasswordchangeinterval.change-user-password-with-userId', array('userId' => 'me')));
            $changePassword->bindJsFunction(Form::BIND_SUCCESS, "function( json ){if( json.result ){window.location.reload();}} ");
            $this->addForm($changePassword);
            $this->assign('formText', OW::getLanguage()->text('frmpasswordchangeinterval', 'reset_password_form_text'));
            $this->assign('password_is_invalid', false);

            $this->setPageTitle(OW::getLanguage()->text('frmpasswordchangeinterval', 'title_change_password'));
            $this->setPageHeading(OW::getLanguage()->text('frmpasswordchangeinterval', 'title_change_password'));

            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('dndindex');
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
        }
    }


    public function logoutAndGoToForgotPassword()
    {
        OW::getUser()->logout();
        $this->redirect(OW::getRouter()->uriForRoute( 'base_forgot_password'));
    }

    /**
     * @throws Redirect404Exception
     */
    public function resendlLink()
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'resend_passwordLink')));
        }
        $passwordValidation = $this->service->getCurrentUser();
        $user = BOL_UserService::getInstance()->findUserById($passwordValidation->getUserId());
        if ($this->service->isTokenExpired($passwordValidation->tokenTime)) {
            $this->service->resendLinkToUserByUserId(true, $user->id);
        } else {
            $this->service->resendLinkToUserByUserId(false, $user->id);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmpasswordchangeinterval', 'change_password_link_sent'));
        $this->redirect(OW_URL_HOME);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function resendlLinkGenerateToken($params)
    {
        $userId = $params['userId'];
        if ($userId == null) {
            throw new Redirect404Exception();
        } else {
            $passwordValidation = $this->service->getUserByUserId($userId);
            if ($passwordValidation == null) {
                throw new Redirect404Exception();
            } else if ($passwordValidation->valid) {
                $this->redirect(OW_URL_HOME);
            } else {
                if ($this->service->isTokenExpired($passwordValidation->tokenTime)) {
                    $this->service->resendLinkToUserByUserId(true, $userId);
                } else {
                    $this->service->resendLinkToUserByUserId(false, $userId);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmpasswordchangeinterval', 'change_password_link_sent'));
                $this->redirect(OW_URL_HOME);
            }
        }
    }

    /**
     * @param $params
     * @throws AuthenticateException
     */
    public function changeUserPassword($params)
    {
        if ($params['token']) {
            $passwordValidationByToken = $this->service->getUserByToken($params['token']);
            $password = $_POST['password'];
            $repeatPassword = $_POST['repeatPassword'];

            $redirectUrl = OW::getRouter()->urlForRoute('frmpasswordchangeinterval.check-validate-password', array('token' => $params['token']));

            if ($passwordValidationByToken == null) {
                throw new AuthenticateException();
            } else if ($password == null) {
                $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_empty'), $redirectUrl);
            } else if ($repeatPassword == null) {
                $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'repeated_password_is_empty'), $redirectUrl);
            } else if ($password != $repeatPassword) {
                $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_not_equal_with_repeated_password'), $redirectUrl);
            }

            $user = BOL_UserService::getInstance()->findUserById($passwordValidationByToken->userId);
            if (BOL_UserService::getInstance()->hashPassword($password,$user->id) == $user->password) {
                $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_equal_with_old_password'), $redirectUrl);
            }

            $form = BOL_UserService::getInstance()->getResetPasswordForm('change-user-password');
            $form->getElement('password')->addValidator(new NewPasswordValidator());
            if (!$form->isValid($_POST)) {
                $errors = $form->getElement('password')->getErrors();
                if (sizeof($errors) > 0) {
                    $this->setErrorAndRedirectToCheckValidatePassword($errors[0], $redirectUrl);
                } else {
                    $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_not_secure'), $redirectUrl);
                }
            } else {
                BOL_UserService::getInstance()->updatePassword($user->getId(), $password);
                $this->redirect(OW_URL_HOME);
            }

        } else {
            throw new AuthenticateException();
        }
    }

    /**
     * @param $params
     * @throws AuthenticateException
     */
    public function changeUserPasswordWithUserId($params)
    {
        if(!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }

        $userId = OW::getUser()->getId();
        $passwordValidationByToken = $this->service->getUserByUserId($userId);
        $currentPassword = $_POST['currentPassword'];
        $password = $_POST['password'];
        $repeatPassword = $_POST['repeatPassword'];
        $redirectUrl = OW::getRouter()->urlForRoute('frmpasswordchangeinterval.change-password');

        if ($passwordValidationByToken == null) {
            throw new AuthenticateException();
        } else if ($password == null) {
            $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_empty'), $redirectUrl);
        } else if ($repeatPassword == null) {
            $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'repeated_password_is_empty'), $redirectUrl);
        } else if ($password != $repeatPassword) {
            $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_not_equal_with_repeated_password'), $redirectUrl);
        }

        $user = BOL_UserService::getInstance()->findUserById($passwordValidationByToken->userId);
        if (BOL_UserService::getInstance()->hashPassword($currentPassword, $user->id) != $user->password) {
            $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('base', 'password_protection_error_message'), $redirectUrl);
        } else if (BOL_UserService::getInstance()->hashPassword($password,$user->id) == $user->password) {
            $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_equal_with_old_password'), $redirectUrl);
        }

        $form = BOL_UserService::getInstance()->getResetPasswordForm('change-user-password');
        $form->getElement('password')->addValidator(new NewPasswordValidator());
        if (!$form->isValid($_POST)) {
            $errors = $form->getElement('password')->getErrors();
            if (sizeof($errors) > 0) {
                $this->setErrorAndRedirectToCheckValidatePassword($errors[0], $redirectUrl);
            } else {
                $this->setErrorAndRedirectToCheckValidatePassword(OW::getLanguage()->text('frmpasswordchangeinterval', 'password_is_not_secure'), $redirectUrl);
            }
        } else {
            BOL_UserService::getInstance()->updatePassword($user->getId(), $password);
            $this->redirect(OW_URL_HOME);
        }
    }

    /**
     * @param $msg
     * @param $redirectUrl
     */
    public function setErrorAndRedirectToCheckValidatePassword($msg, $redirectUrl)
    {
        OW::getFeedback()->error($msg);
        $this->redirect($redirectUrl);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function invalidPassword($params)
    {
        $userId = $params['userId'];
        $passwordValidation = $this->service->getUserByUserId($userId);
        if ($passwordValidation == null) {
            throw new Redirect404Exception();
        } else {
            $this->assign('token_is_expired', $this->service->isTokenExpired($passwordValidation->tokenTime));
            $this->assign('resendEmailChangePasswordUrl', OW::getRouter()->urlForRoute('frmpasswordchangeinterval.resend-link-generate-token', array('userId' => $userId)));
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function checkValidatePassword($params)
    {
        $passwordValidation = $this->service->getUserByToken($params['token']);
        if ($passwordValidation == null) {
            throw new Redirect404Exception();
        } else if ($passwordValidation->valid) {
            $this->redirect(OW_URL_HOME);
        } else {
            $isTokenExpired = $this->service->isTokenExpired($passwordValidation->tokenTime);
            if ($isTokenExpired) {
                $this->assign('token_is_expired', true);
                $this->assign('resendEmailChangePasswordUrl', OW::getRouter()->urlForRoute('frmpasswordchangeinterval.resend-link-generate-token', array('userId' => $passwordValidation->userId)));
            } else {
                $user = BOL_UserService::getInstance()->findUserById($passwordValidation->userId);
                $this->assign('token_is_expired', false);
                $this->assign('formText', OW::getLanguage()->text('base', 'reset_password_form_text', array('realname' => $user->username)));
                $changePassword = BOL_UserService::getInstance()->getResetPasswordForm('change-user-password');
                $changePassword->setAction(OW::getRouter()->urlForRoute('frmpasswordchangeinterval.change-user-password', array('token' => $passwordValidation->token)));
                $changePassword->bindJsFunction(Form::BIND_SUCCESS, "function( json ){if( json.result ){window.location.reload();}} ");
                $this->addForm($changePassword);
            }
        }
    }
}
