<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class groupsTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1, $user2, $user3;
    private $group1, $group2, $group3;

    public function createGroupSeleniumVersion($title, $desc, $whoCanView, $whoCanInvite)
    {
        $this->url(OW::getRouter()->urlForRoute('groups-create'));
        $this->byName('title')->value($title);
        //desc
        $this->webDriver->executeScript("var iframeText = document.getElementsByClassName(\"cke_wysiwyg_frame cke_reset\")[0]; if(iframeText!=null) { var conDocument = iframeText.contentDocument;conDocument.body.innerHTML = \"" . $desc . "\";} else { var iframeText = document.querySelectorAll(\"textarea[name=\\\"description\\\"]\")[0]; iframeText.style.display=\"block\"; iframeText.innerText=\"" . $desc . "\";}", array());
        if ($whoCanView == 'anyone') {
            $this->byCssSelector('input[name="whoCanView"][value="anyone"]')->click();
        } else {
            $this->byCssSelector('input[name="whoCanView"][value="invite"]')->click();
        }

        if ($whoCanInvite == 'creator') {
            $this->byCssSelector('input[name="whoCanInvite"][value="creator"]')->click();
        } else {
            $this->byCssSelector('input[name="whoCanInvite"][value="participant"]')->click();
        }

        try {
            $this->byCssSelector('input[name="whoCanCreateContent"][value="group"]')->click();
        } catch (Exception $e) {
        }

        $this->scrollDown();
        $this->byName('save')->click();;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('friends', 'groups'));
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

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME . 'sign-in');
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in($this->TEST_USER1_NAME, $this->TEST_PASSWORD, true, true, $sessionId);

        $group_name = uniqid('group_test_');
        $this->createGroupSeleniumVersion($group_name, 'desc1', 'anyone', 'creator');
        $this->group1 = $this->findGroupByTitle($group_name);
        self::assertNotEmpty($this->group1);

        $group_name = uniqid('group_test_');
        $this->createGroupSeleniumVersion($group_name, 'desc2', 'anyone', 'participant');
        $this->group2 = $this->findGroupByTitle($group_name);
        self::assertNotEmpty($this->group2);

        $group_name = uniqid('group_test_');
        $this->createGroupSeleniumVersion($group_name, 'desc3', 'invite', 'participant');
        $this->group3 = $this->findGroupByTitle($group_name);
        self::assertNotEmpty($this->group3);

        $this->signOutDesktop();

        ensure_session_active();
        OW::getUser()->login($this->user1->getId());
        GROUPS_BOL_Service::getInstance()->inviteUser($this->group3->id, $this->user3->getId(), $this->user1->getId());
        OW::getUser()->logout();
    }

    private function findGroupByTitle($title)
    {
        $example = new OW_Example();
        $example->andFieldEqual('title', $title);
        return GROUPS_BOL_GroupDao::getInstance()->findObjectByExample($example);
    }

    public function testGroups1()
    {
        //----SCENARIO 1
        //User1 create GROUP1 : everyone can join, only user1 can invite
        //User2 joins, Can't invite, can post
        //User3 not Joins, can join, can't post

        //----SCENARIO 2
        //User1 create GROUP2 : everyone can join and invite
        //User2 Joins, Can invite, can post

        //----SCENARIO 3
        //User1 create GROUP3 : join with invite link, invites user2 (a friend)
        //User2 can't view
        //User3 can't view

        $test_caption = "groupsTest-testGroups1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER2
        $this->sign_in($this->user2->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        try {
            $this->url(OW_URL_HOME . 'groups/' . $this->group1->id . '/');
            $this->waitUntilElementLoaded('byClassName', 'group_details_join_btn_label');
            $this->byClassName('group_details_join_btn_label')->click();
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists('//*[@name="status"]', DEFAULT_TIMEOUT_MILLIS);
            self::assertTrue($res);
            $res = $this->checkIfXPathExists('//*[@id="GROUPS_InviteLink"]');
            self::assertTrue(!$res);
            $this->url(OW_URL_HOME . 'groups/' . $this->group2->id . '/');
            $this->waitUntilElementLoaded('byClassName', 'group_details_join_btn_label');
            $this->byClassName('group_details_join_btn_label')->click();
            $this->hide_element('demo-nav');
            $status = $this->waitUntilElementLoaded('byName', 'status');
            $status->value($test_caption);
            $this->webDriver->executeScript(' document.getElementById("updatestatus_submit_button").click();');
            $res = $this->checkIfXPathExists('//*[@id="GROUPS_InviteLink"]');
            self::assertTrue($res);

            $this->url(OW_URL_HOME . 'groups/' . $this->group3->id . '/join');
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists('//*[@id="GROUPS_InviteLink"]');
            self::assertTrue(!$res);

            $this->signOutDesktop(true);
        } catch (Exception $ex) {
            $this->handleException($ex, $test_caption, true);
        }

        //----------USER3
        $this->sign_in($this->user3->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        try {
            $this->url(OW_URL_HOME . 'groups/' . $this->group1->id);
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists("//*[contains(@class, 'ow_ic_info')]");
            self::assertTrue($res);
            $res = $this->checkIfXPathExists('//*[@name="status"]');
            self::assertTrue(!$res);

            $this->url(OW_URL_HOME . 'groups/' . $this->group3->id . '/join');
            $this->hide_element('demo-nav');
            $res = $this->checkIfXPathExists('//*[@id="GROUPS_InviteLink"]');
            self::assertTrue(!$res);

        } catch (Exception $ex) {
            $this->handleException($ex, $test_caption, true);
        }
    }


    public function tearDown()
    {
        if ($this->isSkipped)
            return;

        //delete Groups
        $groupDto = GROUPS_BOL_Service::getInstance();
        $groupDto->deleteGroup($this->group1->id);
        $groupDto->deleteGroup($this->group2->id);
        $groupDto->deleteGroup($this->group3->id);

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        parent::tearDown();
    }
}