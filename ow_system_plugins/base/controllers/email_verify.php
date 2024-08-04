<?php
/**
 * Email Verify controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class BASE_CTRL_EmailVerify extends OW_ActionController
{
    protected $questionService;
    protected $emailVerifyService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();

        $this->userService = BOL_UserService::getInstance();
    }

    protected function setMasterPage()
    {
         OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function index( $params )
    {
        if( OW::getRequest()->isAjax() )
        {
            echo "{message:'user is not verified'}";
            exit;
        }

        $this->setMasterPage();

        $this->setDocumentKey('email_verify_page');

        $userId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( (int) $user->emailVerify === 1 )
        {
            $this->redirect(OW::getRouter()->uriForRoute('base_member_dashboard'));
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $emailVerifyForm = new Form('emailVerifyForm');

        $email = new TextField('email');
        $email->setLabel($language->text('base', 'questions_question_email_correction_label'));
        //$email->setRequired();
        $email->addValidator(new BASE_CLASS_EmailVerifyValidator());

        $emailVerifyForm->addElement($email);

        $submit = new Submit('sendVerifyMail');
        $submit->setValue($language->text('base', 'email_verify_send_verify_mail_button_label'));

        $emailVerifyForm->addElement($submit);
        $this->addForm($emailVerifyForm);

        if ( OW::getRequest()->isPost() )
        {
            if ( $emailVerifyForm->isValid($_POST) )
            {
                $data = $emailVerifyForm->getValues();

                $email = htmlspecialchars(trim($data['email']));

                if ( $user->email != $email )
                {
                    BOL_UserService::getInstance()->updateEmail($user->id, $email);
                    $user->email = $email;
                }

                $this->emailVerifyService->sendUserVerificationMail($user);

                $this->redirect();
            }
        }
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_EMAIL_VERIFY_FORM_RENDER, array('this' => $this)));
    }

    public function verify( $params )
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $code = null;
        if ( isset($params['code']) )
        {
            $code = $params['code'];
            $this->emailVerifyService->verifyEmail($code);
        }
    }

    public function verifyForm( $params )
    {
        $this->setMasterPage();
        $language = OW::getLanguage();

        if ( OW::getUser()->isAuthenticated() )
        {
            $userId = OW::getUser()->getId();
            $user = BOL_UserService::getInstance()->findUserById($userId);
            if ( (int) $user->emailVerify === 1 )
            {
                $this->redirect(OW::getRouter()->uriForRoute('base_member_dashboard'));
            }
        }

        $this->setDocumentKey('email_verify_index_page');

        $this->setPageHeading($language->text('base', 'email_verify_index'));
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_EMAIL_VERIFY_FORM_RENDER, array('this' => $this, 'page' => 'verifyForm')));
        $form = new Form('verificationForm');

        $verificationCode = new TextField('verificationCode');
        $verificationCode->setLabel($language->text('base', 'email_verify_verification_code_label'));
        $verificationCode->addValidator(new BASE_CLASS_VerificationCodeValidator());

        $form->addElement($verificationCode);

        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'email_verify_verification_code_submit_button_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $code = $data['verificationCode'];

                $this->emailVerifyService->verifyEmail($code);
            }
        }
    }
}
