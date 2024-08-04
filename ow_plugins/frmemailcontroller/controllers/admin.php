<?php
/**
 * frmemailcontroller admin action controller
 *
 */
class FRMEMAILCONTROLLER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param array $params
     */
    public function index(array $params = array())
    {
        $this->setPageHeading(OW::getLanguage()->text('frmemailcontroller', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmemailcontroller', 'admin_settings_heading'));
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmemailcontroller_admin_config'));
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmemailcontroller', 'settings_updated').'\')  }  }');

        $validEmailServices = new Textarea('validEmailServices');
        $validEmailServices->setLabel($language->text('frmemailcontroller', 'input_settings_valid_email_list_label'));
        $validEmailServices->setDescription($language->text('frmemailcontroller', 'input_settings_valid_email_list_desc'));
        $form->addElement($validEmailServices);

        $disableEmailController = new CheckboxField('disableEmailController');
        $disableEmailController->setLabel($language->text('frmemailcontroller', 'input_settings_disable_email_controller_label'));
        $disableEmailController->setDescription($language->text('frmemailcontroller', 'input_settings_disable_email_controller_desc'));
        $form->addElement($disableEmailController);


        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $validProviderList = [];
            if (!empty($data['validEmailServices']) )
            {
                $validProviderList = array_unique(preg_split('/\n/', $data['validEmailServices']));
            }

            if ( !$config->configExists('frmemailcontroller', 'valid_email_services'))
            {
                $config->addConfig('frmemailcontroller', 'valid_email_services', json_encode(array_map('trim', $validProviderList)));
            }else {
                $config->saveConfig('frmemailcontroller', 'valid_email_services', json_encode(array_map('trim', $validProviderList)));
            }

            if ( !$config->configExists('frmemailcontroller', 'disable_frmemailcontroller'))
            {
                $config->addConfig('frmemailcontroller', 'disable_frmemailcontroller',(int)$data['disableEmailController']);
            }else {
                $config->saveConfig('frmemailcontroller', 'disable_frmemailcontroller',(int)$data['disableEmailController']);
            }
            exit(json_encode(array('result' => true)));
        }
        $validEmailServices->setValue(implode(PHP_EOL, json_decode($config->getValue('frmemailcontroller', 'valid_email_services'))));
        $disableEmailController->setValue($config->getValue('frmemailcontroller', 'disable_frmemailcontroller'));
    }

}