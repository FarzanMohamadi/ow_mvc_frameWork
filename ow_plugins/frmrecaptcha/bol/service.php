<?php
class FRMRECAPTCHA_BOL_Service
{

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function setSiteKey( $siteKey ){
        OW::getConfig()->saveConfig("frmrecaptcha", "siteKey", $siteKey);
    }

    public function setSecretKey( $secretKey ){
        OW::getConfig()->saveConfig("frmrecaptcha", "secretKey", $secretKey);
    }

    public function getSiteKey(){

        if(OW::getConfig()->configExists("frmrecaptcha", "siteKey")){
            return OW::getConfig()->getValue('frmrecaptcha', 'siteKey');
        }
        return null;
    }


    public function verifyRecaptchaResponse(OW_Event $event){
        if (OW::getConfig()->configExists("frmrecaptcha", "siteKey") &&
            OW::getConfig()->configExists("frmrecaptcha", "secretKey")){
            $token = $_REQUEST['g-recaptcha-response'];

            if(isset($token)){
                $secretKey = OW::getConfig()->getValue('frmrecaptcha', 'secretKey');
                $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$token);
                $responseData = json_decode($verifyResponse, true);
                if(isset($responseData)){
                    if($responseData['success'])
                    {
                        return;
                    }
                    elseif(isset($responseData['error-codes']) && array_search('invalid-input-secret' ,$responseData['error-codes']) !== false){
                        $this->sendInvalidSecretKeyNotification();
                    }
                    else
                    {
                        $event = new OW_Event('base.bot_detected', array('isBot' => true));
                        OW::getEventManager()->trigger($event);
                        OW::getFeedback()->error(OW::getLanguage()->text('frmrecaptcha', 'recaptcha_verification_failed'));
                        OW::getApplication()->redirect();
                    }
                }
                else{
                    OW::getFeedback()->error(OW::getLanguage()->text('frmrecaptcha', 'recaptcha_verification_failed'));
                    OW::getApplication()->redirect();

                }
            }
            else{
                $event = new OW_Event('base.bot_detected', array('isBot' => true));
                OW::getEventManager()->trigger($event);
                OW::getFeedback()->error(OW::getLanguage()->text('frmrecaptcha', 'recaptcha_verification_failed'));
                OW::getApplication()->redirect();
            }

        }
    }

    public function activateRecaptcha(OW_Event $event){
        $params = $event->getParams();
        if ( isset($params['joinCtrl']) ){
            $joinCtrl = $params['joinCtrl'];
            if (OW::getConfig()->configExists("frmrecaptcha", "siteKey") &&
                OW::getConfig()->configExists("frmrecaptcha", "secretKey")){
                $joinCtrl->addComponent('invisibleRecaptchaCmp', new FRMRECAPTCHA_CMP_InvisibleRecaptcha($this->getSiteKey()));
                OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("frmrecaptcha")->getStaticJsUrl() . 'frmrecaptcha.js');
            }
        }
    }

    public function sendInvalidSecretKeyNotification(){
        $txt = OW::getLanguage()->text('frmrecaptcha', 'invalid_secret_key_notice_text');
        $html = OW::getLanguage()->text('frmrecaptcha', 'invalid_secret_key_notice_html');

        $subject = OW::getLanguage()->text('frmrecaptcha', 'invalid_secret_key_notice_subject');

        try
        {
            $mail = OW::getMailer()->createMail()
                ->addRecipientEmail(OW::getConfig()->getValue('base', 'site_email'))
                ->setTextContent($txt)
                ->setHtmlContent($html)
                ->setSubject($subject);

            OW::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
        }
    }




}