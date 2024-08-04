<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class mEventTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2,$user3;

    private $event1;
    private $event1_title = "Mobile Test Event I";

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('event'));
        $this->checkIfMobileIsActive();

        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER3_NAME,"user3@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        // set some info to users

        //+1 year
        $start_stamp1 = mktime(0, 0, 0, date('n',time()), date('j',time()), date('Y',time())+1 );

        ensure_session_active();
        OW::getUser()->login($this->user1->getId());
        $data = array(
            'title' => $this->event1_title,
            'desc' => 'Secret Society',
            'who_can_invite' => '2',
            'who_can_view' => '2',
            'location' => 'loc3',
            'start_time' => 'all_day',
            'end_time' => 'all_day',
        );
        $this->event1 = EVENT_BOL_EventService::getInstance()->createEvent($data, $this->user1->getId(), $start_stamp1, $start_stamp1 + 60*60*24, false, true, null);
        EVENT_BOL_EventService::getInstance()->inviteUser($this->event1->id,$this->user2->getId(),$this->user1->getId());
        OW::getUser()->logout();
    }

    public function testScenario1()
    {
        //----SCENARIO 1 - Secret Society
        //User1 creates an event.
        //User1 invites user2
        //User2 can't view
        //User3 can't view

        $test_caption = "mEventTest-testScenario1";
        ///$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        //----------GUEST
        $this->url(OW_URL_HOME . 'event/'.$this->event1->id);
        //check if title is the same
        self::assertFalse($this->checkIfXPathExists('//div[@id="owm_heading" and contains(text(),"'.$this->event1_title.'")]'));

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER2
        try {
            $this->mobile_sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . 'event/'.$this->event1->id);
            //check if title is the same
            self::assertFalse($this->checkIfXPathExists('//div[@id="owm_heading" and contains(text(),"'.$this->event1_title.'")]'));
            sleep(1);
            $this->url('sign-out');
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        //----------USER3
        try {
            $this->mobile_sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . 'event/'.$this->event1->id);
            //check if title is the same
            self::assertFalse($this->checkIfXPathExists('//div[@id="owm_heading" and contains(text(),"'.$this->event1_title.'")]'));
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

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        parent::tearDown();
    }
}