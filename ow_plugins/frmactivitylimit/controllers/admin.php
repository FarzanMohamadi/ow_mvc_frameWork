<?php
/**
 * frmactivitylimit
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */
class FRMACTIVITYLIMIT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmactivitylimit', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmactivitylimit', 'admin_title'));

        $config = OW::getConfig();
        $lang = OW::getLanguage();

        $form = new Form('settings');
        $this->addForm($form);

        $field1 = new TextField('max_db_requests');
        $field1->setRequired(true);
        $validator = new IntValidator();
        $validator->setMinValue(5);
        $validator->setMaxValue(10000000);
        $field1->addValidator($validator);
        $field1->setLabel($lang->text('frmactivitylimit','max_db_requests'));
        $field1->setValue($config->getValue('frmactivitylimit', 'max_db_requests'));
        $form->addElement($field1);

        $field1 = new TextField('minutes_to_reset');
        $field1->setRequired(true);
        $validator = new IntValidator();
        $validator->setMinValue(1);
        $validator->setMaxValue(30*24*60);
        $field1->addValidator($validator);
        $field1->setLabel($lang->text('frmactivitylimit','minutes_to_reset'));
        $field1->setValue($config->getValue('frmactivitylimit', 'minutes_to_reset'));
        $form->addElement($field1);

        $field1 = new TextField('blocking_minutes');
        $field1->setRequired(true);
        $validator = new IntValidator();
        $validator->setMinValue(1);
        $validator->setMaxValue(30*24*60);
        $field1->addValidator($validator);
        $field1->setLabel($lang->text('frmactivitylimit','blocking_minutes'));
        $field1->setValue($config->getValue('frmactivitylimit', 'blocking_minutes'));
        $form->addElement($field1);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmactivitylimit', 'form_submit'));
        $form->addElement($submit);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                OW::getConfig()->saveConfig('frmactivitylimit', 'max_db_requests', intval($_POST['max_db_requests']));
                OW::getConfig()->saveConfig('frmactivitylimit', 'minutes_to_reset', floatval($_POST['minutes_to_reset']));
                OW::getConfig()->saveConfig('frmactivitylimit', 'blocking_minutes', floatval($_POST['blocking_minutes']));
                OW::getFeedback()->info(OW::getLanguage()->text('frmactivitylimit', 'save_successful_message'));
            }
        }
    }
}