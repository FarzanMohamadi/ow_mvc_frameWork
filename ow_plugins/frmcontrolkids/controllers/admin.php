<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontrolkids.controllers
 * @since 1.0
 */
class FRMCONTROLKIDS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmcontrolkids', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmcontrolkids', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmcontrolkids');
        
        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmcontrolkids.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("'. OW::getLanguage()->text('frmcontrolkids', 'setting_saved') .'");}else{OW.error("Parser error");}}');


        $minimumKidsAge = new TextField('kidsAge');
        $minimumKidsAge->setLabel($language->text('frmcontrolkids','minimumKidsAgeLabel'));
        $minimumKidsAge->setRequired();
        $minimumKidsAge->addValidator(new IntValidator(1));
        $minimumKidsAge->setValue($configs['kidsAge']);
        $form->addElement($minimumKidsAge);

        $marginTime = new TextField('marginTime');
        $marginTime->setLabel($language->text('frmcontrolkids','marginTimeLabel'));
        $marginTime->setRequired();
        $marginTime->addValidator(new IntValidator(1));
        $marginTime->setValue($configs['marginTime']);
        $form->addElement($marginTime);

        $submit = new Submit('save');
        $form->addElement($submit);
        
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $config->saveConfig('frmcontrolkids', 'kidsAge', $form->getElement('kidsAge')->getValue());
                $config->saveConfig('frmcontrolkids', 'marginTime', $form->getElement('marginTime')->getValue());
                exit(json_encode(array('result' => true)));
            }
        }
    }
}
