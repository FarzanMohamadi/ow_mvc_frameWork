<?php
class FRMTPNETSMS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $this->setPageHeading($language->text('frmtpnetsms', 'admin_settings_title'));
        $this->setPageTitle($language->text('frmtpnetsms', 'admin_settings_title'));

        $form = new Form('setting');

        $field = new TextField('user_id');
        $field->setLabel($language->text('frmtpnetsms', 'user_id_label'));
        $field->setValue($config->getValue('frmtpnetsms', 'user_id'));
        $field->setRequired();
        $form->addElement($field);

        $field = new TextField('password');
        $field->setLabel($language->text('frmtpnetsms', 'password_label'));
        $field->setValue($config->getValue('frmtpnetsms', 'password'));
        $field->setRequired();
        $form->addElement($field);

        $field = new TextField('originator');
        $field->setLabel($language->text('frmtpnetsms', 'originator_label'));
        $field->setValue($config->getValue('frmtpnetsms', 'originator'));
        $field->setRequired();
        $form->addElement($field);

        $field = new TextField('url');
        $field->setLabel($language->text('frmtpnetsms', 'url_label'));
        $field->setValue($config->getValue('frmtpnetsms', 'url'));
        $field->setRequired();
        $form->addElement($field);

        $element = new Submit('submit');
        $form->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $config->saveConfig('frmtpnetsms', 'user_id', $data['user_id']);
                $config->saveConfig('frmtpnetsms', 'password', $data['password']);
                $config->saveConfig('frmtpnetsms', 'originator', $data['originator']);
                $config->saveConfig('frmtpnetsms', 'url', $data['url']);
                OW::getFeedback()->info($language->text('frmtpnetsms', 'saved_successfully'));
            }
        }

        $this->addForm($form);
    }
}