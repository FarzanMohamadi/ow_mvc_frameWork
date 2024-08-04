<?php
/**
 * Forum admin action controller
 *
 */
class FRMJALALI_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param array $params
     */
	public function index(array $params = array())
	{
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmjalali', 'admin_settings_title'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmjalali', 'admin_settings_title'));
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmjalali_admin_config'));
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmjalali', 'settings_updated').'\')  }  }');

        $dateLocale = new Selectbox('dateLocale');
        $option = array();
        $option[1] = OW::getLanguage()->text('frmjalali', 'date_locale_jalali_format');
        $option[2] = OW::getLanguage()->text('frmjalali', 'date_locale_gregorian_format');
        $dateLocale->setValue(OW::getConfig()->getValue('frmjalali', 'dateLocale'));
        $dateLocale->setHasInvitation(false);
        $dateLocale->setRequired();
        $dateLocale->setOptions($option);
        $form->addElement($dateLocale);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $config->saveConfig('frmjalali', 'dateLocale', $form->getElement('dateLocale')->getValue());
            setcookie("frmjalali", "", time() - 3600);
            setcookie("frmjalali", $form->getElement('dateLocale')->getValue());
            exit(json_encode(array('result' => true)));
        }
	}
	
}