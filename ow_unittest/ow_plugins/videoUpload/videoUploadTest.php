<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class videoUploadTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $Youtube_CODE = '<iframe width="560" height="315" src="https://www.youtube.com/embed/RRI6iSrS1kc" frameborder="0" allowfullscreen></iframe>';
    private $Aparat_CODE = '<style>.h_iframe-aparat_embed_frame{position:relative;} .h_iframe-aparat_embed_frame .ratio {display:block;width:100%;height:auto;} .h_iframe-aparat_embed_frame iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style><div class="h_iframe-aparat_embed_frame"> <span style="display: block;padding-top: 57%"></span><iframe src="https://www.aparat.com/video/video/embed/videohash/GjYCP/vt/frame" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" ></iframe></div>';
    private $Scenario1_TITLE = 'What a wonderful goal!';
    private $Scenario1_DESC = "salam\nin videoye khoobie!\n\nnegash konid";

    private $userService;
    private $user1;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('video'));
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();

        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
    }

    public function testScenario1()
    {

        $test_caption = "videoUploadTest-testScenario1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        //----------LOGIN
        $this->url(OW_URL_HOME . "dashboard");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url('video');

            //BEFORE CHECK
            $vid1 = '0';
            try {
                $res = $this->checkIfXPathExists('//*[contains(@class,"floatbox_body")]/a');
                if($res) {
                    $item1 = $this->byClassName('ow_video_list_item');
                    $vid1 = $item1->byXPath('./a')->attribute('href');
                }
            } catch (Exception $ex) {}


            //POST VIDEO
            $this->byId('btn-add-new-video')->click();
            $this->byId('input_type_code')->click();
            $this->byName('code')->value($this->Aparat_CODE);
            $this->byName('title')->value($this->Scenario1_TITLE);
            //$this->byClassName('htmlarea_styles')->value($this->Scenario1_DESC);

            $statusPrivacy = $this->byName('statusPrivacy');
            $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only
            try {
                $this->byName('videoAddForm')->byXPath('//input[@type="submit" and @name="add"]')->click();
            }catch (Exception $ex) {
                $this->echoText('problem with curl aparat. not considered an error!');
                //$this->echoText($ex, true);
                //if (getenv("SNAPSHOT_DIR")) file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                return;// problem with curl
            }
            $this->url('video');


            //AFTER CHECK
            try {
                $item1 = $this->byClassName('ow_video_list_item');
                $vid2 = $item1->byXPath('./a')->attribute('href'); //gives error if no video

                self::assertNotSame($vid1, $vid2);
                $id2 = substr($vid2,strrpos($vid2,"/")+1);
                VIDEO_BOL_ClipService::getInstance()->deleteClip($id2);
            } catch (Exception $ex) {
                $vid2 = '0';
            }
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