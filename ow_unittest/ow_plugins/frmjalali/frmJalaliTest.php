<?php
class frmJalaliTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = 'asdf@1111';
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmjalali'));
        ensure_session_active();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);

    }

    public function testEventJalaliCalender()
    {
        $months = array	(OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_1'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_2')
        ,OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_3'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_4')
        ,OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_5'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_6')
        ,OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_7'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_8')
        ,OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_9'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_10')
        ,OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_11'),OW::getLanguage()->text('frmjalali', 'date_time_month_short_fa_12'));
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME . "dashboard");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user->getUsername(),$this->TEST_CORRECT_PASSWORD,true,true,$sessionId);
        srand(time());
        $number = rand();
        try{
            try{
                $this->waitUntilElementLoaded('byName','status');
                $this->url('events');
                $this->waitUntilElementLoaded('byCssSelector','.add_event_button');
                $this->byCssSelector('.add_event_button')->click();

                $this->waitUntilElementLoaded('byName','title');
                $this->webDriver->executeScript("var iframeText = document.getElementsByClassName(\"cke_wysiwyg_frame cke_reset\")[0]; if(iframeText!=null) { var conDocument = iframeText.contentDocument;conDocument.body.innerHTML = \"salam\";} else { var iframeText = document.getElementsByName(\"desc\")[0]; iframeText.style.display=\"block\"; iframeText.innerText=\"salam\";} document.getElementsByName('who_can_view')[0].checked =true;document.getElementsByName('who_can_invite')[0].checked =true;", array());
                $this->byName('title')->value('selenium event title'.$number);
                $this->byName('location')->value('selenium event location'.$number);
                $this->byName('submit')->submit();
                $this->waitUntilElementLoaded('byClassName', 'ow_comments_ipc');
                foreach ($months as $month) {
                    if (strpos($this->byCssSelector('.ow_value')->attribute('innerText'), $month) !== false) {
                        self::assertTrue(true);
                    }
                }
            }catch (Exception $ex){
                $this->handleException($ex,'',true,false);
            }

            self::assertTrue(true);

        }catch (Exception $ex){
            $this->handleException($ex,'',true,false);
        }
    }


    public function tearDown()
    {
        if($this->isSkipped)
            return;

        EVENT_BOL_EventService::getInstance()->deleteUserEvents($this->user->getId());
        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}