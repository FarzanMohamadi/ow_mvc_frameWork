<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/11
 */

class privacySpecialTest extends FRMTestUtilites
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
        ensure_session_active();
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

    public function testScenario1()
    {
        $test_caption = "privacyTest-testScenario1";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        //----------USER1
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->byName('status')->click();
            $this->byName('status')->value($test_caption);
            $statusPrivacy = $this->waitUntilElementDisplayed('byName', 'statusPrivacy');
            $statusPrivacy->byXPath('option[@value="friends_only"]')->click();//only_for_me, everybody, friends_only
            $this->byXPath('//input[@name="save"]')->click();

            $this->signOutDesktop();
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER2: User2 can see
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $res = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]/../../../../..");
            self::assertTrue($res);

            $this->signOutDesktop();
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $res = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content_status')]/../../../../..");
            self::assertFalse($res);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario7()
    {
        // User2 posts in User1
        // User1 changes last_post_of_others_newsfeed to only_for_me
        // User4 can't see the post
        $test_caption = "privacyTest-testScenario7";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        //----------USER2 - FRIEND of 1
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

            $form = $this->byId('feed1');
            $form->byName('status')->value($test_caption);
            $form->byName('save')->click();
            sleep(1);
            $form->byName('status')->value($test_caption);
            $form->byName('save')->click();

            $this->signOutDesktop();
            sleep(1);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER1
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME.'profile/privacy');
            $this->hide_element('demo-nav');
            //only_for_me, everybody, friends_only
            //others post
            $privacyItem = $this->byName('other_post_on_feed_newsfeed');
            $privacyItem->byXPath('option[@value="only_for_me"]')->click();

            //last posts of others
            $privacyItem = $this->byName('last_post_of_others_newsfeed');
            $privacyItem->byXPath('option[@value="only_for_me"]')->click();

            $this->hide_element('ow_chat_cont','top','-150px');
            $this->byXPath('//input[@name="privacySubmit"]')->click();

            $this->signOutDesktop();
            sleep(1);
        }catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //--------------CRON JOB
        OW::getConfig()->saveConfig("issa","weAreTesting",true);
        $cron_dir = "\"".OW_DIR_ROOT."ow_cron".DS."run.php\"";
        fwrite(STDERR, exec("php ".$cron_dir));
        OW::getConfig()->deleteConfig("issa","weAreTesting");
        sleep(1);

        //----------USER4
        $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
            sleep(1);
            $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
            if($resp) {
                PRIVACY_BOL_ActionService::getInstance()->cronUpdatePrivacy(); //direct run
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
            }
            self::assertTrue(!$resp);
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
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        FRMSecurityProvider::deleteUser($this->user4->getUsername());
        parent::tearDown();
    }
}