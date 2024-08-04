<?php
/**
 * 
 * All rights reserved.
 */
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.sso.bol
 * @since 1.0
 */
class SSO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('sso', 'admin_page_heading'));
        $this->setPageTitle($language->text('sso', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('sso');

        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('sso.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("sso", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

        $ssoLoginUrl = new TextField('ssoLoginUrl');
        $ssoLoginUrl->setValue($configs['ssoLoginUrl']);
        $ssoLoginUrl->setLabel($language->text('sso','ssoLoginUrl'));
        $ssoLoginUrl->setRequired();
        $form->addElement($ssoLoginUrl);

        $ssoLogoutUrl = new TextField('ssoLogoutUrl');
        $ssoLogoutUrl->setValue($configs['ssoLogoutUrl']);
        $ssoLogoutUrl->setLabel($language->text('sso','ssoLogoutUrl'));
        $ssoLogoutUrl->setRequired();
        $form->addElement($ssoLogoutUrl);

        $ssoRegistrationUrl = new TextField('ssoRegistrationUrl');
        $ssoRegistrationUrl->setValue($configs['ssoRegistrationUrl']);
        $ssoRegistrationUrl->setLabel($language->text('sso','ssoRegistrationUrl'));
        $ssoRegistrationUrl->setRequired();
        $form->addElement($ssoRegistrationUrl);

        $ssoRegistrationUrl = new TextField('ssoChangePasswordUrl');
        $ssoRegistrationUrl->setValue($configs['ssoChangePasswordUrl']);
        $ssoRegistrationUrl->setLabel($language->text('sso','ssoChangePasswordUrl'));
        $ssoRegistrationUrl->setRequired();
        $form->addElement($ssoRegistrationUrl);

        $ssoRegistrationUrl = new TextField('ssoGetToken');
        $ssoRegistrationUrl->setValue($configs['ssoGetToken']);
        $ssoRegistrationUrl->setLabel($language->text('sso','ssoGetToken'));
        $ssoRegistrationUrl->setRequired();
        $form->addElement($ssoRegistrationUrl);

        $autoRegisterUsers = new TextField('usersDetailsUrl');
        $autoRegisterUsers->setValue($configs['usersDetailsUrl']);
        $autoRegisterUsers->setLabel($language->text('sso','usersDetailsUrl'));
        $form->addElement($autoRegisterUsers);

        $autoRegisterUsers = new CheckboxField('autoRegisterUsers');
        $autoRegisterUsers->setValue($configs['autoRegisterUsers']);
        $autoRegisterUsers->setLabel($language->text('sso','autoRegisterUsers'));
        $form->addElement($autoRegisterUsers);


        $submit = new Submit('save');
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $this->addOrEditConfig('sso', 'ssoLoginUrl', $form->getElement('ssoLoginUrl')->getValue());
                $this->addOrEditConfig('sso', 'ssoLogoutUrl', $form->getElement('ssoLogoutUrl')->getValue());
                $this->addOrEditConfig('sso', 'ssoRegistrationUrl', $form->getElement('ssoRegistrationUrl')->getValue());
                $this->addOrEditConfig('sso', 'ssoChangePasswordUrl', $form->getElement('ssoChangePasswordUrl')->getValue());
                $this->addOrEditConfig('sso', 'usersDetailsUrl', $form->getElement('usersDetailsUrl')->getValue());
                SSO_BOL_Service::getInstance()->createMobileField();
                exit(json_encode(array('result' => true)));
            }
        }
    }
    private function addOrEditConfig($key, $name, $value){
        $config = OW::getConfig();
        if ($config->configExists($key,$name)){
            $config->saveConfig($key, $name, $value);
        }else{
            $config->addConfig($key, $name, $value);
        }
    }
}
