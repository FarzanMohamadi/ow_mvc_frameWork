<?php
class FRMMENU_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmmenu', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmmenu', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function settings()
    {
        $adminForm = new Form('adminForm');      

        $lang = OW::getLanguage();
        $config = OW::getConfig();

        $field = new CheckboxField('replaceMenu');
        $field->setLabel($lang->text('frmmenu','replaceMenu'));
        $field->setValue($config->getValue('frmmenu', 'replaceMenu'));
        $adminForm->addElement($field);
        
        $element = new Submit('saveSettings');
        $element->setValue($lang->text('frmmenu', 'admin_save_settings'));
        $adminForm->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($adminForm->isValid($_POST)) {
                $config = OW::getConfig();
                $values = $adminForm->getValues();
                $config->saveConfig('frmmenu', 'replaceMenu', $values['replaceMenu']);
                OW::getFeedback()->info($lang->text('frmmenu', 'user_save_success'));
            }
        }

       $this->addForm($adminForm);
   } 
}
