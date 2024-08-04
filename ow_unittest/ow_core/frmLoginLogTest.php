<?php
class frmLoginLogTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = '12345';

    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmuserlogin'));

        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);
    }

    public function testLoginLog()
    {
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        $t = time() - 10;
        $this->sign_in($this->TEST_USERNAME, $this->TEST_CORRECT_PASSWORD, true, true, $sessionId);

        try{
            $queryForGetData = "select * from `".OW_DB_PREFIX."frmuserlogin_login_details` where userId =".$this->user->getId()." and time>=".$t;
            $data = OW::getDbo()->queryForRow($queryForGetData);
            self::assertTrue(isset($data));
        }catch (Exception $ex){
            $this->handleException($ex,'',true,false);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}