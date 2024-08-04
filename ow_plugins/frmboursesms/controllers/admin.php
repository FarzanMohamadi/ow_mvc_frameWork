<?php
class FRMBOURSESMS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $this->setPageHeading($language->text('frmboursesms', 'admin_settings_title'));
        $this->setPageTitle($language->text('frmboursesms', 'admin_settings_title'));

        $form = new Form('setting');

        $apikey_field = new TextField('apikey');
        $apikey_field->setLabel($language->text('frmboursesms', 'apikey_label'));
        $apikey_field->setRequired();
        $form->addElement($apikey_field);

        $sender_field = new TextField('sender');
        $sender_field->setLabel($language->text('frmboursesms', 'sender_label'));
        $sender_field->setRequired();
        $form->addElement($sender_field);


        $url_field = new TextField('url');
        $url_field->setLabel($language->text('frmboursesms', 'url_label'));
        $url_field->setRequired();
        $form->addElement($url_field);

        $element = new Submit('submit');
        $form->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $config->saveConfig('frmboursesms', 'apikey', $data['apikey']);
                $config->saveConfig('frmboursesms', 'sender', $data['sender']);
                $config->saveConfig('frmboursesms', 'url', $data['url']);
                OW::getFeedback()->info($language->text('frmboursesms', 'saved_successfully'));
            }
        }

        $apikey_field->setValue($config->getValue('frmboursesms', 'apikey'));

        $sender_field->setValue($config->getValue('frmboursesms', 'sender'));

        $url_field->setValue($config->getValue('frmboursesms', 'url'));

        $this->addForm($form);
    }
}