<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/09/11
 */

class frmsmartscrollTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmadvancedscroll'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testSmartScroll()
    {
        //scenario 1:
        //login
        //scroll down
        //check if toTop is displayed

        $test_caption = "frmsmartscrollTest-testSmartScroll";
        $this->webDriver->prepare();
        $this->setScreenSize(1000, 500);

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER1
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'dashboard');
            $this->hide_element('demo-nav');
            $this->byClassName('ow_footer')->click();
            sleep(1);
            $res = $this->checkIfXPathExists('//*[@id="toTop"]');
            self::assertTrue($res);
            self::assertTrue($this->byId('toTop')->displayed());
            if(!$this->byId('toTop')->displayed()) {
                if (getenv("SNAPSHOT_DIR"))
                    OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
            }
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        parent::tearDown();
    }
}