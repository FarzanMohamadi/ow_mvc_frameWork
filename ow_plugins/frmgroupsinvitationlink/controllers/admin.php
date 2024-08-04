<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.controllers
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmgroupsinvitationlink', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        $form = new FRMGROUPSINVITATIONLINK_SettingsForm($this);
        if ( !empty($_POST) && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $config = OW::getConfig();

            $config->saveConfig('frmgroupsinvitationlink', 'link_expiration_time', $data['link_expiration_time']);
            $config->saveConfig('frmgroupsinvitationlink', 'deep_link', $data['deep_link']);
        }

        $this->addForm($form);
    }

}

class FRMGROUPSINVITATIONLINK_SettingsForm extends Form
{

    /***
     * FRMGROUPSINVITATIONLINK_SettingsForm constructor.
     * @param FRMGROUPSINVITATIONLINK_CTRL_Admin $ctrl
     */
    public function __construct( $ctrl )
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmgroupsinvitationlink', 'admin_settings_heading'));
        parent::__construct('form');

        $configs = OW::getConfig()->getValues('frmgroupsinvitationlink');

        $ctrl->assign('configs', $configs);

        $l = OW::getLanguage();

        $textField['link_expiration_time'] = new TextField('link_expiration_time');
        $textField['link_expiration_time']->setLabel($l->text('frmgroupsinvitationlink', 'link_expiration_time'))
            ->setValue($configs['link_expiration_time'])
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $this->addElement($textField['link_expiration_time']);

        $textField['deep_link'] = new TextField('deep_link');

        $textField['deep_link']->setLabel($l->text('frmgroupsinvitationlink', 'deep_link_label'))
            ->setValue($configs['deep_link']);

        $this->addElement($textField['deep_link']);

        $submit = new Submit('submit');

        $submit->setValue($l->text('frmgroupsinvitationlink', 'save_btn_label'));

        $this->addElement($submit);
    }
}