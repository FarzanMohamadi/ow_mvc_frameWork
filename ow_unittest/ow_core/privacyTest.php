<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/11
 */

class privacyTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_USER4_NAME = "user4";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2,$user3,$user4;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('friends', 'privacy'));

        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER3_NAME,"user3@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER4_NAME,"user4@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        $this->user4 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER4_NAME);

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        $friendsQuestionService->request($this->user1->getId(),$this->user4->getId());
        $friendsQuestionService->accept($this->user4->getId(),$this->user1->getId());
    }

    public function testScenario2()
    {
        $test_caption = "privacyTest-testScenario2";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        //----------USER1
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->hide_element('demo-nav');
            sleep(2);
            $this->byName('status')->click();
            $this->byName('status')->value($test_caption);
            $this->byTag('body')->click();
            $this->byName('status')->click();
            $statusPrivacy = $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only
            $this->byXPath('//input[@name="save"]')->click();

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER2
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
            $value = (string)$this->byCssSelector('.ow_newsfeed_content_status')->attribute('innerText');
            self::assertEquals($value, $test_caption);

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
            $value = (string)$this->byCssSelector('.ow_newsfeed_content_status')->attribute('innerText');
            self::assertEquals($value, $test_caption);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario3()
    {
        $test_caption = "privacyTest-testScenario3";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        //----------USER1
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->hide_element('demo-nav');
            sleep(2);
            $this->byName('status')->click();
            $this->byName('status')->value($test_caption);
            $statusPrivacy = $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy->byXPath('option[@value="only_for_me"]')->click();//only_for_me, everybody, friends_only
            $this->byXPath('//input[@name="save"]')->click();
            sleep(2);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        $this->signOutDesktop(true);
        sleep(1);

        //----------USER2
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
            try {
                $value = (string)$this->byCssSelector('.ow_newsfeed_content_status')->attribute('innerText');
                if (getenv("SNAPSHOT_DIR"))
                    OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                self::assertTrue(false);
            } catch (Exception $ex) {
                $value = '';
            }
            self::assertNotEquals($value, $test_caption);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        $this->signOutDesktop(true);
        sleep(1);

        //----------USER3
        $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
            try {
                $value = (string)$this->byCssSelector('.ow_newsfeed_content_status')->attribute('innerText');
                if (getenv("SNAPSHOT_DIR"))
                    OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());

            } catch (Exception $ex) {
                $value = '';
            }
            self::assertNotEquals($value, $test_caption);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario4PrivacySettings()
    {
        $test_caption = "privacyTest-testScenario4PrivacySettings";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        //----------USER1
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME.'profile/privacy');
            $this->hide_element('demo-nav');
            //only_for_me, everybody, friends_only
            $this->byName('base_view_profile')->byXPath('option[@value="friends_only"]')->click();
            $this->byName('base_view_my_presence_on_site')->byXPath('option[@value="only_for_me"]')->click();

            $this->hide_element('ow_chat_cont','top','-150px');
            $this->byXPath('//input[@name="privacySubmit"]')->click();

            sleep(2);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        $this->signOutDesktop(true);
        sleep(1);

        //----------USER2
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user');
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            //1--check profile
            $this->waitUntilElementLoaded('byCssSelector', '.user_profile_data');
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        try { //2--check live
            $this->waitUntilElementLoaded('byCssSelector', '.ow_miniic_live');
            self::assertTrue(false);
            if (getenv("SNAPSHOT_DIR"))
                OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . $test_caption . '_3.png', $this->currentScreenshot());
        } catch (Exception $ex) {
        }
    }

    public function testScenario5()
    {
        // User1 posts for friends
        // User2 likes the post
        // User3 can't see the post
        // User4 can see the post and it's like
        $test_caption = "privacyTest-testScenario5";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'

        //----------USER1
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . "dashboard");

            $this->byName('status')->click();
            $this->byName('status')->value($test_caption);
            $statusPrivacy = $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy->byXPath('option[@value="friends_only"]')->click(); //only_for_me, everybody, friends_only
            $this->byXPath('//input[@name="save"]')->click();

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        $postId = '';

        //----------USER2 - FRIEND of 1
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $post = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]/../../../../..");
            $postId = $post->attribute('id');
            $likeButton = $this->byCssSelector('#'.$postId.' .newsfeed_like_btn_cont');
            $this->scrollDown();
            $likeButton->click();

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]");
            self::assertTrue(!$resp);

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER4 - FRIEND of 1
        $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->byXPath("//*[@id='$postId']//*[contains(text(),'1') and contains(@class, 'newsfeed_counter_likes')]");
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario6()
    {
        // User1 sets wallwritingprivacy to friends
        // User2 posts in User1
        // User2 likes the post
        // User3 can't see the post
        // User4 can see the post and it's like count
        $test_caption = "privacyTest-testScenario6";

        $this->webDriver->prepare();
        $this->setScreenSize(1000,3000);

        $this->url(OW_URL_HOME . "dashboard");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'

        //----------USER1
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME.'profile/privacy'); //only_for_me, everybody, friends_only

            $privacyItem = $this->byName('who_post_on_newsfeed');
            $privacyItem->byXPath('option[@value="friends_only"]')->click();
            $this->byXPath('//input[@name="privacySubmit"]')->click();

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER2 - FRIEND of 1
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $form = $this->byId('feed1');
            $form->byName('status')->value($test_caption);
            $form->byName('save')->click();
            sleep(1);

            $post = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]/../../../../..");
            $postId = $post->attribute('id');
            $likeButton = $this->byCssSelector('#'.$postId.' .newsfeed_like_btn_cont');
            $likeButton->click();

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]");
            self::assertTrue(!$resp);

            $this->signOutDesktop(true);
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER4 - FRIEND of User1
        $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            $this->byXPath("//*[@id='$postId']//*[contains(text(),'1') and contains(@class, 'newsfeed_counter_likes')]");
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        FRMSecurityProvider::deleteUser($this->user4->getUsername());
        parent::tearDown();
    }
}