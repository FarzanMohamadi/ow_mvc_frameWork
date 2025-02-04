<?php
class newsfeedTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $test_caption;
    protected $file_path;
    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('newsfeed'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME, "user1@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function  testDeletePost()
    {
        $this->test_caption = "deleteNewsfeedPostTest";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        $this->sign_in($this->user1->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        //create a post and delete
        try {
            $this->postAndDelete("this is a simple text!");
        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function postAndDelete($text)
    {
        $text = date('D, j M Y H:i:s O') . " : " . $text;
        //--------POST A NEW STATUS
        try {
            $this->url(OW_URL_HOME . "dashboard");
            $this->byClassName('ow_newsfeed_status_input')->click();
            $this->byClassName('ow_newsfeed_status_input')->value($text);
            $statusPrivacy = $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only

            $this->byXPath('//input[@name="save"]')->click();
            $this->waitUntilElementDisplayed('byXPath',
                '//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }

        //check if post exist
        sleep(2);
        $this->url(OW_URL_HOME . "dashboard");//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]'));

        //delete post
        $this->waitUntilElementLoaded('byCssSelector','.ow_newsfeed_string.ow_small.ow_smallmargin');
        $this->byCssSelector('.ow_context_action')->click();
        $deleteButton = $this->waitUntilElementDisplayed('byXPath',
            "//*[contains(@class, 'newsfeed_remove_btn owm_red_btn')]");
        $deleteButton->click();
        $this->acceptConfirm();

        //check if post exist
        sleep(2);
        $this->url(OW_URL_HOME . "dashboard");
        $DeleteDiv = $this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]');
        self::assertFalse($DeleteDiv);
    }

    public function tearDown()
    {
        if ($this->isSkipped)
            return;
        //delete user
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        parent::tearDown();
    }
}