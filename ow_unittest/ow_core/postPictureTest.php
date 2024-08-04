<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/31
 */

class postPictureTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('photo', 'newsfeed'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testPostPicture()
    {
        //User1 goes to dashboard
        //Sends a newsfeed with photo
        //Checks if the photo is uploaded

        $test_caption = "postPictureTest-testPostPicture";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        //----------LOGIN
        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            //BEFORE CHECK
            $this->url('photo/viewlist/latest/photos');
            $this->waitUntilElementHidden('byId', 'browse-photo-preloader');
            $item1 = $this->byXPath('//*[@class="ow_photo_item"][1]/..');
            $img1 = $item1->attribute('id');
        }catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
            return;
        }

        try {
            //POST IMAGE
            $this->url('photo/viewlist/latest/photos');

            //image
            $file_path = OW_DIR_ROOT.'ow_unittest'.DS.'test.png'; //"D:\\programmer.jpg"

            //put image in form
            $this->byId('add-new-photo-album')->click();
            $this->waitUntilElementLoaded('byId', 'add-new-photo-container');
            $this->executeScript("$('.new-album, #add-new-photo-container .ow_hidden').css('display','block');");

            $this->waitUntil(function () use ($file_path) {
                $attachment = $this->byId('upload-form')->byName('file');
                try {
                    $attachment->value($file_path);
                    $this->waitUntilElementLoaded("byXPath",
                        '//*[@id="slot-area"]//*[@class="ow_photo_preview_image"]', 3000);
                    return true;
                } catch (Exception $ex) {
                    $this->saveSnapshot('temp-picture-preview-not-loaded-1');
                    $attachment->value('');
                    $this->saveSnapshot('temp-picture-preview-not-loaded-2');
                    return null;
                }
            }, DEFAULT_TIMEOUT_MILLIS);

            $this->byName('album-name')->value('test_album');
            $this->byCssSelector('.new-album textarea[name~="description"]')->value('who cares.');

            $statusPrivacy = $this->byName('statusPrivacy');
            $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only
            $this->byClassName('ow_photo_upload_submit')->click();
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

            self::assertNotEquals($img1, $img2);
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
        parent::tearDown();
    }
}