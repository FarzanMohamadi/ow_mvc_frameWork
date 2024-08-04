<?php
class FRMCLAMAV_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('checkIfClamavWorks', 60*24);
    }

    public function run()
    {
        //ignore
    }

    public function checkIfClamavWorks()
    {
        $path=OW_DIR_ROOT.'index.php';
        $checkFileCleanEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $path)));
        if(isset($checkFileCleanEvent->getData()['errorMessage'])){
            $user=BOL_UserService::getInstance()->findUserById(1);
            $mail = OW::getMailer()->createMail();
            $mail->addRecipientEmail($user->email);
            $mail->setSubject(OW::getLanguage()->text('frmclamav','clamav_exception_subject'));
            $mail->setHtmlContent(OW::getLanguage()->text('frmclamav','clamav_exception_body',array('message'=>$checkFileCleanEvent->getData()['errorMessage'])));
            $mail->setTextContent(OW::getLanguage()->text('frmclamav','clamav_exception_body',array('message'=>$checkFileCleanEvent->getData()['errorMessage'])));
            OW::getMailer()->addToQueue($mail);
        }
    }
}