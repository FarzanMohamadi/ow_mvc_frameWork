<?php
/**
 * Main page
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.controllers
 * @since 1.0
 */
class FRMCONTACTUS_CTRL_Contact extends OW_ActionController
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmcontactus', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmcontactus', 'index_page_heading'));

        $contactEmails = array();
        $contacts = FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentList();
        foreach ( $contacts as $contact )
        {
            /* @var $contact FRMCONTACTUS_BOL_Department */
            $contactEmails[$contact->id]['label'] = $contact->label;
            $contactEmails[$contact->id]['email'] = $contact->email;
        }

        $form = new Form('contact_form');
        $text = "";
        $config = OW::getConfig();
        if($config->configExists('frmcontactus','adminComment')) {
            $text= $config->getValue('frmcontactus', 'adminComment');
            $eventForEnglishFieldSupport = new OW_Event('frmmultilingualsupport.show.data.in.multilingual', array('adminComment' => $text, 'entityType' => 'frmcontactus', 'display' => 'showAdminComment'));
            OW::getEventManager()->trigger($eventForEnglishFieldSupport);
            if (isset($eventForEnglishFieldSupport->getData()['multiData'])) {
                $text = $eventForEnglishFieldSupport->getData()['multiData'];
            }
            $this->assign('adminComment', $text);
        }
        $fieldTo = new Selectbox('to');
        foreach ( $contactEmails as $id => $value )
        {
            $fieldTo->addOption($id, $value['label']);
        }
        $fieldTo->setRequired();
        $fieldTo->setHasInvitation(false);
        $fieldTo->setLabel($this->text('frmcontactus', 'form_label_to'));
        $form->addElement($fieldTo);

        if ( OW::getUser()->isAuthenticated() )
        {
            $fieldFrom = new HiddenField('from');
            $fieldFrom->setValue( OW::getUser()->getEmail() );
            $this->assign('isAuthenticated', true);
        }else{
            $fieldFrom = new TextField('from');
            $fieldFrom->setLabel($this->text('frmcontactus', 'form_label_from'));
            $fieldFrom->addAttribute('placeholder', $this->text('frmcontactus', 'form_label_from'));
            $this->assign('isAuthenticated', false);
        }
        $fieldFrom->setRequired();
        $fieldFrom->addValidator(new EmailValidator());
        $form->addElement($fieldFrom);

        $fieldSubject = new TextField('subject');
        $fieldSubject->addAttribute('placeholder', $this->text('frmcontactus', 'form_label_subject'));
        $fieldSubject->setLabel($this->text('frmcontactus', 'form_label_subject'));
        $fieldSubject->setRequired();
        $form->addElement($fieldSubject);

        $fieldMessage = new Textarea('message');
        $fieldMessage->setLabel($this->text('frmcontactus', 'form_label_message'));
        $fieldMessage->setRequired();
        $fieldMessage->addAttribute('placeholder', $this->text('frmcontactus', 'form_label_message'));
        $form->addElement($fieldMessage);

        $fieldCaptcha = new CaptchaField('captcha');
        $fieldCaptcha->setLabel($this->text('frmcontactus', 'form_label_captcha'));
        $form->addElement($fieldCaptcha);
        $this->assign('captcha_present', 'true');

        $submit = new Submit('send');
        $submit->setValue($this->text('frmcontactus', 'form_label_submit'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                if ( !array_key_exists($data['to'], $contactEmails) )
                {
                    OW::getFeedback()->error($this->text('frmcontactus', 'no_department'));
                    return;
                }

                $subject = UTIL_HtmlTag::stripTagsAndJs($data['subject']);

                $message = UTIL_HtmlTag::stripTagsAndJs($data['message']);

                $from = UTIL_HtmlTag::stripTagsAndJs($data['from']);

                if(empty($subject) || empty($message) || empty($from)){
                    OW::getFeedback()->error(OW::getLanguage()->text("base", "form_validate_common_error_message"));
                    $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.index'));
                }

                $mail = OW::getMailer()->createMail();
                $mail->addRecipientEmail($contactEmails[$data['to']]['email']);
                $mail->setSender($from);
                $mail->setSenderSuffix(false);
                $mail->setSubject($subject);
                $mail->setTextContent($message);
                $mail->setHtmlContent($message);
                $frmcontactus = FRMCONTACTUS_BOL_Service::getInstance();
                $frmcontactus->addUserInformation($subject,$from,$contactEmails[$data['to']]['label'],$message);
                OW::getMailer()->addToQueue($mail);

                OW::getSession()->set('frmcontactus.dept', $contactEmails[$data['to']]['label']);
                $this->redirectToAction('sent');
            }
        }

        $this->assign('backgroundImage', OW::getPluginManager()->getPlugin('frmcontactus')->getStaticUrl().'img/bg.png');
        $this->setDocumentKey("contactus_index");
    }

    public function sent()
    {
        $dept = null;

        if ( OW::getSession()->isKeySet('frmcontactus.dept') )
        {
            $dept = OW::getSession()->get('frmcontactus.dept');
            OW::getSession()->delete('frmcontactus.dept');
        }
        else
        {
            $this->redirectToAction('index');
        }

        OW::getFeedback()->info(OW::getLanguage()->text('frmcontactus', 'message_sent', ( $dept === null ) ? null : array('dept' => $dept)));
        $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.index'));
        /*$feedback = $this->text('frmcontactus', 'message_sent', ( $dept === null ) ? null : array('dept' => $dept));
        $this->assign('feedback', $feedback);*/
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
}