<?php
/**
 * Send message email component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.8.0
 */
class BASE_CMP_SendMessageToEmail extends OW_Component
{
    public function __construct($userId)
    {
        parent::__construct();

        $form = new Form("send_message_form");
        $form->setAjax(true);
        $form->setAjaxResetOnSuccess(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_AjaxSendMessageToEmail', 'sendMessage'));

        $user = new HiddenField("userId");
        $user->setValue($userId);
        $form->addElement($user);

        $subject = new TextField('subject');
        $subject->setInvitation(OW::getLanguage()->text('base', 'subject'));
        $subject->setRequired(true);
        $form->addElement($subject);

        $textarea = new WysiwygTextarea("message",'base');
        $textarea->setInvitation(OW::getLanguage()->text('base', 'message_invitation'));
        $requiredValidator = new WyswygRequiredValidator();
        $requiredValidator->setErrorMessage(OW::getLanguage()->text('base', 'message_empty'));
        $textarea->addValidator($requiredValidator);

        $form->addElement($textarea);

        $submit = new Submit('send');
        $submit->setLabel(OW::getLanguage()->text('base', 'send'));
        $form->addElement($submit);

        $form->bindJsFunction(Form::BIND_SUCCESS, ' function ( data ) {

            if ( data.result )
            {
                OW.info(data.message);
            }
            else
            {
                OW.error(data.message);
            }

            if ( OW.getActiveFloatBox() )
            {
                OW.getActiveFloatBox().close();
            }

        } ');

        $this->addForm($form);
    }
}
