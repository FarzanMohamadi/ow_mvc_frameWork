<?php
/**
 * Send message email controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.8.0
 */

class BASE_CTRL_AjaxSendMessageToEmail extends OW_ActionController
{
    public function init()
    {
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }

        if ( !( OW::getUser()->isAuthenticated() && ( OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base') ) ) ) {
            throw new Redirect404Exception();
        }
    }

    public function sendMessage()
    {
        $userId = !empty($_POST['userId']) ? $_POST['userId'] : null;
        $subject = !empty($_POST['subject']) ? $_POST['subject'] : null;
        $message = !empty($_POST['message']) ? $_POST['message'] : null;

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( empty($user) )
        {
            exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('base', 'invalid_user'))));
        }

        if ( empty($subject) )
        {
            exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('base', 'empty_subject'))));
        }

        if ( empty($message) )
        {
            exit(json_encode(array('result' => false, 'message' => OW::getLanguage()->text('base', 'empty_message'))));
        }

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($user->getEmail());
        $mail->setSubject($subject);
        $mail->setHtmlContent($message);
        $mail->setTextContent(strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $message)));

        OW::getMailer()->send($mail);

        exit(json_encode(array('result' => true, 'message' => OW::getLanguage()->text('base', 'message_send'))));
    }
}
