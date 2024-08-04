<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/07/10
 */

class linkCheckingTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1;

    private $questionService;


    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array());
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        if($this->userService->isExistUserName($this->TEST_USER1_NAME)) { // delete if already exists
            $this->user1 = $this->userService->findByUsername($this->TEST_USER1_NAME);
            $this->userService->deleteUser($this->user1->getId());
        }
        $this->user1 = $this->userService->createUser($this->TEST_USER1_NAME, $this->TEST_PASSWORD, "user1@gmail.com", null, true);
        // set some info to users
        $this->questionService = BOL_QuestionService::getInstance();
        $data = array();
        $data['username'] = $this->user1->getUsername();
        $data['email'] = $this->user1->getEmail();
        $data['realname'] = $this->user1->getUsername();
        $data['sex'] = "1";
        $data['birthdate'] = "1987/3/21";
        $this->questionService->saveQuestionsData($data, $this->user1->getId());
    }

    public function checkAllLinksInPage($start_url){
        $error_count = 0;
        $selenium_error_count = 0;
        $total_count = 0;
        $this->url($start_url);
        $i = 0;
        while(true){
            $i++;
            $p = '(//a[@href])['.$i.']';
            try{
                $tag = $this->byXPath($p);
                if(!$tag->displayed())
                    continue;
                $href = $tag->attribute("href");
            }
            catch(Exception $ex){
                break;
            }
            if(strpos($href,"http")===0 && strpos($href, OW_URL_HOME)===false) {// check only internal links
                continue;
            }

            $selenium_error = false;
            try {
                $tag->click();
            }catch(Exception $ex){
                $selenium_error = true;
                $selenium_error_count++;
                $this->echoText("SELENIUM ERROR:".$href);
            }

            if(!$selenium_error) {
                if($this->checkIfXPathExists('//body[contains(@class, "base_page404")]')) {
                    $this->echoText("ERROR404:".$href);
                    $error_count++;
                }else if($this->checkIfXPathExists('//*[text()[contains(.,"Error 500")]]')) {
                    $this->echoText("ERROR500:".$href);
                    $error_count++;
                }
            }
            $total_count++;
            $this->url($start_url);
        }
        if($error_count!==0)
            $this->echoText("ERRORS FOR $start_url: $error_count(+$selenium_error_count selenium errors) of $total_count urls.");
        return ($error_count==0);
    }
    public function checkAllLinks($start_url, $checkLoggedOut)
    {
        //----------USER LOGGED OUT
        $ret1 = true;
        if($checkLoggedOut)
            $ret1 = $this->checkAllLinksInPage($start_url);
        //----------USER LOGGED IN
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        $ret2 = $this->checkAllLinksInPage($start_url);
        $this->signOutDesktop();

        return ($ret1&&$ret2);
    }

    public function testLinksHomepage()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $start_url = OW_URL_HOME ."";
        $ret = $this->checkAllLinks($start_url, true);
        self::assertTrue($ret);
    }

    public function testLinksDashboard()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $start_url = OW_URL_HOME ."dashboard";
        $ret = $this->checkAllLinks($start_url, false);
        self::assertTrue($ret);
    }
    public function testLinksUsers()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $start_url = OW_URL_HOME ."users";
        $ret = $this->checkAllLinks($start_url, false);//true
        self::assertTrue($ret);
    }
    public function testLinksPhoto()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $start_url = OW_URL_HOME ."photo/viewlist/latest";
        $ret = $this->checkAllLinks($start_url, true);
        self::assertTrue($ret);
    }
    public function testLinksRules()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $start_url = OW_URL_HOME ."rules";
        $ret = $this->checkAllLinks($start_url, false);//true
        self::assertTrue($ret);
    }

    /*
    public function testLinksForum()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."forum";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, true);
        self::assertTrue($ret);
    }
    public function testLinksGroups()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."groups";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, true);
        self::assertTrue($ret);
    }
     public function testLinksEvents()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."events";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, true);
        self::assertTrue($ret);
    }
    public function testLinksBlogs()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."blogs";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, true);
        self::assertTrue($ret);
    }
    public function testLinksNews()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."news";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, true);
        self::assertTrue($ret);
    }
    public function testLinksVideo()
    {
        $this->url(OW_URL_HOME); //required for sessions
        $CURRENT_SESSIONS = $this->prepareSession();
        $CURRENT_SESSIONS->currentWindow()->maximize();
        $start_url = OW_URL_HOME ."video";
        $ret = $this->checkAllLinks($start_url, $CURRENT_SESSIONS, false);//true
        self::assertTrue($ret);
    }
*/

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        parent::tearDown();
    }
}