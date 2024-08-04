<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/11/29
 */

class mSignUpTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForTest';
    private $TEST_EMAIL = 'adminSignUp@gmail.com';
    private $TEST_PASSWORD = 'passFor123';
    private $TEST_PASSWORD_REPEAT = 'passFor123';
    private $TEST_FULL_NAME = 'Admin Istrator';
    private $TEST_MALE = true;
    private $TEST_DOB_ADULT = ['4','2','1346'];

    private $CURRENT_SESSIONS;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array());
        $this->checkIfMobileIsActive();
        ensure_session_active();
    }

    public function testScenario1()
    {
        //self::markTestSkipped('must be rewritten');
        $test_caption = "mSignupTest-testScenario1";
        $this->url(OW_URL_HOME . "mobile-version");
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        try {
            $this->byClassName("join_now_widget")->click();
            $this->fillSignUpForm($this->TEST_USERNAME, $this->TEST_EMAIL, $this->TEST_PASSWORD,
                $this->TEST_PASSWORD_REPEAT, $this->TEST_FULL_NAME, $this->TEST_MALE, $this->TEST_DOB_ADULT);
            //------------------SUBMIT-----------
            $this->byName('joinSubmit')->click();

            sleep(1);
            $userDao = BOL_UserDao::getInstance();
            $user = $userDao->findByUserName($this->TEST_USERNAME);
            self::assertNotNull($user);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    private function fillSignUpForm($username, $email, $pass, $repeat_pass, $real_name, $is_male, $dob){

        $this->hide_element('demo-nav');
        $joinForm = $this->byId("joinForm");

        //------------------USERNAME-----------
        $i = 1;
        while($i<10){
            $tmp2 = '(//*[contains(@class, "ow_username_validator")])['.$i.']';
            $tmp = $joinForm->byXPath($tmp2);
            if($tmp->displayed()) {
                $tmp->value($username);
                break;
            }
            $i++;
        }
        //------------------EMAIL, PASSWORDS-----------
        $joinForm->byClassName('ow_email_validator')->value($email);
        //$this->scroll_byName($this,'password');
        $joinForm->byName('password')->value($pass);
        $joinForm->byName('repeatPassword')->value($repeat_pass);

        //------------------REAL NAME-----------
        $tmp2 = '//div[div/input[@name="repeatPassword"]]';
        $i = 1;
        while($i<20) {
            $tmp2 = $tmp2.'/following::div';
            $tmp = $joinForm->byXPath($tmp2);
            if ($tmp->displayed()) {
                $tmp3 = $tmp->byXPath('div');
                $tmp3 = $tmp3->byXPath('input');
                $tmp3_id = $tmp3->attribute('id');
                $this->byId($tmp3_id)->value($real_name);
                break;
            }
            $i++;
        }

        //------------------GENDER-----------
        $i = 1;
        while($i<20) {
            //$tmp2 = '(//*[@type="radio"])['.$i.']';
            $tmp2 = '(//div[ul/li/input[@type="radio"]])[' . $i . ']';
            $tmp = $joinForm->byXPath($tmp2);

            if ($tmp->displayed()) {
                if($is_male)
                    $tmp = $tmp->byXPath('ul[1]/li[1]/input[1]');
                else
                    $tmp = $tmp->byXPath('ul[1]/li[2]/input[1]');
                $tmp_id = $tmp->attribute('id');

                //$this->echoText($i . ' ' . $tmp2 . '    ' . $tmp_id);
                $this->webDriver->executeScript('document.getElementById("' . $tmp_id . '").checked=true;', array());
                break;
            }
            $i++;
        }

        //------------------DOB-----------
        $i = 1;
        while($i<30) {
            $tmp2 = '(//div[div/select])[' . $i . ']';
            $tmp = $joinForm->byXPath($tmp2);

            if ($tmp->displayed()) {
                //------DAY
                $tmp3 = $tmp->byXPath('div[1]/select[1]');
                $tmp3->byXPath('option[@value="'.$dob[0].'"]')->click();
                /*
                $tmp3_name = $tmp3->attribute('name');
                $this->execute(array(
                    //'script' => 'document.getElementsByName("'.$tmp3_name.'")[0].getElementsByTagName("option")['.$this->TEST_DOB[0].'].selected = "selected"',
                    'script' => 'document.getElementsByName("'.$tmp3_name.'")[0].value = "'.$dob[0].'";',
                    'args' => array()
                ));
                //*/
                //------MONTH
                $tmp3 = $tmp->byXPath('div[2]/select[1]');
                $tmp3->byXPath('option[@value="'.$dob[1].'"]')->click();
                //-------YEAR
                $tmp3 = $tmp->byXPath('div[3]/select[1]');
                $tmp3->byXPath('option[@value="'.$dob[2].'"]')->click();
                break;
            }
            $i++;
        }

        //------------SECURITY CODE
        $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
        $this->keys('c0de');

        //*------------------CAPTCHA, SESSIONS-----------
        if($this->checkIfXPathExists('//*[@name="captchaField"]')) {
            $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
            $sessionId = str_replace('%2C',',',$sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
            ensure_no_session();
            session_id($sessionId);
            @session_start();

            $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
            $joinForm->byName('captchaField')->value($captchaText);
            session_write_close();
        }
        //---------------------------------------------------*/

    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->TEST_USERNAME);
        parent::tearDown();
    }
}