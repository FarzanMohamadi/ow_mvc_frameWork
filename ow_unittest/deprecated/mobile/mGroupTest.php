<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class mGroupTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2,$user3;

    private $group1;
    private $group1_title = "Mobile Test Event I";

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('groups', 'friends'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER3_NAME,"user3@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        // set some info to users

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        ensure_session_active();
        OW::getUser()->login($this->user1->getId());
        $data2 = array(
            'title' => $this->group1_title,
            'description' => 'desc3',
            'whoCanInvite' => 'participant',
            'whoCanView' => 'invite',
        );
        $this->group1 = GROUPS_BOL_Service::getInstance()->createGroup($this->user1->getId(), $data2);
        GROUPS_BOL_Service::getInstance()->inviteUser($this->group1->id, $this->user2->getId(),$this->user1->getId());
        OW::getUser()->logout();
    }

    public function testScenario1()
    {
        //----SCENARIO 1 - Secret Society
        //User1 creates a group.
        //User1 invites user2
        //User2 can't view
        //User3 can't view

        $test_caption = "mGroupTest-testScenario1";
        ///$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        //----------GUEST
        $this->url(OW_URL_HOME . 'groups/'.$this->group1->id);
        //check if title is the same
        self::assertFalse($this->checkIfXPathExists('//div[@id="owm_heading" and contains(text(),"'.$this->group1_title.'")]'));

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER2
        try {
            $this->mobile_sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . 'groups/'.$this->group1->id);
            //check if title is the same
            self::assertFalse($this->checkIfXPathExists('//*[contains(@class,"title")]//*[contains(text(),"'.$this->group1_title.'")]'));
            sleep(1);
            $this->url('sign-out');
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
        //----------USER3
        try {
            $this->mobile_sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . 'groups/'.$this->group1->id);
            //check if title is the same
            self::assertFalse($this->checkIfXPathExists('//*[contains(@class,"title")]//*[contains(text(),"'.$this->group1_title.'")]'));
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete Groups
        $groupDto = GROUPS_BOL_Service::getInstance();
        $groupDto->deleteGroup($this->group1->id);

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        parent::tearDown();
    }
}