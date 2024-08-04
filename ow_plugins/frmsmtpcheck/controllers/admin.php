<?php
/**
 * frmsmtpcheck
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsmtpcheck
 * @since 1.0
 */

class FRMSMTPCHECK_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmsmtpcheck', 'admin_settings_heading'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmsmtpcheck', 'admin_settings_heading'));

        $form = new Form("form");
        $configs = OW::getConfig()->getValues('frmsmtpcheck');

        $textField = new TextField('suffix');
        $textField->setLabel(OW::getLanguage()->text('frmsmtpcheck', 'suffix'))->setValue($configs['suffix']);
        $form->addElement($textField);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmsmtpcheck', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            OW::getConfig()->saveConfig('frmsmtpcheck', 'suffix', $data['suffix']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmsmtpcheck', 'admin_changed_success'));
        }

        $this->addForm($form);
    }
}