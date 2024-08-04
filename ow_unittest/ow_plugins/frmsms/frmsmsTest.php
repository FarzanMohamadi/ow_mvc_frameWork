<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/09/11
 */
class frmsmsTest extends FRMTestUtilites
{
    const USER_PREFIX = 'register_test_';
    private static $PLUGIN_KEY = 'frmsms';
    private $mandatoryUserApproveLastValue = null;
    private $createdUsernameArray = array();
    private $CURRENT_SESSIONS;
    private $pluginIsInstalled;

    protected function setUp()
    {
        parent::setUp();
        self::markTestSkipped('Test is useless');

        $plugin = BOL_PluginDao::getInstance()->findPluginByKey(self::$PLUGIN_KEY);
        $this->pluginIsInstalled = isset($plugin);
        if(!$this->pluginIsInstalled){
            BOL_PluginService::getInstance()->install(self::$PLUGIN_KEY);
        }
        $this->checkRequiredPlugins(array(self::$PLUGIN_KEY));
    }

    public function setUpPage()
    {
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
    }

    public function testRegister()
    {
        try {
            $registrationUrl = OW::getRouter()->urlForRoute('base_join');
            $this->url($registrationUrl);
            $this->register();
        } catch (Exception $ex) {
            $this->handleException($ex, 'frmsms_test_register', true);
        }
    }

    public function testRegisterWithInviteLink()
    {
        try {
            if (OW::getConfig()->configExists('base', 'who_can_join')) {
                $this->mandatoryUserApproveLastValue = OW::getConfig()->getValue('base', 'who_can_join');
            } else {
                $this->mandatoryUserApproveLastValue = null;
            }
            OW::getConfig()->saveConfig('base', 'who_can_join', 2);
            $registrationUrl = $this->generateInviteLink();
            $this->url($registrationUrl);
            $this->register();
        } catch (Exception $ex) {
            $this->handleException($ex, 'frmsms_test_register', true);
        }
    }

    private function register()
    {
        $this->waitUntilElementLoaded('byName','joinForm');
        $randomToken = UTIL_String::getRandomStringWithPrefix(self::USER_PREFIX,5);
        $this->createdUsernameArray[] = $randomToken;
        $this->fillSignUpForm($randomToken,$randomToken.'@gmail.com',$randomToken,$randomToken,$randomToken,true,['4','2','1346']);
        $this->byName('joinForm')->submit();
        $element = $this->byCssSelector('input[name="mobile_code"]');
        self::assertTrue(isset($element));
    }

    /**
     * @return string
     */
    private function generateInviteLink()
    {
        $dto = new BOL_InviteCode();
        $dto->setCode(UTIL_String::getRandomString(20));
        $dto->setUserId(0);
        $dto->setExpiration_stamp(time() + 3600 * 24 * 30);
        BOL_InviteCodeDao::getInstance()->save($dto);
        return OW_URL_HOME . 'join?code=' . $dto->code;
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
        $tmp2 = '//tr[td[2]/input[@name="repeatPassword"]]';
        $i = 1;
        while($i<20) {
            $tmp2 = $tmp2.'/following::tr';
            $tmp = $joinForm->byXPath($tmp2);
            $tmp3 = $tmp->byXPath('td[2]');
            if ($tmp3->displayed()) {
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
            $tmp2 = '(//td[ul/li/input[@type="radio"]])[' . $i . ']';
            $tmp = $joinForm->byXPath($tmp2);

            if ($tmp->displayed()) {
                if($is_male)
                    $tmp = $tmp->byXPath('ul[1]/li[1]/input[1]');
                else
                    $tmp = $tmp->byXPath('ul[1]/li[2]/input[1]');
                $tmp_id = $tmp->attribute('id');

                //$this->echoText($i . ' ' . $tmp2 . '    ' . $tmp_id);
                $this->executeScript('document.getElementById("' . $tmp_id . '").checked=true;');
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
                //$this->echoText($i . ' ' . $tmp2);

                //DAY
                $tmp3 = $tmp->byXPath('div[1]/select[1]');
                $tmp3_name = $tmp3->attribute('name');
                $this->executeScript('document.getElementsByName("'.$tmp3_name.'")[0].value = "'.$dob[0].'";');

                //MONTH
                $tmp3 = $tmp->byXPath('div[2]/select[1]');
                $tmp3_name = $tmp3->attribute('name');
                $this->executeScript('document.getElementsByName("'.$tmp3_name.'")[0].value = "'.$dob[1].'";');

                //YEAR
                $tmp3 = $tmp->byXPath('div[3]/select[1]');
                $tmp3->byXPath('option[@value="'.$dob[2].'"]')->click();

                break;
            }
            $i++;
        }

        //------------Mobile-------------------------------
        $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
        $mobile = '09'.rand(100000000,999999999);
        $this->keys($mobile);

        //------------Term of use-------------------------------
        try {
            $element = $this->byName('termOfUse');
            $element->click();
        } catch (Exception $ignored) {
        }

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
        if (empty($captchaText)) {
            OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . 'empty_captcha.png', $this->currentScreenshot());
        }
        else {
            $joinForm->byName('captchaField')->value($captchaText);
        }
        session_write_close();
        //---------------------------------------------------/
    }

    public function tearDown()
    {
        if ($this->isSkipped)
            return;

        //reset mandatory_user_approve to before test state
        if (OW::getConfig()->configExists('base', 'who_can_join')) {
            if ($this->mandatoryUserApproveLastValue != null) {
                OW::getConfig()->saveConfig('base', 'who_can_join', $this->mandatoryUserApproveLastValue);
            } else {
                OW::getConfig()->deleteConfig('base', 'who_can_join');
            }
        }

        foreach ($this->createdUsernameArray as $username)
        {
            $user = BOL_UserDao::getInstance()->findByUserName($username);
            if(isset($user)) {
                BOL_UserDao::getInstance()->deleteById($user->getId());
            }
        }

        if (!$this->pluginIsInstalled) {
            BOL_PluginService::getInstance()->uninstall(self::$PLUGIN_KEY);
        }
        parent::tearDown();
    }
}