<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/11
 */

class frmContactUsTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = '12345';

    private $CONTACT_DEPT = "Dept2";
    private $CONTACT_EMAIL = "Dept2@gmail.com";
    private $CONTACT_SUBJECT = 'Hello';
    private $CONTACT_MESSAGE = 'it is me';

    private $userService;
    private $user;

    private $CURRENT_SESSIONS;
    private $FRMCONTACT_Service;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmcontactus'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);

        $this->FRMCONTACT_Service = FRMCONTACTUS_BOL_Service::getInstance();

        if(! FRMCONTACTUS_BOL_DepartmentDao::getInstance()->findIsExistLabel($this->CONTACT_DEPT))
            $this->FRMCONTACT_Service->addDepartment($this->CONTACT_EMAIL,$this->CONTACT_DEPT);
    }

    public function testUserSend()
    {
        $test_caption = "frmContactUsTest-testUserSend";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->sign_in($this->TEST_USERNAME, $this->TEST_CORRECT_PASSWORD, true);

        $this->url(OW_URL_HOME . "frmcontact");
        try {
            $this->byName('to')->value("dept2");
            $this->byName('subject')->value($this->CONTACT_SUBJECT);
            $this->byName('message')->value($this->CONTACT_MESSAGE);
            //------------------CAPTCHA, SESSIONS-----------
            $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
            $sessionId = str_replace('%2C',',',$sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
            if(session_status() == PHP_SESSION_ACTIVE){
                //Destroy current
                session_destroy();
            }
            session_id($sessionId);
            @session_start();

            $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
            $this->byName('captcha')->value($captchaText);
            session_write_close();
            //---------------------------------------------------/
            $this->byName('contact_form')->submit();

            // check db
            $sendExample = new OW_Example();
            $sendExample->andFieldEqual("label", $this->CONTACT_DEPT);
            $sendExample->andFieldEqual("subject", $this->CONTACT_SUBJECT);
            $sendExample->andFieldEqual("message", $this->CONTACT_MESSAGE);
            $res = FRMCONTACTUS_BOL_UserInformationDao::getInstance()->findIdByExample($sendExample);

            self::assertNotNull($res);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());

        FRMCONTACTUS_BOL_Service::getInstance()->deleteUserInformationBylabel($this->CONTACT_DEPT);
        parent::tearDown();
    }
}