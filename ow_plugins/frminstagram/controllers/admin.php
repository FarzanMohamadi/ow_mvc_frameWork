<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
class FRMINSTAGRAM_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frminstagram', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frminstagram', 'admin_settings_heading'));

        $form = new Form("form");
        $configs = OW::getConfig()->getValues('frminstagram');

        $textField = new TextField('default_page');
        $textField->setLabel(OW::getLanguage()->text('frminstagram', 'default_page'))
            ->setValue($configs['default_page']);
        $form->addElement($textField);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frminstagram', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            OW::getConfig()->saveConfig('frminstagram', 'default_page', $data['default_page']);
            OW::getFeedback()->info(OW::getLanguage()->text('frminstagram', 'admin_changed_success'));
        }

        $this->addForm($form);
    }

}