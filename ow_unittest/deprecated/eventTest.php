<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */
class eventTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1, $user2, $user3;
    private $event1, $event2, $event3;

    public function createEventSeleniumVersion($title, $desc, $location, $whoCanView, $whoCanInvite)
    {
        $this->url(OW::getRouter()->urlForRoute('event.add'));
        $this->byName('title')->click();
        $this->byName('title')->value($title);

        //desc
        $this->webDriver->executeScript("var iframeText = document.getElementsByClassName(\"cke_wysiwyg_frame cke_reset\")[0]; if(iframeText!=null) { var conDocument = iframeText.contentDocument;conDocument.body.innerHTML = \"".$desc."\";} else { var iframeText = document.getElementsByName(\"desc\")[0]; iframeText.style.display=\"block\"; iframeText.innerText=\"".$desc."\";}", array());


        $this->byName('location')->clear();
        $this->byName('location')->value($location);

        if($whoCanView == 'anyone'){
            $this->byCssSelector('input[name="who_can_view"][value="1"]')->click();
        }else{
            $this->byCssSelector('input[name="who_can_view"][value="2"]')->click();
        }

        if($whoCanInvite == 'creator'){
            $this->byCssSelector('input[name="who_can_invite"][value="1"]')->click();
        }else{
            $this->byCssSelector('input[name="who_can_invite"][value="2"]')->click();
        }
        $this->byName('submit')->click();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('event', 'friends'));
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME, "user1@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME, "user2@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER3_NAME, "user3@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        // set some info to users

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(), $this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(), $this->user1->getId());

        //login
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME.'sign-in');
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in($this->TEST_USER1_NAME, $this->TEST_PASSWORD, true, true, $sessionId);

        $event_name = uniqid('event_test_');
        $this->createEventSeleniumVersion($event_name, 'Seminar', 'loc1', 'anyone', 'creator');
        $this->event1 = $this->findEventByTitle($event_name);
        $event_name = uniqid('event_test_');
        $this->createEventSeleniumVersion($event_name, 'Zoo', 'loc2', 'anyone', 'participant');
        $this->event2 = $this->findEventByTitle($event_name);
        $event_name = uniqid('event_test_');
        $this->createEventSeleniumVersion($event_name, 'Secret Society', 'loc3', 'invite', 'participant');
        $this->event3 = $this->findEventByTitle($event_name);
        $this->signOutDesktop();

        ensure_session_active();
        OW::getUser()->login($this->user1->getId());
        EVENT_BOL_EventService::getInstance()->inviteUser($this->event3->id, $this->user3->getId(), $this->user1->getId());
        OW::getUser()->logout();
    }

    private function findEventByTitle($title)
    {
        $example = new OW_Example();
        $example->andFieldEqual('title',$title);
        return EVENT_BOL_EventDao::getInstance()->findObjectByExample($example);
    }

    public function testEvent1()
    {
        //----SCENARIO 1 - Seminar
        //User1 create Event1 : everyone can join, only user1 can invite
        //User2 Maybe attends, can't invite, can post
        //User3 Won't attend, can't invite, can post

        //----SCENARIO 2 - Zoo
        //User1 create Event2 : everyone can join and invite
        //User2 Maybe attends, can invite, can post

        //----SCENARIO 3 - Secret Society
        //User1 create Event3 : join with invite link, invites user3
        //User2 can't attend or read
        //User3 can't attend or read


        $test_caption = "eventTest-testEvent1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER2
        $this->sign_in($this->user2->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        try {
            $this->url(OW_URL_HOME . 'event/' . $this->event1->id);
            $this->hide_element('demo-nav');
            $this->byId('event_attend_maybe_btn')->click();
            $this->waitUntilElementDisplayed('byCssSelector', '.ow_message_node.info');
            $this->url(OW_URL_HOME . 'event/' . $this->event1->id); //refresh page for invite link
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue($res);
            $res = $this->checkIfXPathExists('//*[@id="inviteLink"]');
            self::assertTrue(!$res);


            $this->url(OW_URL_HOME . 'event/' . $this->event2->id);
            $this->hide_element('demo-nav');
            $this->byId('event_attend_maybe_btn')->click();
            $this->waitUntilElementDisplayed('byCssSelector', '.ow_message_node.info');
            $this->url(OW_URL_HOME . 'event/' . $this->event2->id);
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue($res);
            $res = $this->checkIfXPathExists('//*[@id="inviteLink"]');
            self::assertTrue($res);


            $this->url(OW_URL_HOME . 'event/' . $this->event3->id);
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue(!$res);
            $this->signOutDesktop();
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        try {
            $this->url(OW_URL_HOME . 'event/' . $this->event1->id);
            $this->hide_element('demo-nav');
            $this->byId('event_attend_no_btn')->click();
            $this->url(OW_URL_HOME . 'event/' . $this->event1->id); //refresh page for invite link
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue($res);
            $res = $this->checkIfXPathExists('//*[@id="inviteLink"]');
            self::assertTrue(!$res);

            $this->url(OW_URL_HOME . 'event/' . $this->event3->id);
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue(!$res);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }


    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete events
        $eventDto = EVENT_BOL_EventService::getInstance();
        $eventDto->deleteEvent($this->event1->id);
        $eventDto->deleteEvent($this->event2->id);
        $eventDto->deleteEvent($this->event3->id);

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        parent::tearDown();
    }
}