<?php
class consoleItemsTest extends FRMTestUtilites
{
    private $TEST_USER_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $test_caption;
    private $userService;
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('mailbox'));
        $this->checkRequiredPlugins(array('notifications'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER_NAME, "user1@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USER_NAME);
    }

    public function  testActiveConsole()
    {
        $this->test_caption = "deleteNewsfeedPostTest";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME);
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in($this->user->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);

        $myProfileUrl = OW_URL_HOME ."user/".$this->TEST_USER_NAME ;
        $this->url($myProfileUrl);

        //profile
        $this->waitUntilElementLoaded('byClassName','console_my_profile_no_avatar');
        $this->byClassName('console_my_profile_no_avatar')->click();
        $res = $this->checkIfXPathExists('//*[contains(@class,"ow_console_dropdown_cont")]/a');
        if($res) {
            $item1 = $this->byClassName('ow_console_dropdown_cont');
            self::assertEquals($myProfileUrl, $item1->byXPath('./a')->attribute('href'));
            $this->byXPath('//*[contains(@class,"ow_console_dropdown_cont")]/a')->click();
        }
        //error
        /*self::assertTrue($this->checkIfXPathExists("//a[contains(@href='" . $myProfileUrl . "')]"));
        self::assertTrue($this->checkIfXPathExists("//a[contains(@href='" . $myProfileUrl . "')]/@href"));
        $this -> assertEquals(1 ,count($this->byXPath("//a[contains(@href='".$myProfileUrl."')]")->size()));*/

        //notifications
        $this->waitUntilElementLoaded('byClassName','ow_notification_list');
        $this->byClassName('ow_notification_list')->click();
        self::assertTrue($this->checkIfXPathExists('//*[contains(@class,"ow_console_view_all_btn")]'));
        $this->byXPath('//*[contains(@class,"ow_console_view_all_btn")]')->click();
    }
    public function tearDown()
    {
        if ($this->isSkipped)
            return;
        //delete user
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}