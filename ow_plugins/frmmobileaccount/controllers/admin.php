<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobileaccount.controllers
 * @since 1.0
 */
class FRMMOBILEACCOUNT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index($params)
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $this->setPageHeading(OW::getLanguage()->text('frmmobileaccount', 'admin_settings_title'));
        $this->setPageTitle(OW::getLanguage()->text('frmmobileaccount', 'admin_settings_title'));

        $form = new Form('setting');

        $field = new TextField('expired_cookie');
        $field->setLabel(ow::getLanguage()->text('frmmobileaccount', 'expired_cookie'));
        $field->addValidator(new IntValidator());
        $field->setValue($config->getValue('frmmobileaccount', 'expired_cookie'));
        $form->addElement($field);

        $field = new TextField('username_prefix');
        $field->setLabel(ow::getLanguage()->text('frmmobileaccount', 'username_prefix'));
        $field->setValue($config->getValue('frmmobileaccount', 'username_prefix'));
        $form->addElement($field);

        $field = new TextField('email_postfix');
        $field->setLabel(ow::getLanguage()->text('frmmobileaccount', 'email_postfix'));
        $field->setValue($config->getValue('frmmobileaccount', 'email_postfix'));
        $form->addElement($field);

        $field = new Selectbox('login_type_version');
        $field->setHasInvitation(false);
        $options = array(
            FRMMOBILEACCOUNT_BOL_Service::BOTH_VERSION => $language->text('base', 'all'),
            FRMMOBILEACCOUNT_BOL_Service::MOBILE_VERSION => $language->text('base', 'mobile_version_menu_item'),
            FRMMOBILEACCOUNT_BOL_Service::DESKTOP_VERSION => $language->text('base', 'desktop_version_menu_item')
        );
        $field->setOptions($options);
        $field->setLabel(ow::getLanguage()->text('frmmobileaccount', 'login_type_version'));
        $field->addValidator(new IntValidator());
        $field->setValue($config->getValue('frmmobileaccount', 'login_type_version'));
        $form->addElement($field);

        $field = new Selectbox('join_type_version');
        $field->setHasInvitation(false);
        $options = array(
            FRMMOBILEACCOUNT_BOL_Service::BOTH_VERSION => $language->text('base', 'all'),
            FRMMOBILEACCOUNT_BOL_Service::MOBILE_VERSION => $language->text('base', 'mobile_version_menu_item'),
            FRMMOBILEACCOUNT_BOL_Service::DESKTOP_VERSION => $language->text('base', 'desktop_version_menu_item')
        );
        $field->setOptions($options);
        $field->setLabel(ow::getLanguage()->text('frmmobileaccount', 'join_type_version'));
        $field->addValidator(new IntValidator());
        $field->setValue($config->getValue('frmmobileaccount', 'join_type_version'));
        $form->addElement($field);

        $field = new Selectbox('mandatory_email');
        $field->setHasInvitation(false);
        $options = array(
            0 => $language->text('frmmobileaccount', 'optional'),
            1 => $language->text('frmmobileaccount', 'mandatory')
        );
        $field->setOptions($options);
        $field->setLabel(ow::getLanguage()->text('base', 'ow_ic_mail'));
        $field->setValue($config->getValue('frmmobileaccount', 'mandatory_email'));
        $form->addElement($field);

        $element = new Submit('submit');
        $form->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $config->saveConfig('frmmobileaccount', 'username_prefix', $data['username_prefix']);
                $config->saveConfig('frmmobileaccount', 'email_postfix', $data['email_postfix']);
                $config->saveConfig('frmmobileaccount', 'expired_cookie', $data['expired_cookie']);
                $config->saveConfig('frmmobileaccount', 'login_type_version', $data['login_type_version']);
                $config->saveConfig('frmmobileaccount', 'join_type_version', $data['join_type_version']);
                $config->saveConfig('frmmobileaccount', 'mandatory_email', $data['mandatory_email']);
                OW::getFeedback()->info(OW::getLanguage()->text('frmmobileaccount', 'saved_successfully'));
            }
        }

        $this->addForm($form);
    }
}