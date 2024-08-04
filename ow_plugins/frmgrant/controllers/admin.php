<?php
class FRMGRANT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('frmgrant', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmgrant', 'admin_settings_title'));
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmgrant.admin-config'));
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmgrant', 'settings_updated').'\')  }  }');

        $collegeAndFields = new Textarea('collegeAndFields');
        $collegeAndFields->setLabel($language->text('frmgrant', 'input_settings_collegeAndFields_list_label'));
        $collegeAndFields->setDescription($language->text('frmgrant', 'input_settings_collegeAndFields_list_desc'));
        $form->addElement($collegeAndFields);


        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $collegeAndFieldsList = [];
            if (!empty($data['collegeAndFields']) )
            {
                $collegeAndFieldsList = array_unique(preg_split('/\n/', $data['collegeAndFields']));
            }
            if ( !$config->configExists('frmgrant', 'collegeAndFields_list_setting'))
            {
                $config->addConfig('frmgrant', 'collegeAndFields_list_setting', json_encode(array_map('trim', $collegeAndFieldsList)));
            }else {
                $config->saveConfig('frmgrant', 'collegeAndFields_list_setting', json_encode(array_map('trim', $collegeAndFieldsList)));
            }
            exit(json_encode(array('result' => true)));
        }
        $collegeAndFields->setValue(implode(PHP_EOL, json_decode($config->getValue('frmgrant', 'collegeAndFields_list_setting'))));
    }
}