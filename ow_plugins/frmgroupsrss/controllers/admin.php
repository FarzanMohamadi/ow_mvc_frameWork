<?php

class FRMGROUPSRSS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    public function __construct()
    {
        parent::__construct();

        $this->setPageTitle($this->text('frmgroupsrss','admin_setting_title'));
        $this->setPageHeading($this->text('frmgroupsrss','admin_setting_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function index()
    {
        $form = new FRMGROUPSRSS_SettingsForm();

        if ( !empty($_POST) && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $config = OW::getConfig();

            $config->saveConfig('frmgroupsrss', 'update_interval', $data['update_interval']);
            $config->saveConfig('frmgroupsrss', 'feeds_count', $data['feeds_count']);

            OW::getFeedback()->info($this->text('frmgroupsrss', 'settings_success_msg'));
        }

        $this->addForm($form);
    }
}

class FRMGROUPSRSS_SettingsForm extends Form
{
    /***
     * FRMGROUPSRSS_SettingsForm constructor.
     */
    public function __construct()
    {
        $lang = OW::getLanguage();

        OW::getDocument()->setTitle($lang->text('frmgroupsrss', 'admin_groups_rss_settings_heading'));
        parent::__construct('form');

        $configs = OW::getConfig()->getValues('frmgroupsrss');

        $textField['update_interval'] = new TextField('update_interval');
        $textField['update_interval']->setLabel($lang->text('frmgroupsrss', 'admin_groups_rss_settings_update_interval'))
            ->setValue($configs['update_interval'])
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $this->addElement($textField['update_interval']);

        $textField['feeds_count'] = new TextField('feeds_count');
        $textField['feeds_count']->setLabel($lang->text('frmgroupsrss', 'admin_groups_rss_settings_feeds_count'))
            ->setValue($configs['feeds_count'])
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $this->addElement($textField['feeds_count']);

        $submit = new Submit('submit');

        $submit->setValue($lang->text('frmgroupsrss', 'save_btn_label'));

        $this->addElement($submit);
    }
}
