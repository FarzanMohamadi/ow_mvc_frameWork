<?php
class answerPostInGroupTest extends FRMTestUtilites
{
    private static $USER_NAME1 = 'user1';
    private static $PASSWORD = 'password123';
    private $groupService;
    private $test_user;
    private $group;
    private $test_caption;
    private $addReplySetting = null;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('newsfeed','groups'));

        $this->groupService = GROUPS_BOL_Service::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        ensure_session_active();
        FRMSecurityProvider::createUser(self::$USER_NAME1, 'user1@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->test_user = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME1);
        $addReplyFeatureConfig = OW::getConfig()->getValue('newsfeed', 'addReply');
        OW::getConfig()->saveConfig('newsfeed', 'addReply', 'on');
        if ($addReplyFeatureConfig=="on") {
            $this->addReplySetting=$addReplyFeatureConfig;
        }

        //create group
        $groupData = array(
            'title' => 'test',
            'description' => 'test',
            'whoCanInvite' => 'participant',
            'whoCanView' => 'anyone',
        );
        $this->group = $this->groupService->createGroup($this->test_user->getId(),$groupData);
    }

    public function testAnswerPostInGroup()
    {
        $this->test_caption = "answerPostInGroupCaptionTest";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        //add post by user1
        $this->url(OW_URL_HOME . "groups");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in(self::$USER_NAME1, self::$PASSWORD, true, false, $sessionId);

        try {
            $this->postAndAnswer("this is a simple text!");
        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function postAndAnswer($text)
    {
        $text = date('D, j M Y H:i:s O') . " : " . $text;
        //--------POST A NEW STATUS
        try {
            $this->url(OW_URL_HOME . "groups/" . $this->group->getId());
            $this->byClassName('ow_newsfeed_status_input')->click();
            $this->byClassName('ow_newsfeed_status_input')->value($text);
            $this->webDriver->executeScript(' document.getElementById("updatestatus_submit_button").click();');
            $this->waitUntilElementDisplayed('byXPath',
                '//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }
        //check is post exist
        sleep(2);
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId());//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]'));

        //reply post
        $this->waitUntilElementLoaded('byCssSelector','.ow_newsfeed_string.ow_small.ow_smallmargin');
        $this->byCssSelector('.ow_context_action')->click();
        $this->byCssSelector('.ow_context_action_list li a.groups_reply_to')->click();

        //answer post
        $text = "this is a simple text for reply.";
        $this->byClassName('ow_newsfeed_status_input')->click();
        $this->byClassName('ow_newsfeed_status_input')->value($text);
//        OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . 'answerpostingroup_1.png', $this->currentScreenshot());
        $this->byXPath('//input[@name="save"]')->click();

        $this->waitUntilElementDisplayed('byXPath',
            '//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]');

        //check is reply post exist
        sleep(2);
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId());//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"ow_newsfeed_content_status") and contains(text(),"' . $text . '")]'));
    }

    protected function tearDown()
    {
        //delete group
        $this->groupService->deleteGroup($this->group->getId());
        //delete users
        FRMSecurityProvider::deleteUser(self::$USER_NAME1);
        if (isset($this->addReplySetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'addReply', $this->addReplySetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'addReply');
        }
        parent::tearDown();
    }
}