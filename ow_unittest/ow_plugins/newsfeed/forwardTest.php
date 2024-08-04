<?php
class forwardTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1, $user2, $user3, $user4;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('friends', 'privacy'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME, "user1@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME, "user2@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);

        // user1 and user2 are friends
        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(), $this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(), $this->user1->getId());
    }

    public function  testForwardPost()
    {
        //self::markTestSkipped();

        $this->test_caption = "forwardNewsfeedPostTest";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        $this->sign_in($this->user1->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        //create a post and delete

        try {
            $this->postAndForward("this is a simple text!");

        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }
    public function postAndForward($text)
    {
        $text = date('D, j M Y H:i:s O') . " : " . $text;
        //--------POST A NEW STATUS
        try {
            $this->url(OW_URL_HOME . "dashboard");
            $this->byClassName('ow_newsfeed_status_input')->click();
            $this->byClassName('ow_newsfeed_status_input')->value($text);
            $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy = $this->byName('statusPrivacy');
            $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only

            $this->byXPath('//input[@name="save"]')->click();
            $this->waitUntilElementDisplayed('byXPath',
                '//div[contains(@class,"ow_newsfeed_content_status") and normalize-space(text()) = "'.$text.'"]');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
            return false;
        }

        //check is post exist
        sleep(2);
        $this->url(OW_URL_HOME . "dashboard");//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]',
            DEFAULT_TIMEOUT_MILLIS));
        //forward post
        $this->waitUntilElementLoaded('byCssSelector','.ow_newsfeed_string.ow_small.ow_smallmargin');
        $this->byCssSelector('.ow_context_action')->click();
        $this->byXPath("//*[contains(@class, 'newsfeed_forward_btn item')]")->click();
        //select user tab
        $contentMenu = $this->waitUntilElementLoaded('byXPath',
            "//div[contains(@class,'floatbox_canvas_active')]//ul[contains(@class,'ow_content_menu')]");
        $contentMenu->byXPath('li[2]')->click();
        //select user
        $userListItem = $this->waitUntilElementLoaded('byXPath',
            "//div[contains(@class,'floatbox_canvas_active')]//div[@class='asl_users']//div[contains(@class,'ow_user_list_item ')]");
        $userListItem->click();
       //click send button
        $submitButton = $this->waitUntilElementLoaded('byXPath',
            "//div[contains(@class,'floatbox_canvas_active')]//div[@class='submit_cont']//input[@class='submit']");
//        $this->saveSnapshot("temp-forward-user-selected");
        $submitButton->click();
        $this->waitUntilElementDisplayed('byCssSelector', '.ow_message_node.info');
//        $this->saveSnapshot("temp-forward-success-message");

        //check post is forward
        $this->url(OW_URL_HOME . "user/".$this->TEST_USER1_NAME);//user1 profile
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]',
            DEFAULT_TIMEOUT_MILLIS));

    }
    public function tearDown()
    {
        if ($this->isSkipped)
            return;
        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        parent::tearDown();
    }
}