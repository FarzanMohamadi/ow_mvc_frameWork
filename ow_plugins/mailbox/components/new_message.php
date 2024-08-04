<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.components
 * @since 1.6.1
 * */
class MAILBOX_CMP_NewMessage extends OW_Component
{

    public function __construct()
    {
        parent::__construct();

        $form = OW::getClassInstance("MAILBOX_CLASS_NewMessageForm", $this);
        /* @var $user MAILBOX_CLASS_NewMessageForm */
        
        $this->addForm($form);

        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $this->assign('displayCaptcha', false);

        $configs = OW::getConfig()->getValues('mailbox');
        $this->assign('enableAttachments', !empty($configs['enable_attachments']));
    }
}