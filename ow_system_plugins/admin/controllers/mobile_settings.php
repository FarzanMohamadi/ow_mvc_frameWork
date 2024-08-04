<?php
/**
 * Admin index controller class.
 *
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_MobileSettings extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();

        OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_mobile_settings'));
        OW::getDocument()->setHeadingIconClass('ow_ic_gear_wheel');
        $settingsForm = new Form('mobile_settings');

        $disableMobile = new CheckboxField('disable_mobile');
        $disableMobile->setLabel($language->text('admin', 'mobile_settings_mobile_context_disable_label'));
        $disableMobile->setDescription($language->text('admin', 'mobile_settings_mobile_context_disable_desc'));
        $settingsForm->addElement($disableMobile);
        
        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $settingsForm->addElement($submit);
        
        $this->addForm($settingsForm);
        
        if ( OW::getRequest()->isPost() )
        {
            if ( $settingsForm->isValid($_POST) )
            {
                $data = $settingsForm->getValues();

                $config->saveConfig('base', 'disable_mobile_context', (bool) $data['disable_mobile']);
                OW::getFeedback()->info($language->text('admin', 'settings_submit_success_message'));
            }
            else
            {
                OW::getFeedback()->error('Error');
            }

            $this->redirect();
        }

        $disableMobile->setValue($config->getValue('base', 'disable_mobile_context'));
    }
}
