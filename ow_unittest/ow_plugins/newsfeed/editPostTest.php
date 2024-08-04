<?php
class editPostTest extends FRMTestUtilites
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

    public function testEditPost()
    {
        $this->test_caption = "editNewsfeedPostTest";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        //create a post and delete
        $this->sign_in($this->user1->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        try {
            $this->postAndEdit("this is a simple text!");
        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function postAndEdit($text)
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
            return;
        }

        //check if post exist
        sleep(2);
        $this->url(OW_URL_HOME . "dashboard"); //refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]',
            DEFAULT_TIMEOUT_MILLIS));

        //edit post
        $this->waitUntilElementLoaded('byCssSelector','.ow_newsfeed_string.ow_small.ow_smallmargin');
        $this->byCssSelector('.ow_context_action')->click();
        $this->byXPath("//*[contains(@class, 'newsfeed_edit_btn item')]")->click();
        $status = $this->waitUntilElementLoaded('byXPath', '//form[@name="edit_post"]//textarea[@name="status"]');
        $status->value($text."edited");
        $this->byClassName('floatbox_canvas_active')->byXPath('//input[@name="submit"]')->click();
        $this->waitUntilElementDisplayed('byCssSelector', '.ow_message_node.info');

        $this->url(OW_URL_HOME . "dashboard");//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text."edited" . '")]',
            DEFAULT_TIMEOUT_MILLIS));

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