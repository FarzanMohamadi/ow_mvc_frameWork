<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmutual.controllers
 * @since 1.0
 */
class FRMMUTUAL_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index(array $params = array())
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmmutual', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmmutual', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmmutual');

        $form = new Form('settings');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmmutual.admin'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("frmmutual", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

        $numberOfMutualFriends = new TextField('numberOfMutualFriends');
        $numberOfMutualFriends->setLabel($language->text('frmmutual','numberOfMutualFriends'));
        $numberOfMutualFriends->setRequired();
        $numberOfMutualFriends->setValue($configs['numberOfMutualFriends']);
        $numberOfMutualFriends->addValidator(new IntValidator(1));
        $form->addElement($numberOfMutualFriends);

        $submit = new Submit('save');
        $form->addElement($submit);

        $this->addForm($form);

        if (OW::getRequest()->isAjax()) {
            if ($form->isValid($_POST)) {
                $config->saveConfig('frmmutual', 'numberOfMutualFriends', $form->getElement('numberOfMutualFriends')->getValue());
                exit(json_encode(array('result' => true)));
            }
        }
    }

}
