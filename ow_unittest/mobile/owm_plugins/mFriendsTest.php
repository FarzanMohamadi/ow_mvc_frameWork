<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/11/26
 */

class mFriendsTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "testuser1";
    private $TEST_USER2_NAME = "testuser2";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('friends'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser($this->TEST_USER1_NAME, $this->TEST_USER1_NAME."@gmail.com", $this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);

        FRMSecurityProvider::createUser($this->TEST_USER2_NAME, $this->TEST_USER2_NAME."@gmail.com", $this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);

        //$friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        //$friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        //$friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());
    }

    public function testScenario1()
    {
        //----SCENARIO 1 -
        // User1 goes to User2 page
        // User1 sends request
        // User2 accepts request

        $test_caption = "mFriendsTest-testScenario1";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        //----------Send Request
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url("/user/". $this->TEST_USER2_NAME);
            $this->byXPath('//*[contains(@class,"owm_profile_btns")]//a[contains(@id, "friendship")]')->click();
            sleep(1);
            $this->byCssSelector('button.btn-orange')->click(); // dialog
            sleep(1);
            $this->url(  'sign-out');
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------Accept Req
        try {
            $this->mobile_sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byClassName','owm_content_header_count',5000);

            $this->byClassName('owm_content_header_count')->click();
            $this->waitUntilElementLoaded("byXPath",'//*[contains(@class,"owm_friend_request_accept")]');
            $this->byClassName('owm_friend_request_accept')->click();
            sleep(2);

            //check result
            $this->url("user/".$this->user1->getUsername());
            self::assertTrue($this->checkIfXPathExists('//a[contains(@onclick,"friends/action/cancel")]'));

        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario2()
    {
        //----SCENARIO 2 -
        // User1 goes to User2 page
        // User1 sends request
        // User2 rejects request

        $test_caption = "mFriendsTest-testScenario2";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        //----------Send Request
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url("/user/". $this->TEST_USER2_NAME);
            $this->byXPath('//*[contains(@class,"owm_profile_btns")]//a[contains(@id, "friendship")]')->click();
            sleep(1);
            $this->byCssSelector('button.btn-orange')->click(); // dialog
            sleep(1);
            $this->url(OW_URL_HOME . 'sign-out');
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        sleep(3);
        //----------Ignore Req
        try {
            $this->mobile_sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byClassName','owm_content_header_count',5000);

            $this->byClassName('owm_content_header_count')->click();
            $this->waitUntilElementLoaded("byXPath",'//*[contains(@class,"owm_friend_request_ignore")]');
            $this->byClassName('owm_friend_request_ignore')->click();

            //check result
            $this->url("user/".$this->user1->getUsername());
            self::assertFalse($this->checkIfXPathExists('//a[contains(@onclick,"friends/action/cancel")]'));

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
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        parent::tearDown();
    }
}