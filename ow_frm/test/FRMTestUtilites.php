<?php
use Facebook\WebDriver\Exception\NoSuchElementException;

require_once "FRMUnitTestUtilites.php";
require_once "FacebookWebDriver.php";

/**
 * Created by PhpStorm.
 * User: Moradnejad
 * Date: 4/5/2016
 * Time: 4:08 PM
 */
class FRMTestUtilites extends FRMUnitTestUtilites
{
    protected $browserName = 'chrome';//firefox,chrome
    protected $isSkipped = false;

    /**
     * @var string
     */
    private $rootUrl;

    /**
     * @var IWebDriver
     */
    protected $webDriver;

    protected function setUp()
    {
        parent::setUp();
        if (defined('OW_URL_HOME')) {
            $this->rootUrl = OW_URL_HOME;
        }
        $this->webDriver = new FacebookWebDriver($this->browserName);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->webDriver->close();
    }

    /*
     * $id is element's name, id or text
     *                         $this->byLinkText();
                            $this->byCssSelector();
                        $this->byXPath();
                        $this->byTag();
                        $this->byClassName();
                        $this->byId();
                        $this->byName();
     */
    public function waitUntilElementLoaded($searchMethod, $searchValue, $wait_ms = DEFAULT_TIMEOUT_MILLIS)
    {
        $webdriver = $this->webDriver;
        try {
            $result = $this->waitUntil(function () use ($webdriver, $searchMethod, $searchValue) {
                return $webdriver->$searchMethod($searchValue);
            }, $wait_ms);
        } catch (Exception $ex) {
            $this->fail("Element $searchMethod('$searchValue') not loaded.");
            throw new RuntimeException("Unreachable code");
        }
        sleep(1);
        return $result;
    }

    public function waitUntilElementDisplayed($searchMethod, $searchValue, $wait_ms = DEFAULT_TIMEOUT_MILLIS)
    {
        $webdriver = $this->webDriver;
        try {
            return $this->waitUntil(function () use ($webdriver, $searchMethod, $searchValue) {
                /** @var IElement $element */
                $element = $webdriver->$searchMethod($searchValue);
                return $element->displayed() ? $element : null;
            }, $wait_ms);
        } catch (Exception $ex) {
            $this->fail("Element $searchMethod('$searchValue') not displayed.");
            throw new RuntimeException("Unreachable code");
        }
    }

    public function waitUntilElementHidden($searchMethod, $searchValue, $wait_ms = DEFAULT_TIMEOUT_MILLIS)
    {
        $ts = 0;
        while(true){
            sleep(1);
            $ts += 1000;
            try {
                $this->waitUntilElementDisplayed($searchMethod, $searchValue, 100);
            }catch (Exception $ex){
                return true;
            }
            if($ts >= $wait_ms){
                self::fail("Element $searchMethod('$searchValue') is still existing and visible.");
                throw new RuntimeException("Unreachable code");
            }
        }
        $webdriver = $this->webDriver;
        try {
            return $webdriver->waitUntil(function () use ($webdriver, $searchMethod, $searchValue) {
                try {
                    /** @var IElement $element */
                    $element = $webdriver->$searchMethod($searchValue);
                    return $element->displayed() ? null : $element;
                } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $ex) {
                    return true;
                }
            }, $wait_ms);
        } catch (Exception $ex) {
            $this->echoText($ex->getMessage());
            $this->fail("Element $searchMethod('$searchValue') is still existing and visible.");
            throw new RuntimeException("Unreachable code");
        }
    }

    public function checkIfXPathExists($xpath, $wait_ms = 1000)
    {
        $webDriver = $this->webDriver;
        try {
            $exists = $this->waitUntil(function () use ($webDriver, $xpath) {
                $webDriver->byXPath($xpath);
                return true;
            }, $wait_ms);
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $ex) {
            $exists = false;
        } catch (NoSuchElementException $ex) {
            $exists = false;
        }
        return $exists;
    }

    public function sign_in($identity, $password, $should_success = true, $fillCaptcha = false, $sessionId = 0, $sleep = 0)
    {
        //$should_success==true gives error when function can't login and vice versa.
        //$sessionId is only needed when function should fill captcha field.
        //see privacyTest::testScenario1 for more info

        $this->url(OW_URL_HOME . 'sign-in');
        sleep($sleep);
        $this->waitUntilElementLoaded('byXpath', "//form[@id='sign-in-standard']//input[@name='submit']");

        //FILL CAPTCHA IF EXISTS AND $fillCaptcha
        try {
            if ($fillCaptcha) {
                if ($this->checkIfXPathExists('//input[@name="captchaField"]')) {
                    $cp = $this->byName('captchaField');
                    if ($cp->displayed()) {
                        //------------------CAPTCHA, SESSIONS-----------
                        session_id($sessionId);
                        session_start();
                        $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
                        session_write_close();
                        //---------------------------------------------------/
                        $cp->clear();
                        $cp->value($captchaText);
                    }
                }
            }
        } catch (Exception $ex) {
        }

        //FILL Other fields
        try {
            $form = $this->byId("sign-in-standard");
            $this->waitUntilElementLoaded('byName', 'identity');
            $form->byName('identity')->clear();
            $form->byName('identity')->value($identity);
            $form->byName('password')->clear();
            $form->byName('password')->value($password);
            $rememberMe = $form->byName('remember');
            $checked = $rememberMe->attribute('value');
            if (!isset($checked) || $checked !== 'on')
                $rememberMe->click();

            $form->submit();

            try {
                $this->waitUntilElementLoaded('byClassName', 'ow_message_node');
            }catch(Exception $ex){}
            $success = $this->checkIfXPathExists('//*[contains(@class,"console_my_profile")]');
			if($should_success && !$success) {
                $this->echoText('Login Failed.');
                if($sleep < 10) {
                    $this->echoText('Retry...');
                    $this->sign_in($identity, $password, $should_success, $fillCaptcha, $sessionId, $sleep * 4 + 1);
                }
            }

        } catch (Exception $ex) {
            $this->handleException($ex,'sign_in_error',true);
        }
    }

    public function mobile_sign_in($identity, $password, $fillCaptcha = false, $sessionId = 0)
    {
        //$sessionId is only needed when function should fill captcha field.
        //see mForumTest::testScenario1 for more info

        $this->url(OW_URL_HOME . 'sign-in');

//        $this->url(OW_URL_HOME . '');
//        $this->execute(array(
//            'script' => 'document.getElementById(\'owm_header_right_btn\').scrollIntoView(true);',
//            'args' => array()
//        ));
//        $this->byId('owm_header_right_btn')->click();
        $form = $this->byCssSelector('#main form[name="sign-in"]');
        //FILL CAPTCHA IF EXISTS AND $fillCaptcha
        try {
            if ($fillCaptcha) {
                $this->waitUntilElementLoaded("byName", "captchaField", 3000);
                if ($this->checkIfXPathExists('//*[@id="main"]//input[@name="captchaField"]')) {
                    $cp = $form->byName('captchaField');
                    if ($cp->displayed()) {
                        ensure_no_session();
                        session_id($sessionId);
                        @session_start();
                        $captchaText = (OW_Session::getInstance()->get('securimage_code_value')['default']);
                        session_write_close();
                        $cp->clear();
                        $cp->value($captchaText);
                    }
                }
            }
        } catch (Exception $ex) {
        }

        //FILL Other fields
        try {
            $form->byName('identity')->clear();
            $form->byName('identity')->value($identity);
            $form->byName('password')->clear();
            $form->byName('password')->value($password);

            $form->submit();
            /*
            $this->execute(array(
                'script' => "document.getElementsByName('submit')[0].setAttribute('name', 'btn_submit');",
                'args' => array()
            ));
            $this->byCssSelector('div.ow_sign_in input[name=submit2]')->click();
            $this->byName('sign-in')->submit();

            $this->execute(array(
                'script' => "document.getElementsByName('sign-in')[0].submit();",
                'args' => array()
            ));
            */
        } catch (Exception $ex) {
            $this->handleException($ex,'sign_in_error',true);
            $this->assertTrue(false);
        }
    }

    public function checkIfPluginsNeedUpdate()
    {
        $this->url(OW::getRouter()->urlForRoute('admin_default'));
        try {
            $counter = 0;
            for (; $counter < 100; $counter++) {
                $this->byCssSelector('input.ow_positive[type="button"][onclick*="/ow_updates/index.php?plugin="]')->click();
                $this->waitUntilElementLoaded('byCssSelector', 'input.ow_positive[type="button"][onclick*="/ow_updates/index.php?plugin="]');
            }
            if ($counter === 100) {
                //There is a problem
                $this->assertTrue(false);
            }
        } catch (Exception $ex) {
        }
    }

    protected function hide_element($className, $style_name = "visibility", $value = "hidden")
    {
        try {
            $this->executeScript("document.getElementsByClassName('" . $className . "')[0].style.$style_name = '$value';");
        } catch (Exception $ex) {
            //$this->echoText('hide_element_error:'.$ex);
        }
    }

    protected function echoText($text, $bounding_box = false, $title = "LOG")
    {
        if ($bounding_box) {
            fwrite(STDERR, "\n-----------------------------$title------------------------------------\n");
            fwrite(STDERR, "$text\n");
            fwrite(STDERR, "---------------------------------------------------------------------\n");
        } else
            fwrite(STDERR, "\n==========$title====>$text\n");
    }

    protected function checkRequiredPlugins($requiredPlugins)
    {
        foreach ($requiredPlugins as $pluginKey) {
            $plugin = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);
            if ($plugin == null || !$plugin->isActive()) {
                $this->echoText('"' . $pluginKey . '" plugin is not active.'
                    , false, 'Test skipped');
                $this->isSkipped = true;
                self::markTestSkipped('PLUGIN_NOT_INSTALLED');
                return;
            }
        }
    }

    protected function checkIfMobileIsActive()
    {
        if (!OW::getConfig()->configExists('base', 'disable_mobile_context') || (bool) OW::getConfig()->getValue('base', 'disable_mobile_context')) {
            $this->echoText('"Mobile is not active.'
                , false, 'Test skipped');
            $this->isSkipped = true;
            $this->markTestSkipped('MOBILE_IS_DISABLE');
            return;
        }
    }


    protected function handleException(Exception $ex,$tag='',$shouldFail=true,$screenShot = true){
        $text = $ex;

        if ($screenShot) {
            $filename = $this->saveSnapshot($tag);
            if (isset($filename))
                $text .= "\n\nSnapshot: " . $filename;
        }

        $this->echoText($text,true,'Exception');
        if($shouldFail){
            self::assertTrue(false);
        }
    }

    protected function saveSnapshot($name){
        $filename = null;
        if (getenv("SNAPSHOT_DIR")) {
            $filename = getenv("SNAPSHOT_DIR") . $name . '_' . time() . '.png';
            OW::getStorage()->fileSetContent($filename, $this->currentScreenshot());
        }
        return $filename;
    }

    protected function signOutDesktop(bool $deleteCookies = false){
        //method 3
        if($deleteCookies) {
            try {
                $key = 'adminToken';
                $this->webDriver->deleteCookie($key);
            }catch(Exception $ex){}
            try {
                $key = 'ow_login';
                $this->webDriver->deleteCookie($key);
            }catch(Exception $ex){}
            try {
                $key = OW::getSession()->getName();
                $this->webDriver->deleteCookie($key);
            }catch(Exception $ex){}
            return;
        }

        //method 1
        try {
            $divs = $this->byCssSelector('div.ow_console_item.ow_console_dropdown.ow_console_dropdown_hover');
            if (!empty($divs)) {
                $divs->click();
                $div = $this->byCssSelector('div.ow_console_dropdown_pressed');
                if (is_array($div) && !empty($divs))
                    $div = $div[0];
                $id = $div->attribute('id') . '_content';
                $list = $this->webDriver->findElementsByCssSelector('#' . $id . ' ul.ow_console_dropdown li');
                if (is_array($list) && !empty($list)) {
                    $size = sizeof($list);
                    $list[$size - 1]->click();
                }
            }
        } catch (Exception $ignored) {
        }

        //method 2
        $this->url(OW_URL_HOME.'sign-out');
    }

    protected function acceptConfirm()
    {
        sleep(1);
        $this->waitUntilElementDisplayed('byCssSelector', '.jconfirm-buttons .btn.btn-orange');
        $this->waitUntil(function () {
            try {
                $this->byCssSelector('.jconfirm-buttons .btn.btn-orange')->click();
            } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $ex) {
                // The confirm button may be hidden due to the last click; no problem
                if (strpos($ex->getMessage(), "no such element") !== false) {
                    return true;
                }
                throw $ex;
            }
            // The confirm button should disappear after a while; otherwise, click again
            /*$this->waitUntilElementHidden('byCssSelector', '.jconfirm-buttons .btn.btn-orange',
                3000);*/
            return true;
        }, DEFAULT_TIMEOUT_MILLIS);
//        $this->acceptAlert();
    }

    function url(string $url="")
    {
        if (empty($url)){
            return $this->getUrl();
        }
        $url_parts = parse_url($url);
        if (!isset($url_parts['scheme'])) {
            $url = $this->rootUrl . $url;
        }

        try {
            $this->webDriver->url($url);
        }catch (Facebook\WebDriver\Exception\WebDriverCurlException $ex){
            $this->echoText('WebDriverCurlException Error -> retry');
            $this->webDriver->url($url);
        }
    }

    function getUrl(){
        return $this->webDriver->executeScript('return window.location;',array());
    }

    function byLinkText(string $linkText): IElement
    {
        return $this->webDriver->byLinkText($linkText);
    }

    function byCssSelector(string $cssSelector): IElement
    {
        return $this->webDriver->byCssSelector($cssSelector);
    }

    function byXPath(string $xpath): IElement
    {
        return $this->webDriver->byXPath($xpath);
    }

    function byTag(string $tag): IElement
    {
        return $this->webDriver->byTag($tag);
    }

    function byClassName(string $className): IElement
    {
        return $this->webDriver->byClassName($className);
    }

    function byId(string $id): IElement
    {
        return $this->webDriver->byId($id);
    }

    function byName(string $name): IElement
    {
        return $this->webDriver->byName($name);
    }

    function executeScript(string $script)
    {
        $this->webDriver->executeScript($script);
    }

    function waitUntil(Closure $callable, int $timeoutMillis)
    {
        return $this->webDriver->waitUntil($callable, $timeoutMillis);
    }

    function currentScreenshot()
    {
        return $this->webDriver->currentScreenshot();
    }

    function keys($keys){
//        example for for CTRL+S
//        $this->webDriver->keys([WebDriverKeys::COMMAND, 'S']);
//        TODO:: replace all PHPUnit_Extensions_Selenium2TestCase_Keys::XXX with WebDriverKeys::XXX
        $this->webDriver->keys($keys);
    }

    function setScreenSize($w, $h){
        $this->webDriver->webDriver->manage()->window()->setSize(
            new Facebook\WebDriver\WebDriverDimension($w, $h));
    }

    public function scrollDown($length='all'){
        if($length != 'all'){
            $y = $this->executeScript('return document.body.scrollHeight;');
            $length += intval($y);
            $this->executeScript('window.scrollTo(0, '.$length.');');
            return;
        }
        $last_height  = $this->executeScript('document.body.scrollHeight;');
        while (true)
        {
            $this->executeScript('window.scrollTo(0, document.body.scrollHeight);');
            $new_height = $this->executeScript('return document.body.scrollHeight;');
            if ($new_height == $last_height) {
                break;
            }
            else
                $last_height = $new_height;
            sleep(10);
        }
    }

    public function scrollToElementId($id){
        $this->executeScript('document.getElementById(\'' . $id . '\').scrollIntoView(true);');
    }

}