<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/31
 */

class mPhotoTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1;

    private $test_caption;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('photo'));
        $this->checkIfMobileIsActive();

        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {
        //----SCENARIO 1 -
        // User1 goes to photo plugin
        // User1 posts a picture to a new album
        // User1 posts a picture to the same album

        $this->test_caption = "mPostPictureTest-testScenario1";

        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'

        //---------- Posts a picture
        try {
            $this->mobile_sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,$sessionId);
            $this->waitUntilElementLoaded('byId','owm_header_right_btn',5000);
            $this->postAndCheck("this is a text with photo!", "albumTest");
            $this->postAndCheck("this is a text with photo2!", "albumTest");
        } catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function postAndCheck($desc, $album)
    {
        $desc = date('D, j M Y H:i:s O')." : ".$desc;
        $img1 = -1;
        try {
            //BEFORE CHECK
            $this->url('photo');
            sleep(1);
            if($this->checkIfXPathExists('//*[@class="owm_photo_list_item"][1]')) {
                $item1 = $this->byXPath('//*[@class="owm_photo_list_item"][1]');
                $img1 = $item1->attribute('data-ref');
            }
        }catch (Exception $ex) {
            $img1 = 0;
        }

        //POST IMAGE
        $this->byClassName('owm_add_photo')->click();
        $submitForm = $this->byName('upload-form');

        //image
        $attachment = $this->byId('upload-file-field');
        $this->executeScript('$("#upload-file-field").css("opacity",1)');
        $file_path = OW_DIR_ROOT.'ow_unittest'.DS.'test.png';
        $attachment->value($file_path);
        sleep(2);

        // album
        try{
            $this->byXPath('//select[@id="album_select"]//option[1]')->click();
        }catch (Exception $ex) {
        }
        $submitForm->byName('album')->value($album);
        $submitForm->submit();

        //AFTER CHECK photo is added
        $this->url('photo');
        sleep(1);
        $img2 = -2;
        if($this->checkIfXPathExists('//*[@class="owm_photo_list_item"][1]')) {
            $item1 = $this->byXPath('//*[@class="owm_photo_list_item"][1]');
            $img2 = $item1->attribute('data-ref');
        }
        self::assertNotEquals($img1,$img2);
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