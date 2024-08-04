<?php
class FRMTECHNOLOGY_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('frmtechnology', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmtechnology', 'admin_settings_title'));
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmtechnology.admin-config'));
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmtechnology', 'settings_updated').'\')  }  }');

        $positions = new Textarea('positions');
        $positions->setLabel($language->text('frmtechnology', 'input_settings_position_list_label'));
        $positions->setDescription($language->text('frmtechnology', 'input_settings_position_list_desc'));
        $form->addElement($positions);

        $grades = new Textarea('grades');
        $grades->setLabel($language->text('frmtechnology', 'input_settings_grade_list_label'));
        $grades->setDescription($language->text('frmtechnology', 'input_settings_grade_list_desc'));
        $form->addElement($grades);

        $orgs = new Textarea('orgs');
        $orgs->setLabel($language->text('frmtechnology', 'input_settings_org_list_label'));
        $orgs->setDescription($language->text('frmtechnology', 'input_settings_org_list_desc'));
        $form->addElement($orgs);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $positionsList = [];
            $gradesList = [];
            $orgsList = [];
            if (!empty($data['positions']) )
            {
                $positionsList = array_unique(preg_split('/\n/', $data['positions']));
            }
            if ( !$config->configExists('frmtechnology', 'positions_list_setting'))
            {
                $config->addConfig('frmtechnology', 'positions_list_setting', json_encode(array_map('trim', $positionsList)));
            }else {
                $config->saveConfig('frmtechnology', 'positions_list_setting', json_encode(array_map('trim', $positionsList)));
            }
            if (!empty($data['grades']) )
            {
                $gradesList = array_unique(preg_split('/\n/', $data['grades']));
            }
            if ( !$config->configExists('frmtechnology', 'grades_list_setting'))
            {
                $config->addConfig('frmtechnology', 'grades_list_setting', json_encode(array_map('trim', $gradesList)));
            }else {
                $config->saveConfig('frmtechnology', 'grades_list_setting', json_encode(array_map('trim', $gradesList)));
            }
            if (!empty($data['orgs']) )
            {
                $orgsList = array_unique(preg_split('/\n/', $data['orgs']));
            }
            if ( !$config->configExists('frmtechnology', 'orgs_list_setting'))
            {
                $config->addConfig('frmtechnology', 'orgs_list_setting', json_encode(array_map('trim', $orgsList)));
            }else {
                $config->saveConfig('frmtechnology', 'orgs_list_setting', json_encode(array_map('trim', $orgsList)));
            }
            exit(json_encode(array('result' => true)));
        }
        $positions->setValue(implode(PHP_EOL, json_decode($config->getValue('frmtechnology', 'positions_list_setting'))));
        $grades->setValue(implode(PHP_EOL, json_decode($config->getValue('frmtechnology', 'grades_list_setting'))));
        $orgs->setValue(implode(PHP_EOL, json_decode($config->getValue('frmtechnology', 'orgs_list_setting'))));
    }
}