<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpasswordstrengthmeter.controllers
 * @since 1.0
 */
class FRMPASSWORDSTRENGTHMETER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmpasswordstrengthmeter', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmpasswordstrengthmeter', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmpasswordstrengthmeter');
        
        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmpasswordstrengthmeter.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("'.OW::getLanguage()->text("frmpasswordstrengthmeter", "settings_successfuly_saved").'");}else{OW.error("Parser error");}}');

        $minimumCharacter = new TextField('minimumCharacter');
        $minimumCharacter->setLabel($language->text('frmpasswordstrengthmeter','minimum_length_label'));
        $minimumCharacter->setRequired();
        $minimumCharacter->setValue($configs['minimumCharacter']);
        $minimumCharacter->addValidator(new IntValidator(1));
        $form->addElement($minimumCharacter);

        $minimumRequirementPasswordStrength = new Selectbox('minimumRequirementPasswordStrength');
        $options = array();
        $options[1] = OW::getLanguage()->text("frmpasswordstrengthmeter", "strength_poor_label");
        $options[2] = OW::getLanguage()->text("frmpasswordstrengthmeter", "strength_weak_label");
        $options[3] = OW::getLanguage()->text("frmpasswordstrengthmeter", "strength_good_label");
        $options[4] = OW::getLanguage()->text("frmpasswordstrengthmeter", "strength_excellent_label");
        $minimumRequirementPasswordStrength->setHasInvitation(false);
        $minimumRequirementPasswordStrength->setOptions($options);
        $minimumRequirementPasswordStrength->setRequired();
        $minimumRequirementPasswordStrength->setValue($configs['minimumRequirementPasswordStrength']);
        $form->addElement($minimumRequirementPasswordStrength);

        $submit = new Submit('save');
        $form->addElement($submit);
        
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $config->saveConfig('frmpasswordstrengthmeter', 'minimumCharacter', $form->getElement('minimumCharacter')->getValue());
                $config->saveConfig('frmpasswordstrengthmeter', 'minimumRequirementPasswordStrength', $form->getElement('minimumRequirementPasswordStrength')->getValue());
                exit(json_encode(array('result' => true)));
            }
        }
    }
}