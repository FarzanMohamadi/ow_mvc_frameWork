<?php
/**
 * Main page
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.controllers
 * @since 1.0
 */
class FRMCONTACTUS_MCTRL_Contact extends OW_MobileActionController
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
        if($config->configExists('frmcontactus','adminComment') && $config->getValue('frmcontactus', 'adminComment') != "") {
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
            $this->assign('isAuthenticated', false);
        }
        $fieldFrom->setRequired();
        $fieldFrom->addValidator(new EmailValidator());
        $form->addElement($fieldFrom);

        $fieldSubject = new TextField('subject');
        $fieldSubject->setLabel($this->text('frmcontactus', 'form_label_subject'));
        $fieldSubject->setRequired();
        $form->addElement($fieldSubject);

        $fieldMessage = new Textarea('message');
        $fieldMessage->setLabel($this->text('frmcontactus', 'form_label_message'));
        $fieldMessage->setRequired();
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

                $mail = OW::getMailer()->createMail();
                $mail->addRecipientEmail($contactEmails[$data['to']]['email']);
                $mail->setSender($data['from']);
                $mail->setSenderSuffix(false);
                $mail->setSubject($data['subject']);
                $mail->setTextContent($data['message']);
                $mail->setHtmlContent($data['message']);
                $frmcontactus = FRMCONTACTUS_BOL_Service::getInstance();
                $frmcontactus->addUserInformation($data['subject'],$data['from'],$contactEmails[$data['to']]['label'],$data['message']);
                OW::getMailer()->addToQueue($mail);

                OW::getSession()->set('frmcontactus.dept', $contactEmails[$data['to']]['label']);
                $this->redirectToAction('sent');
            }
        }
    }

    public function sent()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmcontactus', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmcontactus', 'index_page_heading'));
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

        /*$feedback = $this->text('frmcontactus', 'message_sent', ( $dept === null ) ? null : array('dept' => $dept));
        $this->assign('feedback', $feedback);*/
        OW::getFeedback()->info(OW::getLanguage()->text('frmcontactus', 'message_sent', ( $dept === null ) ? null : array('dept' => $dept)));
        $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.index'));

    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
}