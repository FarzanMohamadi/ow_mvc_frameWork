<?php
class FRMSMS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmsms', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmsms', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function settings($params)
    {
        $sectionId = 1;
        if(isset($params['sectionId'])){
            $sectionId = $params['sectionId'];
        }
        $this->assign('sectionId', $sectionId);
        if($sectionId==1)
        {
            $this->assign('sections', FRMSMS_BOL_Service::getInstance()->getAdminSections($sectionId));
            $adminForm = new Form('adminForm');
            $config = OW::getConfig();
            $adminForm->setAction(OW::getRouter()->urlForRoute('frmsms-admin.section-id', array('sectionId' => $sectionId)));
            $creditThresholdField = new TextField('credit_threshold');
            $creditThresholdField->setLabel(OW::getLanguage()->text('frmsms', 'credit_threshold'));
            $creditThresholdField->setValue($config->getValue('frmsms', 'credit_threshold'));
            $creditThresholdField->addValidator(new IntValidator());
            $adminForm->addElement($creditThresholdField);


            $tokenResendIntervalField = new TextField('token_resend_interval');
            $tokenResendIntervalField->setLabel(OW::getLanguage()->text('frmsms', 'token_resend_interval'));
            $tokenResendIntervalField->setValue($config->getValue('frmsms', 'token_resend_interval'));
            $tokenResendIntervalField->addValidator(new IntValidator());
            $adminForm->addElement($tokenResendIntervalField);

            $maxTokenRequestField = new TextField('max_token_request');
            $maxTokenRequestField->setLabel(OW::getLanguage()->text('frmsms', 'max_token_request'));
            $maxTokenRequestField->setValue($config->getValue('frmsms', 'max_token_request'));
            $maxTokenRequestField->addValidator(new IntValidator());
            $adminForm->addElement($maxTokenRequestField);

            $removeTextLinkField = new CheckboxField('remove_text_link');
            $removeTextLinkField->setLabel(OW::getLanguage()->text('frmsms', 'remove_text_link'));
            $removeTextLinkField->setValue($config->getValue('frmsms', 'remove_text_link'));
            $adminForm->addElement($removeTextLinkField);

            $element = new Submit('saveSettings');
            $element->setValue(OW::getLanguage()->text('frmsms', 'admin_save_settings'));
            $adminForm->addElement($element);

            if (OW::getRequest()->isPost()) {
                if ($adminForm->isValid($_POST)) {
                    $config = OW::getConfig();
                    $values = $adminForm->getValues();
                    $config->saveConfig('frmsms', 'credit_threshold', $values['credit_threshold']);
                    $config->saveConfig('frmsms', 'token_resend_interval', $values['token_resend_interval']);
                    $config->saveConfig('frmsms', 'max_token_request', $values['max_token_request']);
                    $config->saveConfig('frmsms', 'remove_text_link', $values['remove_text_link']);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmsms', 'user_save_success'));
                }
            }

            $this->addForm($adminForm);
        }else if($sectionId==2) {
            $this->assign('sections', FRMSMS_BOL_Service::getInstance()->getAdminSections($sectionId));
            $config = OW::getConfig();
            $language = OW::getLanguage();
            $form = new Form('form');
            $form->setAction(OW::getRouter()->urlForRoute('frmsms-admin.section-id', array('sectionId' => $sectionId)));

            $validPhoneNumbersField = new Textarea('validPhoneNumbers');
            $validPhoneNumbersField->setLabel($language->text('frmsms', 'input_settings_valid_phone_number_list_label'));
            $validPhoneNumbersField->setDescription($language->text('frmsms', 'input_settings_valid_phone_number_list_desc'));
            $form->addElement($validPhoneNumbersField);

            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);

            if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                $data = $form->getValues();
                if (!empty(trim($data['validPhoneNumbers']))) {
                    $validPhoneNumbers = array_unique(preg_split('/' . PHP_EOL . '/', $data['validPhoneNumbers']));
                    if (!$config->configExists('frmsms', 'valid_phone_numbers')) {
                        $config->addConfig('frmsms', 'valid_phone_numbers', json_encode(array_map('trim', $validPhoneNumbers)));
                    } else {
                        $config->saveConfig('frmsms', 'valid_phone_numbers', json_encode(array_map('trim', $validPhoneNumbers)));
                    }
                } else {
                    $config->deleteConfig('frmsms', 'valid_phone_numbers');
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmsms', 'settings_successfuly_saved'));
            }
            if ($config->configExists('frmsms', 'valid_phone_numbers')) {
                $validPhoneNumbersField->setValue(implode(PHP_EOL, json_decode($config->getValue('frmsms', 'valid_phone_numbers'))));
            }
        }
   } 
}
