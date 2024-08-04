<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/14
 */

class signUpTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForTest';
    private $TEST_EMAIL = 'adminSignUp@gmail.com';
    private $TEST_PASSWORD = 'passFor123';
    private $TEST_PASSWORD_REPEAT = 'passFor123';
    private $TEST_FULL_NAME = 'Admin Istrator';
    private $TEST_MALE = true;
    private $TEST_DOB_ADULT = ['4','2','1346'];

    private $TEST_KIDS_AGE = 30;
    private $TEST_DOB_MINOR = ['5','3','1393'];
    private $TEST_PARENT_EMAIL = 'plannedparenthood@gmail.com';
    private $SMTP_CONFIG;

    private $KIDS_AGE_BEFORE;
    private $CURRENT_SESSIONS;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array());
        ensure_session_active();
        //remove the user if exists from db
        $userDao = BOL_UserDao::getInstance();
        $user = $userDao->findByUserName($this->TEST_USERNAME);
        if($user!=null) {
            $questionDao = BOL_QuestionService::getInstance();
            $questionDao->deleteQuestionDataByUserId($user->getId());
            $userDao->deleteById($user->getId());
        }

        //set kids min age
        $config =  OW::getConfig();
        $this->KIDS_AGE_BEFORE = $config->getValue('frmcontrolkids', 'kidsAge');
        $config->saveConfig('frmcontrolkids', 'kidsAge', $this->TEST_KIDS_AGE);
        $this->SMTP_CONFIG = $config->getValue('base','mail_smtp_enabled');
        $config->saveConfig('base', 'mail_smtp_enabled', true);
    }

    public function testSignUpSuccessForAdultUser()
    {
        $test_caption = "signUpTest-testSignUpSuccessForAdultUser";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->setScreenSize(700, 2500);

        $this->url('join');

        try {
            $this->fillSignUpForm($this->TEST_USERNAME, $this->TEST_EMAIL, $this->TEST_PASSWORD,
                $this->TEST_PASSWORD_REPEAT, $this->TEST_FULL_NAME, $this->TEST_MALE, $this->TEST_DOB_ADULT);
            //------------------SUBMIT-----------
            $this->byName('joinForm')->submit();
            try {
                $this->waitUntilElementLoaded('byXPath', '//form[@name="emailVerifyForm"]', 5000);
            } catch (Exception $ex) {
                try {
                    $this->waitUntilElementLoaded('byCssSelector', '.ow_button.verify_later', 5000);
                } catch (Exception $ex) {
                    $this->handleException($ex, $test_caption, true);
                }
            }
            self::assertTrue(true);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testSignUpSuccessForMinorUser()
    {
        $test_caption = "signUpTest-testSignUpSuccessForMinorUser";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->setScreenSize(700, 2500);

        $this->url('join');

        try {
            $this->fillSignUpForm($this->TEST_USERNAME, $this->TEST_EMAIL, $this->TEST_PASSWORD,
                $this->TEST_PASSWORD_REPEAT, $this->TEST_FULL_NAME, $this->TEST_MALE, $this->TEST_DOB_MINOR);

            $parentMailTr = $this->waitUntilElementDisplayed('byCssSelector', '.ow_tr_last.parent_email');
            $parentMail = $parentMailTr->byName('parentEmail');
            $parentMail->value($this->TEST_PARENT_EMAIL);

            //------------------SUBMIT-----------
            $this->byName('joinForm')->submit();

            $this->waitUntilElementLoaded('byName', 'emailVerifyForm');
            try {
                //-----------------CHECK IF MAIL WAS SENT
                $mailDao = BOL_MailDao::getInstance();
                $sendExample = new OW_Example();
                $sendExample->andFieldEqual("recipientEmail", $this->TEST_PARENT_EMAIL);
                $res = $mailDao->findIdByExample($sendExample);

                if (empty($res)) {
                    $resp = OW::getDbo()->queryForList(" SELECT  `id`,  `recipientEmail` FROM ".OW_DB_PREFIX . "base_mail WHERE `recipientEmail` LIKE '".$this->TEST_PARENT_EMAIL."';");
                    if(count($resp) == 0){
                        fwrite(STDERR, OW_DB_PREFIX . "base_mail:");
                        $resp = OW::getDbo()->queryForList(" SELECT  `id`,  `recipientEmail` FROM ".OW_DB_PREFIX . "base_mail");
                        fwrite(STDERR, print_r($resp, true));
                        self::assertTrue(false);
                    }
                }
                $mailDao->deleteByRecipientEmail($this->TEST_PARENT_EMAIL);
                self::assertTrue(true);
            } catch (Exception $ex) {
                $this->handleException($ex,$test_caption,true);
            }

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
        $joinForm->byName('password')->value($pass);
        $joinForm->byName('repeatPassword')->value($repeat_pass);

        //------------------REAL NAME-----------
        $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
        $this->keys($real_name);

        //------------------GENDER-----------
        $i = 1;
        while($i<20) {
            $tmp2 = '(//td[div/ul/li/input[@type="radio"]])[' . $i . ']';
            $tmp = $joinForm->byXPath($tmp2);

            if ($tmp->displayed()) {
                if($is_male)
                    $tmp = $tmp->byXPath('div[1]/ul[1]/li[1]/input[1]');
                else
                    $tmp = $tmp->byXPath('div[1]/ul[1]/li[2]/input[1]');
                $tmp_id = $tmp->attribute('id');

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
                //DAY
                $tmp3 = $tmp->byXPath('div[1]/select[1]');
                $tmp3_name = $tmp3->attribute('name');
                $this->webDriver->executeScript('document.getElementsByName("'.$tmp3_name.'")[0].value = "'.$dob[0].'";', array());

                //MONTH
                $tmp3 = $tmp->byXPath('div[2]/select[1]');
                $tmp3_name = $tmp3->attribute('name');
                $this->webDriver->executeScript('document.getElementsByName("'.$tmp3_name.'")[0].value = "'.$dob[1].'";', array());

                //YEAR
                $tmp3 = $tmp->byXPath('div[3]/select[1]');
                $tmp3->byXPath('option[@value="'.$dob[2].'"]')->click();

                break;
            }
            $i++;
        }

        //------------SECURITY CODE
        //$this->webDriver->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
        //$this->webDriver->keys('09101001000');

        //------------------CAPTCHA, SESSIONS-----------
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C',',',$sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        if(session_status() == PHP_SESSION_ACTIVE){
            //Destroy current
            session_destroy();
        }
        session_id($sessionId);
        session_start();

        $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
        $joinForm->byName('captchaField')->value($captchaText);
        session_write_close();
        //---------------------------------------------------/
    }


    public function tearDown()
    {
        if($this->isSkipped)
            return;

        $config =  OW::getConfig();
        $config->saveConfig('frmcontrolkids', 'kidsAge', $this->KIDS_AGE_BEFORE);
        $config->saveConfig('base', 'mail_smtp_enabled', $this->SMTP_CONFIG);

        //delete users
        FRMSecurityProvider::deleteUser($this->TEST_USERNAME);
        parent::tearDown();
    }
}