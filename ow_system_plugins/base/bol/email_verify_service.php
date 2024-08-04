<?php
/**
 * Email Verify Service Class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_EmailVerifyService
{
    const TYPE_USER_EMAIL = 'user';
    const TYPE_SITE_EMAIL = 'site';


    /**
     * @var BOL_EmailVerifyDao
     */
    private $emailVerifiedDao;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->emailVerifiedDao = BOL_EmailVerifyDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_EmailVerifyService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EmailVerifyService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @param BOL_EmailVerify $object
     */
    public function saveOrUpdate( BOL_EmailVerify $object )
    {
        $this->emailVerifiedDao->save($object);
    }

    /**
     * @param string $email
     * @param string $type
     * @return BOL_EmailVerify
     */
    public function findByEmail( $email, $type )
    {
        return $this->emailVerifiedDao->findByEmail($email, $type);
    }

    /**
     * @param string $email
     * @param int $userId
     * @param string $type
     * @return BOL_EmailVerify
     */
    public function findByEmailAndUserId( $email, $userId, $type )
    {
        return $this->emailVerifiedDao->findByEmailAndUserId($email, $userId, $type);
    }

    /**
     * @param string $hash
     * @return BOL_EmailVerify
     */
    public function findByHash( $hash )
    {
        return $this->emailVerifiedDao->findByHash($hash);
    }

    /**
     * @return string
     */
    public function generateHash()
    {
        return md5(FRMSecurityProvider::generateUniqueId());
    }

    /**
     * @param array $objects
     */
    public function batchReplace( $objects )
    {
        $this->emailVerifiedDao->batchReplace($objects);
    }

    /**
     * @param int $id
     */
    public function deleteById( $id )
    {
        $this->emailVerifiedDao->deleteById($id);
    }

    /**
     * @param int $userId
     */
    public function deleteByUserId( $userId )
    {
        $this->emailVerifiedDao->deleteByUserId($userId);
    }

    /**
     * @param int $stamp
     */
    public function deleteByCreatedStamp( $stamp )
    {
        $this->emailVerifiedDao->deleteByCreatedStamp($stamp);
    }

    public function sendVerificationMail( $type, $params )
    {
        $subject = $params['subject'];
        $template_html = $params['body_html'];
        $template_text = $params['body_text'];

        switch ( $type )
        {
            case self::TYPE_USER_EMAIL:
                $user = $params['user'];
                $email = $user->email;
                $userId = $user->id;

                break;

            case self::TYPE_SITE_EMAIL:
                $email = OW::getConfig()->getValue('base', 'unverify_site_email');
                $userId = 0;

                break;

            default :
                if ( isset($params['feedback']) && $params['feedback'] == true )
                {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'email_verify_verify_mail_was_not_sent'));
                }
                return;
        }

        $emailVerifiedData = BOL_EmailVerifyService::getInstance()->findByEmailAndUserId($email, $userId, $type);

        if ( $emailVerifiedData !== null )
        {
            $timeLimit = 60 * 60 * 24 * 3; // 3 days

            if ( time() - (int) $emailVerifiedData->createStamp >= $timeLimit )
            {
                $emailVerifiedData = null;
            }
        }

        if ( $emailVerifiedData === null )
        {
            $hash = BOL_EmailVerifyService::getInstance()->generateHash();
            $emailVerifiedData = new BOL_EmailVerify();
            $emailVerifiedData->userId = $userId;
            $emailVerifiedData->email = trim($email);
            $emailVerifiedData->hash = $hash;
            $emailVerifiedData->createStamp = time();
            $emailVerifiedData->type = $type;

            BOL_EmailVerifyService::getInstance()->batchReplace(array($emailVerifiedData));
        }

        $vars = array(
            'code' => $emailVerifiedData->hash,
        );

        $vars['url'] = OW::getRouter()->urlForRoute('base_email_verify_code_check', array('code' => $emailVerifiedData->hash));
        $vars['verification_page_url'] = OW::getRouter()->urlForRoute('base_email_verify_code_form');

        $language = OW::getLanguage();

        $subject = UTIL_String::replaceVars($subject, $vars);
        $template_html = UTIL_String::replaceVars($template_html, $vars);
        $template_text = UTIL_String::replaceVars($template_text, $vars);

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($emailVerifiedData->email);
        $mail->setSubject($subject);
        $mail->setHtmlContent($template_html);
        $mail->setTextContent($template_text);

        OW::getMailer()->send($mail);

        if ( isset($params['feedback']) && $params['feedback'] == true)
        {
            OW::getFeedback()->info($language->text('base', 'email_verify_verify_mail_was_sent'));
        }
    }

    public function sendUserVerificationMail( BOL_User $user, $feedback = true )
    {
        $vars = array(
            'username' => BOL_UserService::getInstance()->getDisplayName($user->id),
        );

        $language = OW::getLanguage();

        $subject = $language->text('base', 'email_verify_subject', $vars);
        $template_html = $language->text('base', 'email_verify_template_html', $vars);
        $template_text = $language->text('base', 'email_verify_template_text', $vars);

        $params = array(
            'user' => $user,
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text,
            'feedback' => $feedback
        );

        $this->sendVerificationMail(self::TYPE_USER_EMAIL, $params);
    }



    public function sendSiteVerificationMail($feedback = true)
    {
        $language = OW::getLanguage();

        $subject = $language->text('base', 'site_email_verify_subject');
        $template_html = $language->text('base', 'site_email_verify_template_html');
        $template_text = $language->text('base', 'site_email_verify_template_text');

        $params = array(
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text,
            'feedback' => $feedback
        );

        $this->sendVerificationMail(self::TYPE_SITE_EMAIL, $params);
    }

    /**
     * @param string $code
     */
    public function verifyEmail( $code )
    {
        $language = OW::getLanguage();

        /* @var BOL_EmailVerify */
        $data =  $this->verifyEmailCode($code);

        if ( $data['isValid'] ) {
            if (OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated()) {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'wait_for_approval'));
                OW_User::getInstance()->logout();
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute(('base_default_index')));
            } else {
                switch ($data["type"]) {
                    case self::TYPE_USER_EMAIL:

                        OW::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_default_index'));
                        break;

                    case self::TYPE_SITE_EMAIL:

                        OW::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('admin_settings_main'));
                        break;
                }
            }
        }
    }

    /**
     * @param string $code
     */
    public function verifyEmailCode( $code, $loginUser = true )
    {
        $result = ["isValid" => false, "type" => "", "message" => ""];

        /* @var BOL_EmailVerify */
        $emailVerifyData = $this->findByHash($code);
        if ( $emailVerifyData !== null )
        {
            $result["type"] = $emailVerifyData->type;
            switch ( $emailVerifyData->type )
            {
                case self::TYPE_USER_EMAIL:

                    $user = BOL_UserService::getInstance()->findUserById($emailVerifyData->userId);

                    if ( $user !== null )
                    {
                        if ( $loginUser ) {
                            if (OW::getUser()->isAuthenticated()) {
                                if (OW::getUser()->getId() !== $user->getId()) {
                                    OW::getUser()->logout();
                                }
                            }

                            OW::getUser()->login($user->getId());
                        }

                        $this->deleteById($emailVerifyData->id);

                        $user->emailVerify = true;
                        BOL_UserService::getInstance()->saveOrUpdate($user);

                        $result["isValid"] = true;
                    }

                    break;

                case self::TYPE_SITE_EMAIL:

                    OW::getConfig()->saveConfig('base', 'site_email', $emailVerifyData->email);
                    BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();

                    $result["isValid"] = true;

                    break;
            }
        }

        return $result;
    }
}
