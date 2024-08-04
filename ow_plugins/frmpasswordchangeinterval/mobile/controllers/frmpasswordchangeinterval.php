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
class FRMPASSWORDCHANGEINTERVAL_MCTRL_Iispasswordchangeinterval extends OW_MobileActionController
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
        }
    }

    public function logoutAndGoToForgotPassword()
    {
        OW::getUser()->logout();
        $this->redirect(OW::getRouter()->uriForRoute( 'base_forgot_password'));
    }
}
