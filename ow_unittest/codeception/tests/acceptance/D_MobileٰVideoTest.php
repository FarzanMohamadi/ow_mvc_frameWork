<?php
use \Codeception\Util\Locator;
class D_MobileVideoTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    private $user1;

    private $Aparat_CODE = '<style>.h_iframe-aparat_embed_frame{position:relative;} .h_iframe-aparat_embed_frame .ratio {display:block;width:100%;height:auto;} .h_iframe-aparat_embed_frame iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style><div class="h_iframe-aparat_embed_frame"> <span style="display: block;padding-top: 57%"></span><iframe src="https://www.aparat.com/video/video/embed/videohash/GjYCP/vt/frame" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" ></iframe></div>';
    private $Scenario1_TITLE = 'What a wonderful place!';
    private $Scenario2_TITLE = 'So cool!';

    protected function _before()
    {
        $this->user1 = $this->tester->registerUser();
    }

    /**
     *  SCENARIO 1
        User1 submits a video with aparat code
        User1 can see the video
     */
    public function testCreateVideoWithCode()
    {
        /**
         * go to mobile version
         */
        $this->tester->login($this->user1->username,$this->user1->password);
        $this->tester->amOnPage('/mobile-version');

        /**
         * go to video creation page
         */
        $this->tester->amOnPage('/m-video/add/index/');
        /**
         * fill fields and add new video
         */
        $this->tester->selectOption(['id'=>'input_type_code'],'code');
        $this->tester->fillField(['name'=>'code'],$this->Aparat_CODE);
        $this->tester->fillField(['name'=>'title'],$this->Scenario1_TITLE);
        $this->tester->click('افزودن');
        $this->tester->see($this->Scenario1_TITLE);
        /**
         * User1 goes to desktop version and sign-out
         */
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();
    }

    /**
        SCENARIO 2
        User1 submits a video with aparat code
        User1 can see the video
     */
    public function testCreateVideoWithUploading()
    {
        /**
         * go to mobile version
         */
        $this->tester->login($this->user1->username,$this->user1->password);
        $this->tester->amOnPage('/mobile-version');

        /**
         * go to video creation page
         */
        $this->tester->amOnPage('/m-video/add/index/');
        /**
         * fill fields and add new video
         */
        $this->tester->selectOption(['id'=>'input_type_upload'],'upload');
        $file_name = 'test.mp4';
        $this->tester->attachFile(['id'=>'videoUpload'],$file_name);
        $this->tester->fillField(['name'=>'code'],$this->Aparat_CODE);
        $this->tester->fillField(['name'=>'title'],$this->Scenario2_TITLE);
        $this->tester->click('افزودن');
        $this->tester->see($this->Scenario2_TITLE);
        /**
         * User1 goes to desktop version and sign-out
         */
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();
    }
}