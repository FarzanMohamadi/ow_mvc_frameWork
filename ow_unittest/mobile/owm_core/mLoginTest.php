<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/11/29
 */

class mLoginTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $TEST_PASSWORD_WRONG = 'WRONG';

    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array());
        $this->checkIfMobileIsActive();
        ensure_session_active();

        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1969/3/21","1",$accountType, 'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {
        //----SCENARIO 1 -
        // LOGIN from sign-in page
        // User input is correct
        // User is in.
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        //----------USER correct input
        $this->mobile_sign_in($this->TEST_USER1_NAME,$this->TEST_PASSWORD);
        self::assertTrue(true);
    }

    public function testScenario2()
    {
        //----SCENARIO 1 -
        // LOGIN from sign-page
        // User input is wrong
        // captcha is shown
        // User input is correct
        // User is in.

        $test_caption = "mLoginTest-testScenario2";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        //----------USER wrong input => captcha => correct input
        try{
            //--------wrong
            $this->url(OW_URL_HOME."sign-in");
            $form = $this->byCssSelector('#main form[name="sign-in"]');
            $form->byName('identity')->clear();
            $form->byName('identity')->value($this->TEST_USER1_NAME);
            $form->byName('password')->clear();
            $form->byName('password')->value($this->TEST_PASSWORD_WRONG);

            $form->byName('submit')->submit();
            sleep(1);
            $this->waitUntilElementLoaded('byId', 'owm_header_right_btn', 5000);
            self::assertTrue($this->checkIfXPathExists('//form[@name="sign-in"]'));

            //-------correct
            $form = $this->byCssSelector('#main form[name="sign-in"]');
            $form->byName('identity')->clear();
            $form->byName('identity')->value($this->TEST_USER1_NAME);
            $form->byName('password')->clear();
            $form->byName('password')->value($this->TEST_PASSWORD);

            //captcha
//            $this->waitUntilElementLoaded('byXPath', '//*[@id="main"]//input[@name="captchaField"]', 5000);
            $captchaExists = $this->checkIfXPathExists('//*[@id="main"]//input[@name="captchaField"]');
            if($captchaExists) {
                $cp = $form->byName('captchaField');
                if ($cp->displayed()) {
                    ensure_no_session();
                    session_id($sessionId);
                    @session_start();
                    $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
                    session_write_close();
                    $cp->clear();
                    $cp->value($captchaText);
                }
            }

            $form->byName('submit')->submit();
            sleep(1);
            $this->waitUntilElementLoaded('byId', 'owm_header_right_btn', 5000);
            self::assertFalse($this->checkIfXPathExists('//form[@name="sign-in"]'));
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario3()
    {
        //----SCENARIO 1 -
        // LOGIN FROM right menu
        // User input is wrong
        // captcha is shown
        // User input is correct
        // User is in.

        $test_caption = "mLoginTest-testScenario3";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        try{
            //----------USER wrong input
            $this->mobile_sign_in($this->user1->getUsername(), $this->TEST_PASSWORD_WRONG, true, $sessionId);

            //-----------Correct
            $this->mobile_sign_in($this->user1->getUsername(), $this->TEST_PASSWORD, true, $sessionId);

            self::assertFalse($this->checkIfXPathExists('//form[@name="sign-in"]'));
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        if(isset($this->user1)) {
            FRMSecurityProvider::deleteUser($this->user1->getUsername());
        }
        if(isset($this->user2)) {
            FRMSecurityProvider::deleteUser($this->user2->getUsername());
        }
        parent::tearDown();
    }
}