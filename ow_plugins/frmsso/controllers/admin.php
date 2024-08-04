<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsso.controllers
 * @since 1.0
 */
class FRMSSO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmsso', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmsso', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmsso');

        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmsso.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("frmsso", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

        $ssoUrl = new TextField('ssoUrl');
        $ssoUrl->setValue($configs['ssoUrl']);
        $ssoUrl->addAttribute('placeholder', 'http://sso.example.com');
        $ssoUrl->setLabel($language->text('frmsso','ssoUrl'));
        $ssoUrl->setRequired();
        $form->addElement($ssoUrl);

        $ssoTicketValidationUrl = new TextField('ssoTicketValidationUrl');
        $ssoTicketValidationUrl->setValue($configs['ssoTicketValidationUrl']);
        $ssoTicketValidationUrl->setLabel($language->text('frmsso','ssoTicketValidationUrl'));
        $ssoTicketValidationUrl->setRequired();
        $form->addElement($ssoTicketValidationUrl);


        $ssoLoginUrl = new TextField('ssoLoginUrl');
        $ssoLoginUrl->setValue($configs['ssoLoginUrl']);
        $ssoLoginUrl->setLabel($language->text('frmsso','ssoLoginUrl'));
        $ssoLoginUrl->setRequired();
        $form->addElement($ssoLoginUrl);

        $ssoLogoutUrl = new TextField('ssoLogoutUrl');
        $ssoLogoutUrl->setValue($configs['ssoLogoutUrl']);
        $ssoLogoutUrl->setLabel($language->text('frmsso','ssoLogoutUrl'));
        $ssoLogoutUrl->setRequired();
        $form->addElement($ssoLogoutUrl);

        $ssoRegistrationUrl = new TextField('ssoRegistrationUrl');
        $ssoRegistrationUrl->setValue($configs['ssoRegistrationUrl']);
        $ssoRegistrationUrl->setLabel($language->text('frmsso','ssoRegistrationUrl'));
        $ssoRegistrationUrl->setRequired();
        $form->addElement($ssoRegistrationUrl);

        $ssoRegistrationUrl = new TextField('ssoChangePasswordUrl');
        $ssoRegistrationUrl->setValue($configs['ssoChangePasswordUrl']);
        $ssoRegistrationUrl->setLabel($language->text('frmsso','ssoChangePasswordUrl'));
        $ssoRegistrationUrl->setRequired();
        $form->addElement($ssoRegistrationUrl);

        $ssoServerSecret = new TextField('ssoServerSecret');
        $ssoServerSecret->setValue($configs['ssoServerSecret']);
        $ssoServerSecret->setLabel($language->text('frmsso','ssoServerSecret'));
        $ssoServerSecret->setRequired();
        $form->addElement($ssoServerSecret);

        $ssoClientSecret = new TextField('ssoClientSecret');
        $ssoClientSecret->setValue($configs['ssoClientSecret']);
        $ssoClientSecret->setLabel($language->text('frmsso','ssoClientSecret'));
        $ssoClientSecret->setRequired();
        $form->addElement($ssoClientSecret);

        $ssoCookieKey = new TextField('ssoCookieKey');
        $ssoCookieKey->setValue($configs['ssoCookieKey']);
        $ssoCookieKey->setLabel($language->text('frmsso','ssoCookieKey'));
        $ssoCookieKey->setRequired();
        $form->addElement($ssoCookieKey);

        $ssoSameDomain = new CheckboxField('ssoSameDomain');
        $ssoSameDomain->setValue($configs['ssoSameDomain']);
        $ssoSameDomain->setLabel($language->text('frmsso','ssoSameDomain'));
        $form->addElement($ssoSameDomain);

        $autoRegisterUsers = new CheckboxField('autoRegisterUsers');
        $autoRegisterUsers->setValue($configs['autoRegisterUsers']);
        $autoRegisterUsers->setLabel($language->text('frmsso','autoRegisterUsers'));
        $form->addElement($autoRegisterUsers);

        $autoRegisterUsers = new TextField('usersDetailsUrl');
        $autoRegisterUsers->setValue($configs['usersDetailsUrl']);
        $autoRegisterUsers->setLabel($language->text('frmsso','usersDetailsUrl'));
        $form->addElement($autoRegisterUsers);

        $ssoCookieKey = new TextField('ssoSharedCookieDomain');
        $ssoCookieKey->setValue($configs['ssoSharedCookieDomain']);
        $ssoCookieKey->setLabel($language->text('frmsso','ssoSharedCookieDomain'));
        $ssoCookieKey->setRequired();
        $form->addElement($ssoCookieKey);

        $submit = new Submit('save');
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $this->addOrEditConfig('frmsso', 'ssoUrl', $form->getElement('ssoUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoTicketValidationUrl', $form->getElement('ssoTicketValidationUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoLoginUrl', $form->getElement('ssoLoginUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoLogoutUrl', $form->getElement('ssoLogoutUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoRegistrationUrl', $form->getElement('ssoRegistrationUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoChangePasswordUrl', $form->getElement('ssoChangePasswordUrl')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoServerSecret', $form->getElement('ssoServerSecret')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoClientSecret', $form->getElement('ssoClientSecret')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoCookieKey', $form->getElement('ssoCookieKey')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoSameDomain', $form->getElement('ssoSameDomain')->getValue());
                $this->addOrEditConfig('frmsso', 'autoRegisterUsers', $form->getElement('autoRegisterUsers')->getValue());
                $this->addOrEditConfig('frmsso', 'ssoSharedCookieDomain', $form->getElement('ssoSharedCookieDomain')->getValue());
                $this->addOrEditConfig('frmsso', 'usersDetailsUrl', $form->getElement('usersDetailsUrl')->getValue());
                FRMSSO_BOL_Service::getInstance()->createMobileField();
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
