<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/31
 */

class mNewsfeedTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $test_caption;
    protected $file_path;
    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('newsfeed'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {

        //----SCENARIO 1 -
        // User1 goes to newsfeed.
        // User1 posts status1 (just text)
        // User1 checks if status1 is posted
        // User1 posts status2 (text+photo)
        // User1 checks if status2 is posted
        // --User1 posts status3 (text+audio)
        // --User1 checks if status2 is posted

        $this->test_caption = "mPostNewsfeedTest-testScenario1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------CREATE A TOPIC
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->postAndCheck("this is a simple text!");
            sleep(1);
           // $this->postAndCheck("this is a text with photo!", true, false);
            sleep(1);
            //$this->postAndCheck("this is a text with audio!", false, true);
        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function postAndCheck($text, $photo=false, $audio=false)
    {
        $text = date('D, j M Y H:i:s O')." : ".$text;
        //--------POST A NEW STATUS
        $this->file_path = "";
        try {
            $this->url(OW_URL_HOME . "newsfeed");
            $this->byClassName('owm_newsfeed_status_update_circle')->click();
            $this->byId('newsfeed_status_input')->click();
            $this->byId('newsfeed_status_input')->value($text);

           /* if($photo) {
                $newsFeedForm = $this->byName('newsfeed_update_status');
                $attachment = $newsFeedForm->byId('newsfeed-att-file');
                $this->file_path = getcwd() . DS . $this->test_caption . '.png'; //"D:\\programmer.jpg"
                //$this->echoText($this->file_path);
                OW::getStorage()->fileSetContent($this->file_path, $this->currentScreenshot());
                $attachment->value($this->file_path);
                //$this->waitUntilElementLoaded("byXPath", '//input[@name="attachment" and @value != ""]');
            }*/
             $this->waitUntilElementLoaded('byName', 'statusPrivacy');
             $this->byXPath('//option[@value="everybody"]')->click();//only_for_me, everybody, friends_only
             $this->byXPath('//input[@name="save"]')->click();
        }catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
            if($photo)
                OW::getStorage()->removeFile($this->file_path);
            return false;
        }

        //--------CHECK IF POSTED
        $div = $this->waitUntilElementLoaded('byXPath',
            '//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"'.$text.'")]');
        $photo_posted = $this->checkIfXPathExists('//div[contains(@class,"owm_newsfeed_body_status") and contains(text(),"'
            .$text.'")]/..//div[contains(@class,"owm_newsfeed_imglist_wrap")]');
        self::assertEquals($photo,$photo_posted);
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        if(OW::getStorage()->fileExists($this->file_path)) {
            OW::getStorage()->removeFile($this->file_path);
        }
        parent::tearDown();
    }
}