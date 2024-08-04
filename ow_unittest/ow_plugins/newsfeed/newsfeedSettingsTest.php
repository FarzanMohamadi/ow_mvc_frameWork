<?php
class newsfeedSettingsTest extends FRMTestUtilites
{
    private static $USER_NAME = 'user1';
    private static $PASSWORD = '12345';
    private $groupService;
    private $test_user;
    private $test_caption;
    private $disableNewsfeedFromUserProfileSetting = null;
    private $disableCommentsSetting = null;
    private $removeDashboardStatusFormSetting = null;

    protected function setUp()
    {
        $this->checkRequiredPlugins(array('newsfeed'));

        parent::setUp();

        $this->groupService = GROUPS_BOL_Service::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        ensure_session_active();
        FRMSecurityProvider::createUser(self::$USER_NAME, 'user1@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->test_user = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME);

        $disableNewsfeedFromUserProfileConfig = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
        OW::getConfig()->saveConfig('newsfeed', 'disableNewsfeedFromUserProfile', 'on');
        if(isset($disableNewsfeedFromUserProfileConfig)){
            if ($disableNewsfeedFromUserProfileConfig=="on") {
                $this->disableNewsfeedFromUserProfileSetting=$disableNewsfeedFromUserProfileConfig;
            }
        }

        $disableCommentsConfig = OW::getConfig()->getValue('newsfeed', 'disableComments');
        OW::getConfig()->saveConfig('newsfeed', 'disableLikeComments', 'on');
        if(isset($disableCommentsConfig)){
            if ($disableCommentsConfig=="on") {
                $this->disableCommentsSetting=$disableCommentsConfig;
            }
        }

        $removeDashboardStatusFormConfig = OW::getConfig()->getValue('newsfeed', 'removeDashboardStatusForm');
        OW::getConfig()->saveConfig('newsfeed', 'removeDashboardStatusForm', 0);
        if(isset($removeDashboardStatusFormConfig)){
            if ($removeDashboardStatusFormConfig==0) {
                $this->removeDashboardStatusFormSetting=$removeDashboardStatusFormConfig;
            }
        }

    }

    public function testAllSettings()
    {

        //sign in
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME . "sign-in");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in(self::$USER_NAME, self::$PASSWORD, true, false, $sessionId);

        //test disableNewsfeedFromUserProfile
        $this->url(OW_URL_HOME . "user/" . self::$USER_NAME);
        $this->waitUntilElementDisplayed('byXPath',
            '//div[contains(@class,"user_profile_data")]');
        $profileNewsfeedUpdateStatus = $this->checkIfXPathExists('//div[contains(@id,"newsfeed_update_status_info_id")]');
        self::assertEquals(false, $profileNewsfeedUpdateStatus);

        //test disableLikeComments
        $this->url(OW_URL_HOME . "dashboard");
        $this->test_caption = "postCaptionTest";
        $text = date('D, j M Y H:i:s O') . " : " . $this->test_caption;
        //--------POST A NEW STATUS
        try {
            $this->byClassName('ow_newsfeed_status_input')->click();
            $this->byClassName('ow_newsfeed_status_input')->value($text);
            $this->byXPath('//input[@name="save"]')->click();
            $this->waitUntilElementDisplayed('byXPath',
                '//div[contains(@class,"ow_newsfeed_content_status") and normalize-space(text()) = "'.$text.'"]');
//            $this->saveSnapshot('temp-answer-submit-post');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
            return false;
        }
        $newsfeedLikeButton = $this->checkIfXPathExists('//div[contains(@class,"newsfeed_like_btn_cont")]');
        self::assertEquals(false, $newsfeedLikeButton);
        $newsfeedCommentButton = $this->checkIfXPathExists('//div[contains(@class,"newsfeed_comment_btn_cont")]');
        self::assertEquals(false, $newsfeedCommentButton);

        //test removeDashboardStatusForm
        OW::getConfig()->saveConfig('newsfeed', 'removeDashboardStatusForm', 'on');
        $this->url(OW_URL_HOME . "dashboard");
        $profileNewsfeedUpdateStatus = $this->checkIfXPathExists('//div[contains(@id,"newsfeed_update_status_info_id")]');
        self::assertEquals(false, $profileNewsfeedUpdateStatus);

    }

    protected function tearDown()
    {
        parent::tearDown();

        FRMSecurityProvider::deleteUser(self::$USER_NAME);

        if (isset($this->disableNewsfeedFromUserProfileSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'disableNewsfeedFromUserProfile', $this->disableNewsfeedFromUserProfileSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'disableNewsfeedFromUserProfile');
        }

        if (isset($this->disableCommentsSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'disableLikeComments', $this->disableCommentsSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'disableLikeComments');
        }

        if (isset($this->removeDashboardStatusFormSetting)) {
            OW::getConfig()->saveConfig('newsfeed', 'removeDashboardStatusForm', $this->removeDashboardStatusFormSetting);
        }else{
            OW::getConfig()->deleteConfig('newsfeed', 'removeDashboardStatusForm');
        }
        parent::tearDown();
    }
}