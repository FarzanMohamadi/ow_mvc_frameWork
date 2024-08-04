<?php
class FRMADMINNOTIFICATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmadminnotification', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmadminnotification', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function settings()
    {
        $adminForm = new Form('adminForm');      

        $lang = OW::getLanguage();
        $config = OW::getConfig();

        $field = new TextField('emailSendTo');
        $field->addValidator(new EmailValidator());
        $field->setLabel($lang->text('frmadminnotification','emailSendTo'));
        $field->setValue($config->getValue('frmadminnotification', 'emailSendTo'));
        $field->setDescription($lang->text('frmadminnotification', 'emailSendToDescription'));
        $adminForm->addElement($field);
        
        $field = new CheckboxField('newsCommentNotification');
        $field->setLabel($lang->text('frmadminnotification','newsCommentNotification'));
        $field->setValue($config->getValue('frmadminnotification', 'newsCommentNotification'));
        $adminForm->addElement($field);


        $field = new CheckboxField('topicForumNotification');
        $field->setLabel($lang->text('frmadminnotification','topicForumNotification'));
        $field->setValue($config->getValue('frmadminnotification', 'topicForumNotification'));
        $adminForm->addElement($field);

        $field = new CheckboxField('registerNotification');
        $field->setLabel($lang->text('frmadminnotification','registerNotification'));
        $field->setValue($config->getValue('frmadminnotification', 'registerNotification'));
        $adminForm->addElement($field);
        
        $element = new Submit('saveSettings');
        $element->setValue($lang->text('frmadminnotification', 'admin_save_settings'));
        $adminForm->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($adminForm->isValid($_POST)) {
                $config = OW::getConfig();
                $values = $adminForm->getValues();
                $config->saveConfig('frmadminnotification', 'newsCommentNotification', $values['newsCommentNotification']);
                $config->saveConfig('frmadminnotification', 'topicForumNotification', $values['topicForumNotification']);
                $config->saveConfig('frmadminnotification', 'registerNotification', $values['registerNotification']);
                $config->saveConfig('frmadminnotification', 'emailSendTo', $values['emailSendTo']);
                OW::getFeedback()->info($lang->text('frmadminnotification', 'user_save_success'));
            }
        }

       $this->addForm($adminForm);
   } 
}
