<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/11/26
 */

class mForumTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2;


    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('forum'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {
        //----SCENARIO 1 -
        // User1 can see forum.
        // User1 creates a new topic.
        // User1 replies to the topic.

        $test_caption = "mForumTest-testScenario1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------CREATE A TOPIC
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . "forum");
            $this->byXPath('//*[contains(@class, "owm_list_item_view_header")]')->click();
            sleep(2);
            $this->byCssSelector("#content #owm_forum_new_topic_link")->click();
            sleep(2);
            $this->waitUntilElementLoaded('byXPath','//input[@name="title"]',5000);
            $this->byXPath('//input[@name="title"]')->value('Topic 1');
            $this->byClassName('owm_suitup-editor')->click();
            $this->byClassName('owm_suitup-editor')->value('As above, ...');
            sleep(1);
            $this->byXPath('//div[contains(@class, "owm_forum_topic_submit")]//input[@type="submit"]/..')->click();
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------Reply Topic
        try {
            $this->waitUntilElementLoaded('byId','forum_new_post_wrappper',5000);
            $this->byId('forum_new_post_wrappper')->click();
            $field = $this->waitUntilElementDisplayed('byClassName','owm_suitup-editor');
            $field->value('... so below.');
            $this->byXPath('//div[contains(@class, "owm_forum_topic_submit")]//input[@type="submit"]')->click();
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