<?php
class FRMRAHYABPAYAMGOSTARANSMS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $this->setPageHeading($language->text('frmrahyabpayamgostaransms', 'admin_settings_title'));
        $this->setPageTitle($language->text('frmrahyabpayamgostaransms', 'admin_settings_title'));

        $form = new Form('setting');

        $sender_field = new TextField('sender');
        $sender_field->setLabel($language->text('frmrahyabpayamgostaransms', 'sender_label'));
        $sender_field->setRequired();
        $form->addElement($sender_field);

        $username_field = new TextField('username');
        $username_field->setLabel($language->text('frmrahyabpayamgostaransms', 'username_label'));
        $username_field->setRequired();
        $form->addElement($username_field);

        $password_field = new TextField('password');
        $password_field->setLabel($language->text('frmrahyabpayamgostaransms', 'password_label'));
        $password_field->setRequired();
        $form->addElement($password_field);


        $url_field = new TextField('url');
        $url_field->setLabel($language->text('frmrahyabpayamgostaransms', 'url_label'));
        $url_field->setRequired();
        $form->addElement($url_field);

        $element = new Submit('submit');
        $form->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $config->saveConfig('frmrahyabpayamgostaransms', 'sender', $data['sender']);
                $config->saveConfig('frmrahyabpayamgostaransms', 'username', $data['username']);
                $config->saveConfig('frmrahyabpayamgostaransms', 'password', $data['password']);
                $config->saveConfig('frmrahyabpayamgostaransms', 'url', $data['url']);
                OW::getFeedback()->info($language->text('frmrahyabpayamgostaransms', 'saved_successfully'));
            }
        }

        $sender_field->setValue($config->getValue('frmrahyabpayamgostaransms', 'sender'));

        $username_field->setValue($config->getValue('frmrahyabpayamgostaransms', 'username'));

        $password_field->setValue($config->getValue('frmrahyabpayamgostaransms', 'password'));

        $url_field->setValue($config->getValue('frmrahyabpayamgostaransms', 'url'));

        $this->addForm($form);
    }
}