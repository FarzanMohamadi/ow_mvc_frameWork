<?php
class FRMRECAPTCHA_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index(){

        $form = new FRMRECAPTCHA_KeyPairForm();
        $this->addForm($form);


        if ( OW::getRequest()->isPost() && $form->isValid($_POST) ){

            if ( $form->process() ){
                OW::getFeedback()->info(OW::getLanguage()->text('frmrecaptcha', 'save_recaptcha_setting_success'));
                $this->redirect();
            }

            OW::getFeedback()->error(OW::getLanguage()->text('frmrecaptcha', 'save_recaptcha_setting_failed'));
            $this->redirect();
        }
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmrecaptcha', 'heading_configuration'));
        OW::getDocument()->setHeadingIconClass('ow_ic_friends');

    }

}

class FRMRECAPTCHA_KeyPairForm extends Form {

    public function __construct()
    {
        parent::__construct('FRMRECAPTCHA_KeyPairForm');
        $field = new TextField('siteKey');
        $field->setRequired(true);
        if (OW::getConfig()->configExists("frmrecaptcha", "siteKey")){
            $field->setValue(OW::getConfig()->getValue('frmrecaptcha', 'siteKey'));
        }
        $this->addElement($field);

        $field = new TextField('secretKey');
        $field->setRequired(true);
        if (OW::getConfig()->configExists("frmrecaptcha", "secretKey")){
            $field->setValue(OW::getConfig()->getValue('frmrecaptcha', 'secretKey'));
        }
        $this->addElement($field);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmrecaptcha', 'save_btn_label'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();
        $service = FRMRECAPTCHA_BOL_Service::getInstance();
        $service->setSiteKey(trim($values['siteKey']));
        $service->setSecretKey(trim($values['secretKey']));

        return true;
    }
}