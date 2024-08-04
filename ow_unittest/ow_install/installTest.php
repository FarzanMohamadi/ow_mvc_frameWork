<?php
use Facebook\WebDriver\WebDriverKeys;
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 9/19/2017
 * Time: 1:51 PM
 */
class InstallTest extends FRMTestUtilites{
    private $homeUrl;
    private $adminEmail;
    private $adminUsername;
    private $adminPassword;
    private $dbHost;
    private $dbUser;
    private $dbName;
    private $dbPassword;
    private $siteTitle;

    protected function setUp()
    {
        parent::setUp();
        ensure_session_active();
        $this->homeUrl = getenv('home_url');
        $this->adminEmail = getenv('admin_email');
        $this->adminUsername = getenv('admin_username');
        $this->adminPassword = getenv('admin_password');
        $this->dbHost = getenv('db_host');
        $this->dbUser = getenv('db_user');
        $this->dbName = getenv('db_name');
        $this->dbPassword = getenv('db_password');
        $this->siteTitle = getenv('site_title');

        self::assertNotNull($this->homeUrl);
        self::assertNotEmpty($this->homeUrl);
        self::assertNotEmpty($this->adminEmail);
        self::assertNotEmpty($this->adminUsername);
        self::assertNotEmpty($this->adminPassword);
        self::assertNotEmpty($this->dbHost);
        self::assertNotEmpty($this->dbUser);
        self::assertNotEmpty($this->dbName);
        self::assertNotEmpty($this->dbPassword);
        self::assertNotEmpty($this->siteTitle);
    }

    public function testInstall(){
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url($this->homeUrl);
        //rules
        try{
            $this->waitUntilElementLoaded('byName','rules_accepted');
            $this->byName('rules_accepted')->click();
            $this->byTag('form')->submit();
        }catch (Exception $ex){
            $this->handleException($ex,'install_rules',true);
        }
        //site setting
        try{
            $this->waitUntilElementLoaded('byName','site_title');
            $this->byName('site_title')->value($this->siteTitle);
            $this->byName('admin_email')->value($this->adminEmail);
            $this->byName('admin_username')->value($this->adminUsername);
            $this->byName('admin_password')->value($this->adminPassword);
            $this->byTag('form')->submit();
        }catch (Exception $ex){
            $this->handleException($ex,'install_site_setting',true);
        }
        //db
        try{
            $this->waitUntilElementLoaded('byName','db_host');
            $this->byName('db_host')->value($this->dbHost);
            $this->byName('db_user')->value($this->dbUser);
            $this->byName('db_name')->value($this->dbName);
            $this->byName('db_password')->value($this->dbPassword);
            $prefix = UTIL_String::getRandomStringWithPrefix('',2).'_';
            $prefix = strtolower($prefix);
            $this->echoText('tables prefix: '.$prefix);
            $this->byName('db_prefix')->clear();
            $this->byName('db_prefix')->value($prefix);
            $this->byTag('form')->submit();
        }catch (Exception $ex){
            $this->handleException($ex,'install_db',true);
        }
        //finalize
        try{
            $this->waitUntilElementLoaded('byXPath','//input[@type="submit"]');
//            $this->keys(WebDriverKeys::TAB);
//            $this->keys(WebDriverKeys::TAB);
//            $this->keys(WebDriverKeys::ENTER);
//            $this->byXPath('//input[@type="submit"]')->click();
            $this->byTag('form')->submit();
        }catch (Exception $ex){
//            $this->handleException($ex,'install_cron',true);
        }
        //plugins
        try{
            $this->waitUntilElementLoaded('byId','groups',30*1000);
            if(!$this->checkIfXPathExists('//input[@id="groups"]')){
                $this->saveSnapshot('install_test_cron_page_fail');
                $this->echoText('Cron page again!',false,'InstallTest');
                $this->waitUntilElementLoaded('byCssSelector','input[type="submit"]');
//                $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
//                $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
//                $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::ENTER);
                $this->waitUntilElementLoaded('byXPath','//input[@type="submit"]',30*1000);
            }
            $this->waitUntilElementLoaded('byXPath','//input[@id="blogs"]',30*1000);

            $this->byXPath('//input[@id="blogs"]')->click();
            $this->byId('groups')->click();
            $this->byId('event')->click();
//            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
//            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::ENTER);
            $this->byTag('form')->submit();
        }catch (Exception $ex){
//            $this->handleException($ex,'install_plugins',true);
        }
        //main page
        try{
//            $this->echoText($this->getUrl());
//            self::assertTrue(strpos($this->url(), 'install/completed') !== false);
            $this->url($this->homeUrl);
            $this->waitUntilElementLoaded('byCssSelector','input[name="sex"][value="1"]');
            $this->byCssSelector('input[name="sex"][value="1"]')->click();
            //day_birthday
            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::DOWN);
            //month_birthday
            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::DOWN);
            //year_birthday
            $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::TAB);
            foreach (range(0, 18) as $ignored)
                $this->keys(PHPUnit_Extensions_Selenium2TestCase_Keys::DOWN);
            $this->byName('submit')->click();
            $this->waitUntilElementLoaded('byCssSelector','div.ow_page_container');
        }catch (Exception $ex){
            $this->handleException($ex,'install_finalize',true);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
