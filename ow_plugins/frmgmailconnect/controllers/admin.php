<?php
class FRMGMAILCONNECT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
	public function __construct() {
        parent::__construct();
    }
	public function index(){	

		$form = new FRMGMAILCONNECT_AccessForm();
		$this->addForm($form);
	

		if ( OW::getRequest()->isPost() && $form->isValid($_POST) ){
	
			if ( $form->process() ){
				OW::getFeedback()->info(OW::getLanguage()->text('frmgmailconnect', 'register_app_success'));
                $this->redirect();
			}
		
            OW::getFeedback()->error(OW::getLanguage()->text('frmgmailconnect', 'register_app_failed'));
			$this->redirect();
		}  
		$this->assign('returnUrl',OW::getRouter()->urlForRoute('frmgmailconnect_oauth'));
		OW::getDocument()->setHeading(OW::getLanguage()->text('frmgmailconnect', 'heading_configuration'));
        OW::getDocument()->setHeadingIconClass('ow_ic_friends');
	}

}


class FRMGMAILCONNECT_AccessForm extends Form {

  public function __construct()
  {
    parent::__construct('FRMGMAILCONNECT_AccessForm');
    $service = FRMGMAILCONNECT_BOL_Service::getInstance();
    $conf = $service->getProperties();
    $field = new TextField('clientId');
    $field->setRequired(true);
    $field->setValue($conf->client_id);
    $this->addElement($field);

    $field = new TextField('clientSecret');
    $field->setRequired(true);
    $field->setValue($conf->client_secret);
    $this->addElement($field);

    $submit = new Submit('save');
    $submit->setValue(OW::getLanguage()->text('frmgmailconnect', 'save_btn_label'));
    $this->addElement($submit);
  }

  public function process()
  {
    $values = $this->getValues();
    $service = FRMGMAILCONNECT_BOL_Service::getInstance();
    $conf = new FRMGMAILCONNECT_BOL_Config();
    $conf->client_id = trim($values['clientId']);
    $conf->client_secret = trim($values['clientSecret']);
    return $service->saveProperties($conf);
  }
}