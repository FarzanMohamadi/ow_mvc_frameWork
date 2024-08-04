<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/11/26
 */

class mVideoTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $Youtube_CODE = '<iframe width="560" height="315" src="https://www.youtube.com/embed/RRI6iSrS1kc" frameborder="0" allowfullscreen></iframe>';
    private $Aparat_CODE = '<style>.h_iframe-aparat_embed_frame{position:relative;} .h_iframe-aparat_embed_frame .ratio {display:block;width:100%;height:auto;} .h_iframe-aparat_embed_frame iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style><div class="h_iframe-aparat_embed_frame"> <span style="display: block;padding-top: 57%"></span><iframe src="https://www.aparat.com/video/video/embed/videohash/GjYCP/vt/frame" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" ></iframe></div>';
    private $Scenario1_TITLE = 'What a wonderful place!';
    private $Scenario2_TITLE = 'So cool!';

    private $userService;
    private $user1,$user2;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('video'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {
        //----SCENARIO 1 -
        // User1 submits a video
        // User1 can be seen

        $test_caption = "mVideoTest-testScenario1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();


        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------CREATE A video
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . "video");
            $this->byXPath('//input[@id="btn-add-new-video"]')->click();

            $this->byId('input_type_code')->click();
            $this->byName('code')->value($this->Aparat_CODE);
            $this->byName('title')->value($this->Scenario1_TITLE);
            try {
                $this->byXPath('//input[@name="add"]')->click();
            }catch (Exception $ex) {
                $this->echoText('problem with curl aparat1. not considered an error!');
                //$this->echoText($ex, true);
                return;// problem with curl
            }

            sleep(1);
            //check exists
            self::assertTrue($this->checkIfXPathExists('//*[contains(text(),"'.$this->Scenario1_TITLE.'")]'));
            $vid = $this->webDriver->getAttributeByXPath('//input[@name="entityId"]','value');
            VIDEO_BOL_ClipService::getInstance()->deleteClip($vid);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function testScenario2()
    {
        //----SCENARIO 2 -
        // User2 uploads a video
        // User2 can be seen

        $test_caption = "mVideoTest-testScenario2";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------Upload a video
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);

            $this->url(OW_URL_HOME . "video");
            $this->byXPath('//input[@id="btn-add-new-video"]')->click();

            $this->byId('input_type_upload')->click();
            $attachment = $this->byXPath('//input[@id="videoUpload"]');
            $file_path = getcwd().DS. $test_caption . '.mp4';
            OW::getStorage()->fileSetContent($file_path, $this->currentScreenshot());
            $attachment->value($file_path);

            $this->byName('title')->value($this->Scenario2_TITLE);
            try {
                $this->byXPath('//input[@name="add"]')->click();
            }catch (Exception $ex) {
                $this->echoText('problem with curl aparat2. not considered an error!');
                //$this->echoText($ex, true);
                return;// problem with curl
            }
            OW::getStorage()->removeFile($file_path);
            sleep(1);

            //check exists
            self::assertTrue($this->checkIfXPathExists('//*[contains(text(),"'.$this->Scenario2_TITLE.'")]'));
            $vid = $this->webDriver->getAttributeByXPath('//input[@name="entityId"]','value');
            VIDEO_BOL_ClipService::getInstance()->deleteClip($vid);
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        parent::tearDown();
    }
}