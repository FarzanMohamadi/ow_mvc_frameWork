<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */
class FRMFILEMANAGER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmfilemanager', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmfilemanager', 'admin_title'));

        // reset all form
        $form = new Form('reset_and_import');
        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmfilemanager', 'reset_all_files'));
        $form->addElement($submit);
        $this->addForm($form);

        // reset profile form
        $form = new Form('reset_only_groups');
        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmfilemanager', 'reset_all_group_files'));
        $form->addElement($submit);
        $this->addForm($form);

        // reset profile form
        $form = new Form('reset_only_profile');
        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmfilemanager', 'reset_all_profile_files'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) && in_array($_POST['form_name'], ['reset_and_import', 'reset_only_groups', 'reset_only_profile']))
            {
                FRMFILEMANAGER_BOL_Service::getInstance()->{$_POST['form_name']}();
                OW::getFeedback()->info(OW::getLanguage()->text('admin', 'updated_msg'));
            }
        }

        // form settings
        $form = new Form('settings');

        $el1 = new CheckboxField('enable_mobile_version');
        $el1->setLabel(OW::getLanguage()->text('frmfilemanager', 'enable_mobile_version'));
        $el1->setValue(OW::getConfig()->getValue('frmfilemanager', 'enable_mobile_version', false));
        $form->addElement($el1);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmfilemanager', 'submit'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) && $_POST['form_name']=='settings' )
            {
                $val = isset($_POST['enable_mobile_version'])?(bool)$_POST['enable_mobile_version']:false;
                OW::getConfig()->saveConfig('frmfilemanager', 'enable_mobile_version', $val);
                OW::getFeedback()->info(OW::getLanguage()->text('admin', 'updated_msg'));
            }
        }
    }
}