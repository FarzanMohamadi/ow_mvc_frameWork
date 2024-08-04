<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/09/11
 */

class slideshowTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('slideshow','photo'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1969/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
    }

    public function testSlideshow()
    {
        //----SCENARIO 1 -
        // User1 uploads a new photo
        // User1 goes to photos and clicks on the latest photo
        // User1 can see slideshow

        $test_caption = "slideshowTest-testSlideshow";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        //----------USER1
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);

        try {
            //BEFORE CHECK
            $this->url('photo/viewlist/latest/photos');
            $this->waitUntilElementHidden('byId', 'browse-photo-preloader');

            try {
                $item1 = $this->byXPath('//*[@class="ow_photo_item"][1]/..');
                $img1 = $item1->attribute('id');
            }catch (Exception $ex){
                $img1 = -1;
            }
        }catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
            return;
        }

        try {
            $file_path = OW_DIR_ROOT.'ow_unittest'.DS.'test.png';

            //put image in component
            $this->byId('add-new-photo-album')->click();
            $this->waitUntilElementLoaded('byXPath', '//div[contains(@class,"floatbox_canvas_active")]//div[@id="drop-area"]');

            //show input
            $this->executeScript("$('#add-new-photo-container > .ow_hidden').removeClass('ow_hidden');");
            $res = $this->checkIfXPathExists('//div[@id="add-new-photo-container"]//form[@id="upload-form"]/input');
            self::assertTrue($res);

            $attachment = $this->byXPath('//div[@id="add-new-photo-container"]//form[@id="upload-form"]/input');
            $attachment->value($file_path);

            $this->waitUntilElementLoaded('byXPath','//div[@id="slot-area"]/div[contains(@class,"ow_photo_preview_edit")]');

            $this->byXPath('//*[@id="photo-album-list"]//input[@name="album"]')->click();
            $this->byCssSelector('#photo-album-form .ow_dropdown_list li')->click();
            $this->byCssSelector('#photo-album-form')->byName('album-name')->value('sel-test');
            $this->byCssSelector('#photo-album-form')->byName('description')->value('sel-test');
            $this->byCssSelector('.floatbox_canvas_active .ow_photo_upload_submit')->click();
        }catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
            return;
        }

        try {
            //AFTER CHECK
            $this->url('photo/viewlist/latest/photos');
            $this->waitUntilElementHidden('byId', 'browse-photo-preloader');
            $this->waitUntilElementLoaded('byXPath', '//*[@class="ow_photo_item"][1]//img');
            $item1 = $this->byXPath('//*[@class="ow_photo_item"][1]/..');
            $img2 = $item1->attribute('id');

            self::assertNotEquals($img1,$img2);
        }catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }


    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        parent::tearDown();
    }
}