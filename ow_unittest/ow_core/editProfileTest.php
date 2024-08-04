<?php
class editProfileTest extends FRMTestUtilites
{
    private $test_caption;
    private $TEST_USER_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $TEST_EMAIL = "user1@gmail.com";
    protected $file_path;
    private $userService;
    private $user;
    protected function setUp()
    {
        parent::setUp();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        ensure_session_active();
        FRMSecurityProvider::createUser($this->TEST_USER_NAME, $this->TEST_EMAIL, $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user =  $this->userService->findByUsername($this->TEST_USER_NAME);
    }

    public function testEditProfile()
    {
        $this->test_caption = "testEditProfile";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        $this->sign_in($this->user->getUsername(), $this->TEST_PASSWORD, true, true, $sessionId);
        $this->url(OW_URL_HOME . "profile/edit");
        try {
            $this->waitUntilElementLoaded("byName", "email", 5000);
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }
        $editedEmail = "editedEmail@gmail.com";
        $this->byName("email")->clear();
        $this->byName("email")->value($editedEmail);
        $this->byName("editSubmit")->click();
        // user can't view edit profile page if approve user is mandatory
        try {
            $this->waitUntilElementDisplayed('byCssSelector', '.ow_message_node.info');
        } catch (Exception $ex) {
            $this->handleException($ex, $this->test_caption, true);
        }
        $this->user = $this->userService->findByUsername($this->TEST_USER_NAME);
        self::assertEquals($this->user->getEmail(), $editedEmail);
    }

    public function tearDown()
    {
        if ($this->isSkipped)
            return;
        //delete user
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}