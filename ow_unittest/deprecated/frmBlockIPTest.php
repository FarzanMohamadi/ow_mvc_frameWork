<?php
class frmBlockIPTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = 'asdf@1111';
    private $TEST_WRONG_PASSWORD = '123';

    private $user;
    private $config_initial;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmblockingip'));
        ensure_session_active();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);

        $config =  OW::getConfig();
        $this->config_initial = array();
        $this->config_initial['loginCaptcha'] = $config->getValue('frmblockingip', 'loginCaptcha');
        $this->config_initial['try_count_block'] = $config->getValue('frmblockingip', 'try_count_block');
        $this->config_initial['block'] = $config->getValue('frmblockingip', 'block');
        $this->config_initial['try_count_captcha'] = $config->getValue('frmblockingip', 'try_count_captcha');
        $this->config_initial['expire_time'] = $config->getValue('frmblockingip', 'expire_time');

        # new config
        $config->saveConfig('frmblockingip', 'loginCaptcha', 1);
        $config->saveConfig('frmblockingip', 'try_count_block', 3);
        $config->saveConfig('frmblockingip', 'block', 1);
        $config->saveConfig('frmblockingip', 'try_count_captcha', 1);
        $config->saveConfig('frmblockingip', 'expire_time', 1);
    }

    public function testBlockIP()
    {
        $this->url(OW_URL_HOME.'sign-in');
        try {
            $form = $this->byCssSelector('.ow_page_container .ow_sign_in_wrap form');
            $form->byName('identity')->clear();
            $form->byName('identity')->value($this->TEST_USERNAME);
            $form->byName('password')->clear();
            $form->byName('password')->value($this->TEST_WRONG_PASSWORD);
            $form->submit();
        }
        catch (Exception $ex) {
            $this->handleException($ex,'blockip-first-try');
        }

        try{
            sleep(5);
            $this->waitUntilElementLoaded('byName','captchaField');
            $form = $this->byCssSelector('.ow_page_container .ow_sign_in_wrap form');
            $form->byName('identity')->clear();
            $form->byName('password')->clear();
            $form->byName('identity')->value($this->TEST_USERNAME);
            $form->byName('password')->clear();
            $form->byName('password')->value($this->TEST_WRONG_PASSWORD);
            $form->byName('captchaField')->value('12hsd');
            $form->submit();

            sleep(5);
            $form = $this->byCssSelector('.ow_page_container .ow_sign_in_wrap form');
            $form->byName('identity')->clear();
            $form->byName('password')->clear();
            $form->byName('captchaField')->clear();
            $form->byName('identity')->value($this->TEST_USERNAME);
            $form->byName('password')->value($this->TEST_WRONG_PASSWORD);
            $form->byName('captchaField')->value('12hsd');
            $form->submit();

            sleep(5);
            $form = $this->byCssSelector('.ow_page_container .ow_sign_in_wrap form');
            $form->byName('identity')->clear();
            $form->byName('password')->clear();
            $form->byName('captchaField')->clear();
            $form->byName('identity')->value($this->TEST_USERNAME);
            $form->byName('password')->value($this->TEST_WRONG_PASSWORD);
            $form->byName('captchaField')->value('12hsd');
            $form->submit();
        }
        catch (Exception $ex) {
            $this->handleException($ex,'blockip-failed attempts');
        }

        try{
            # success
            sleep(70);
            $url =  OW_URL_HOME.'dashboard';
            $this->url($url);
            $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
            $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user->getUsername(),$this->TEST_CORRECT_PASSWORD,true,true,$sessionId);
        }
        catch (Exception $ex) {
            $this->handleException($ex,'blockip-success attempt');
        }
        self::assertTrue(true);
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        $config =  OW::getConfig();
        foreach($this->config_initial as $key => $value){
            $config->saveConfig('frmblockingip', $key, $value);
        }

        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}