<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmimport.controllers
 * @since 1.0
 */
class FRMIMPORT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmimport', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmimport', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmimport');
        
        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmimport.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("frmimport", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

        $useImportYahooField = new CheckboxField('use_import_yahoo');
        $useImportYahooField->setValue($configs['use_import_yahoo']);
        $form->addElement($useImportYahooField);

        $yahooIdField = new TextField('yahoo_id');
        $yahooIdField->setLabel($language->text('frmimport','yahoo_client_id'));
        $yahooIdField->setValue($configs['yahoo_id']);
        $form->addElement($yahooIdField);

        $yahooSecretField = new TextField('yahoo_secret');
        $yahooSecretField->setLabel($language->text('frmimport','yahoo_client_secret'));
        $yahooSecretField->setValue($configs['yahoo_secret']);
        $form->addElement($yahooSecretField);

        $useImportGoogleField = new CheckboxField('use_import_google');
        $useImportGoogleField->setValue($configs['use_import_google']);
        $form->addElement($useImportGoogleField);

        $googleIdField = new TextField('google_id');
        $googleIdField->setLabel($language->text('frmimport','google_client_id'));
        $googleIdField->setValue($configs['google_id']);
        $form->addElement($googleIdField);

        $googleSecretField = new TextField('google_secret');
        $googleSecretField->setLabel($language->text('frmimport','google_client_secret'));
        $googleSecretField->setValue($configs['google_secret']);
        $form->addElement($googleSecretField);

        $submit = new Submit('save');
        $form->addElement($submit);
        
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $config->saveConfig('frmimport', 'use_import_yahoo', $form->getElement('use_import_yahoo')->getValue());
                $config->saveConfig('frmimport', 'yahoo_id', $form->getElement('yahoo_id')->getValue());
                $config->saveConfig('frmimport', 'yahoo_secret', $form->getElement('yahoo_secret')->getValue());
                $config->saveConfig('frmimport', 'use_import_google', $form->getElement('use_import_google')->getValue());
                $config->saveConfig('frmimport', 'google_id', $form->getElement('google_id')->getValue());
                $config->saveConfig('frmimport', 'google_secret', $form->getElement('google_secret')->getValue());

                exit(json_encode(array('result' => true)));
            }
        }
    }
}
