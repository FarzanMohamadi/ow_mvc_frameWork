<?php
/**
 * User console component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_SignIn extends OW_MobileComponent
{

    /**
     * Constructor.
     */
    public function __construct( $ajax = true )
    {
        parent::__construct();

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('base','sign_in_submit_label'));
        $form = BOL_UserService::getInstance()->getSignInForm('sign-in', false);

        if ( $ajax )
        {
            $form->setAction(OW::getRouter()->urlFor('BASE_MCTRL_User', 'signIn'));
            $form->setAjax();
            $form->bindJsFunction(Form::BIND_SUBMIT, 'function(data){$("#console_preloader").fadeIn(300);}');
            $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){$("#console_preloader").fadeOut(300);if( data.result ){OWM.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{OWM.error(data.message);}}');
        }
        $eventData= OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORM_SIGNIN_RENDER, array('form' => $form,'BASE_CMP_SignIn' => $this,'ajax' => $ajax)));
        if(isset($eventData->getData()['ssoForm']))
        {
            $this->addForm($eventData->getData()['ssoForm']);
            $this->assign('sso',true);
            if(isset($eventData->getData()['joinButton']))
            {
                $this->addComponent('joinButton',$eventData->getData()['joinButton']);
            }
            return;
        }else if(isset($eventData->getData()['frmmobileaccount_signin_from']))
        {
            $this->addForm($eventData->getData()['frmmobileaccount_signin_from']);
            $this->assign('frmmobileaccount_signin',true);
        }
        OW::getDocument()->addOnloadScript("$('.owm_login_username input').focus()");
        $this->addForm($form);
    }
}