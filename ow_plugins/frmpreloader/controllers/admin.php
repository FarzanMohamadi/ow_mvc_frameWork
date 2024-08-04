<?php
class FRMPRELOADER_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('frmpreloader', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmpreloader', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function settings()
    {


        $PreloaderForm = new Form('PreloaderForm');

        $lang = OW::getLanguage();
        $config = OW::getConfig();
        $configs = $config->getValues('frmpreloader');

        $frmpreloadertype = new Selectbox('frmpreloadertype');
        $options = array();
        $options[1] = 1;
        $options[2] = 2;
        $options[3] = 3;
        $options[4] = 4;
        $frmpreloadertype->setHasInvitation(false);
        $frmpreloadertype->setOptions($options);
        $frmpreloadertype->setRequired();
        $frmpreloadertype->setValue($configs['frmpreloadertype']);
        $PreloaderForm->addElement($frmpreloadertype);


        $saveSettings = new Submit('saveSettings');
        $saveSettings->setValue($lang->text('frmpreloader', 'admin_save_settings'));
        $PreloaderForm->addElement($saveSettings);

        $this->addForm($PreloaderForm);

        if ( OW::getRequest()->isPost())
        {
            if ( $PreloaderForm->isValid($_POST) )
            {
                $config->saveConfig('frmpreloader', 'frmpreloadertype', $PreloaderForm->getElement('frmpreloadertype')->getValue());
            }
        }


    }
}
