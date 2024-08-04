<?php
class mNewsFeedSettingsTest extends FRMTestUtilites
{
    private static $USER_NAME = 'user1';
    private static $PASSWORD = 'password123';
    private $test_user;
    private $group;
    private $group_title = "test_group";
    private $test_caption;
    private $showGroupChatFormSetting = null;
    private $showDashboardChatFormSetting = null;
    private $addReplySetting = null;
    private $disableNewsfeedFromUserProfileSetting = null;
    private $disableCommentsSetting = null;
    private $removeDashboardStatusFormSetting = null;

    protected function setUp()
    {
        $this->checkRequiredPlugins(array('newsfeed','groups'));
        $this->checkIfMobileIsActive();

        parent::setUp();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser(self::$USER_NAME, 'user1@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->test_user = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME);

        $showGroupChatFormConfig = OW::getConfig()->getValue('newsfeed', 'showGroupChatForm');
        if (isset($showGroupChatFormConfig) && $showGroupChatFormConfig=="on") {
            $this->showGroupChatFormSetting=$showGroupChatFormConfig;
        }else{
            OW::getConfig()->saveConfig('newsfeed', 'showGroupChatForm', 'on');
        }

        $showDashboardChatFormConfig = OW::getConfig()->getValue('newsfeed', 'showDashboardChatForm');
        if (isset($showDashboardChatFormConfig) && $showDashboardChatFormConfig=="on") {
            $this->showDashboardChatFormSetting=$showDashboardChatFormConfig;
        }else{
            OW::getConfig()->saveConfig('newsfeed', 'showDashboardChatForm', 'on');
        }

        $addReplyFeatureConfig = OW::getConfig()->getValue('newsfeed', 'addReply');
        OW::getConfig()->saveConfig('newsfeed', 'addReply', 'on');
        if ($addReplyFeatureConfig=="on") {
            $this->addReplySetting=$addReplyFeatureConfig;
        }

        $disableNewsfeedFromUserProfileConfig = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
        OW::getConfig()->saveConfig('newsfeed', 'disableNewsfeedFromUserProfile', 'on');
        if ($disableNewsfeedFromUserProfileConfig=="on") {
            $this->disableNewsfeedFromUserProfileSetting=$disableNewsfeedFromUserProfileConfig;
        }

        $disableCommentsConfig = OW::getConfig()->getValue('newsfeed', 'disableComments');
        OW::getConfig()->saveConfig('newsfeed', 'disableComments', 'on');
        if ($disableCommentsConfig=="on") {
            $this->disableCommentsSetting=$disableCommentsConfig;
        }

        $removeDashboardStatusFormConfig = OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm');
        if (isset($removeDashboardStatusFormConfig) && $removeDashboardStatusFormConfig=="on") {
            $this->removeDashboardStatusFormSetting=$removeDashboardStatusFormConfig;
        }else{
            OW::getConfig()->saveConfig('newsfeed', 'removeDashboardStatusForm', 'on');
        }

        ensure_session_active();
        OW::getUser()->login($this->test_user->getId());
        $group_data = array(
            'title' => $this->group_title,
            'description' => 'desc_group',
            'whoCanInvite' => 'participant',
            'whoCanView' => 'invite',
        );
        $this->group = GROUPS_BOL_Service::getInstance()->createGroup($this->test_user->getId(), $group_data);
        OW::getUser()->logout();
    }

    public function testShowGroupChatForm()
    {
        $this->test_caption = "mNewsfeedTest-AllSettings";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->mobile_sign_in($this->test_user->getUsername(), self::$PASSWORD, true, $sessionId);
        $this->waitUntilElementDisplayed('byId', 'owm_header_right_btn', 5000);

        //test showGroupChatForm
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId());
        $newsfeedStatusUpdateCircle = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_status_update_circle")]');
        self::assertEquals(false, $newsfeedStatusUpdateCircle);

        //test showDashboardChatForm
        $this->url(OW_URL_HOME . "dashboard");
        $newsfeedStatusUpdateCircle = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_status_update_circle")]');
        self::assertEquals(false, $newsfeedStatusUpdateCircle);

        //test addReply
        $text = "testPostReplyCaption";
        $text = date('D, j M Y H:i:s O') . " : " . $text;
        //--------POST A NEW STATUS
        try {
            $this->url(OW_URL_HOME . "groups/" . $this->group->getId());
            $this->byClassName('owm_invitation')->click();
            $this->byId('newsfeed_status_input')->value($text);
            $this->byXPath('//input[@id="updatestatus_submit_button"]')->click();
            $this->waitUntilElementDisplayed('byXPath',
                '//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"' . $text . '")]');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }

        //check if post exist
        sleep(2);
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId()); //refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"' . $text . '")]'));

        //reply post click
        try {
            $this->waitUntilElementDisplayed('byCssSelector','.owm_newsfeed_item_cont');
            $this->byCssSelector('.owm_newsfeed_list .owm_newsfeed_item:nth-child(3) .owm_context_action .cd-nav-trigger')->click();
            $this->byCssSelector('.owm_newsfeed_list .owm_newsfeed_item:nth-child(3) .owm_context_action_list li a.groups_reply_to')->click();
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }

        //answer post
        $text = "this is a simple text for reply.";
        $this->byId('newsfeed_status_input')->value($text);
        $this->byXPath('//input[@id="updatestatus_submit_button"]')->click();
        $this->waitUntilElementDisplayed('byXPath','//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"' . $text . '")]');

        //check if post exist (reply)
        sleep(2);
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId());//refresh page
        self::assertTrue($this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"' . $text . '")]'));

        //end test addReply ===================================

        //test disableNewsfeedFromUserProfile
        $this->url(OW_URL_HOME . "user/" . self::$USER_NAME);
        $this->waitUntilElementDisplayed('byXPath',
            '//div[contains(@class,"owm_profile_info_all")]');
        $newsfeedStatusUpdateCircle = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_status_update_circle")]');
        self::assertEquals(false, $newsfeedStatusUpdateCircle);

        //test disableLikeComments
        $this->url(OW_URL_HOME . "groups/" . $this->group->getId());
        $newsfeedLikeButton = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_control_like")]');
        self::assertEquals(false, $newsfeedLikeButton);
        $newsfeedCommentButton = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_control_comment")]');
        self::assertEquals(false, $newsfeedCommentButton);

        //test removeDashboardStatusForm
        $this->url(OW_URL_HOME . "dashboard");
        $profileNewsfeedUpdateStatus = $this->checkIfXPathExists('//div[contains(@id,"owm_newsfeed_status_update_circle")]');
        self::assertEquals(false, $profileNewsfeedUpdateStatus);
    }

    protected function tearDown()
    {
        parent::tearDown();
        //delete group
        GROUPS_BOL_Service::getInstance()->deleteGroup($this->group->getId());
        //delete users
        FRMSecurityProvider::deleteUser(self::$USER_NAME);

        if (isset($this->showGroupChatFormSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'showGroupChatForm', $this->showGroupChatFormSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'showGroupChatForm');
        }

        if (isset($this->showDashboardChatFormSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'showDashboardChatForm', $this->showDashboardChatFormSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'showDashboardChatForm');
        }

        if (isset($this->addReplySetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'addReply', $this->addReplySetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'addReply');
        }

        if (isset($this->disableNewsfeedFromUserProfileSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'disableNewsfeedFromUserProfile', $this->disableNewsfeedFromUserProfileSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'disableNewsfeedFromUserProfile');
        }

        if (isset($this->disableCommentsSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'disableComments', $this->disableCommentsSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'disableComments');
        }

        if (isset($this->removeDashboardStatusFormSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'removeDashboardStatusForm', $this->removeDashboardStatusFormSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'removeDashboardStatusForm');
        }
        parent::tearDown();
    }
}