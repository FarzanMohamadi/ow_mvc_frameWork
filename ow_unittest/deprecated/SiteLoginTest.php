<?php
/**
 * User: Hamed Tahmooresi
 * Date: 2/9/2016
 * Time: 2:11 PM
 */
//require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SiteViewTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = 'asdf@1111';
    private $TEST_WRONG_PASSWORD = '123';

    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array());
        ensure_session_active();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);
    }
    public function testSuccessfulLogin()
    {
        sleep(4);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        sleep(4);
        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        try{
            sleep(1);
            $this->sign_in($this->user->getUsername(),$this->TEST_CORRECT_PASSWORD,true,true,$sessionId);
        }catch (Exception $ex){
            $this->handleException($ex,'',false,false);
            return;
        }
        try
        {
            $this->waitUntilElementLoaded('byName','status');
            self::assertTrue(true);
        }catch (Exception $ex){
            $this->handleException($ex,'',true,false);
        }
    }
    public function testFailedLogin()
    {
        OW::getDbo()->query('truncate table `'.OW_DB_PREFIX.'frmblockingip_block_ip`');
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME.'sign-in');
        try{
            $form = $this->byCssSelector('.ow_page_container form[name="sign-in"]');
            $this->waitUntilElementLoaded('byName', 'identity');
            $form->byName('identity')->clear();
            $form->byName('identity')->value($this->TEST_USERNAME);
            $form->byName('password')->clear();
            $form->byName('password')->value($this->TEST_WRONG_PASSWORD);
            $form->submit();


            $this->waitUntilElementLoaded('byName','captchaField');
            self::assertTrue(true);
        }catch (Exception $ex){
            $this->handleException($ex,'SiteViewTest-testFailedLogin',true);
        }
    }
    public function tearDown()
    {
        if($this->isSkipped)
            return;
        
        OW::getDbo()->query('truncate table `'.OW_DB_PREFIX.'frmblockingip_block_ip`');

        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}