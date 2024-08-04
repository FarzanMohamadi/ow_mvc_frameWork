<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip.controllers
 * @since 1.0
 */
class FRMBLOCKINGIP_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmblockingip', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmblockingip', 'admin_page_title'));
        $config = OW::getConfig();
        $configs = $config->getValues('frmblockingip');
        
        $form = new Form('settings');
//        $form->setAjax();
//        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmblockingip.admin'));
//        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("frmblockingip", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

        $loginCaptcha = new CheckboxField('loginCaptcha');
        $loginCaptcha->setValue($configs['loginCaptcha']);
        $form->addElement($loginCaptcha);

        $tryCountCaptcha = new TextField('tryCountCaptcha');
        $tryCountCaptcha->setLabel($language->text('frmblockingip','captcha_try_label'));
        $tryCountCaptcha->addValidator(new IntValidator(0));
        $tryCountCaptcha->setValue($configs['try_count_captcha']);
        $form->addElement($tryCountCaptcha);

        $block = new CheckboxField('block');
        $block->setValue($configs['block']);
        $form->addElement($block);

        $tryCountBlock = new TextField('tryCountBlock');
        $tryCountBlock->setLabel($language->text('frmblockingip','block_try_label'));
        $tryCountBlock->setRequired();
        $tryCountBlock->addValidator(new IntValidator(1));
        $tryCountBlock->setValue($configs['try_count_block']);
        $form->addElement($tryCountBlock);

        $expTime = new TextField('expTime');
        $expTime->setLabel($language->text('frmblockingip','Lock_per_minute_label'));
        $expTime->setRequired();
        $expTime->setValue($configs['expire_time']);
        $expTime->addValidator(new IntValidator(1));
        $form->addElement($expTime);

        $submit = new Submit('save');
        $form->addElement($submit);
        
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $config->saveConfig('frmblockingip', 'loginCaptcha', $form->getElement('loginCaptcha')->getValue());
                $config->saveConfig('frmblockingip', 'try_count_captcha', $form->getElement('tryCountCaptcha')->getValue());
                $config->saveConfig('frmblockingip', 'block', $form->getElement('block')->getValue());
                $config->saveConfig('frmblockingip', 'try_count_block', $form->getElement('tryCountBlock')->getValue());
                $config->saveConfig('frmblockingip', 'expire_time', $form->getElement('expTime')->getValue());

            }
        }
    }
}
