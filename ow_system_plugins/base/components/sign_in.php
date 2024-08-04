<?php
/**
 * User console component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_SignIn extends OW_Component
{
    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';

    /**
     * Constructor.
     */
    public function __construct( $ajax = false )
    {
        parent::__construct();
        $form = BOL_UserService::getInstance()->getSignInForm('sign-in');

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORM_SIGNIN_RENDER, array('form' => $form,'BASE_CMP_SignIn' => $this,'ajax' => $ajax)));

        $this->addForm($form);
        $form->setId('sign-in-standard');
        if ( $ajax )
        {
            $form->setAjaxResetOnSuccess(false);
            $form->setAjax();
            $form->setId('sign-in-ajax');
            $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_User', 'ajaxSignIn'));

            $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if( data.result ){OW.info(data.message);setTimeout(function(){window.location.reload();}, 1000);} else{OW.error(data.message);}}');
            $this->assign('forgot_url', OW::getRouter()->urlForRoute('base_forgot_password'));

        }

        OW::getDocument()->addOnloadScript('
        var inputNames = ["identity", "password"];

        $(document).ready(function () {
            setTimeout(function () {
                inputNames.forEach(updateLabels);
            },500);
        });

        $( ".ow_sign_in input" ).keyup(function() {
            inputNames.forEach(updateLabels);
        });

        function updateLabels(item, index) {
            var s = "input[name="+item+"]";
            if($(s).val() != ""){
                $(s).next().addClass("filled");
            } else {
                $(s).next().removeClass("filled");
            }
        }');
        
        $isNewTheme = FRMSecurityProvider::themeCoreDetector() ? true : false;
        $this->assign("isNewTemplate", $isNewTheme);

        $this->assign('joinUrl', OW::getRouter()->urlForRoute('base_join'));

        $this->assign('signup_button', false);
        if(OW::getConfig()->getValue('base', 'disable_signup_button'))
        {
            $this->assign('signup_button', true);
        }
    }
}