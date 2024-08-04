<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class mNewsTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2;

    private $entry1;
    private $entry1_title = "Mobile Test FRMNEWS I";

    public function createNews($userID, $title,$text,$tags=array())
    {
        OW::getCacheManager()->clean( array( EntryDao::CACHE_TAG_POST_COUNT ));
        $service = EntryService::getInstance();

        $entry = new Entry();
        $entry->setAuthorId($userID);
        $entry->setTitle($title);
        $entry->setEntry($text);
        $entry->setIsDraft(0);
        $entry->setPrivacy('everybody');
        $entry->setTimestamp(time());

        $service->save($entry);

        //tags
        $tagService = BOL_TagService::getInstance();
        $tagService->updateEntityTags($entry->getId(), 'news-entry', $tags );

        //Newsfeed
        $tagService->setEntityStatus('news-entry', $entry->getId(), true);
        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'frmnews',
            'entityType' => 'news-entry',
            'entityId' => $entry->getId(),
            'userId' => $userID,
        ));
        OW::getEventManager()->trigger($event);
        OW::getEventManager()->trigger(new OW_Event(EntryService::EVENT_AFTER_ADD, array(
            'entryId' => $entry->getId()
        )));
        return $entry;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmnews', 'friends'));
        $this->checkIfMobileIsActive();
        ensure_session_active();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        // set some info to users

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        OW::getUser()->login($this->user1->getId());
        $this->entry1 = $this->createNews($this->user1->getId(), $this->entry1_title,'<p>Some Text!</p>');
        OW::getUser()->logout();
    }

    public function testScenario1()
    {
        //self::markTestSkipped('must be rewritten');
        //----SCENARIO 1 - Seminar
        //User1 publishes news.
        //Guest clicks the news title from news list
        //Guest can read, can't comment
        //User2 clicks the news title from news list
        //User2 can read. comments on the news

        $test_caption = "mIisnewsTest-testScenario1";
        ///$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME . "mobile-version");

        //--------GUEST
        try {
            $this->url('news');
            //find title in the list
            $this->waitUntilElementLoaded('byClassName','owm_list_item_view_title');
            self::assertTrue($this->checkIfXPathExists('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]'));
            $this->byXPath('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]')
                ->click();

            //check if title is the same
            self::assertTrue($this->checkIfXPathExists('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]',
                DEFAULT_TIMEOUT_MILLIS));
            //check if can comment
            self::assertFalse($this->checkIfXPathExists('//textarea[@name="commentText"]'));
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }

        //----------USER2
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        try {
            $this->mobile_sign_in($this->user2->getUsername(),$this->TEST_PASSWORD, true, $sessionId);
            $this->waitUntilElementDisplayed('byCssSelector', '.owm_msg_block.owm_msg_info');

            $this->url('news');
            //find title in the list
            $this->waitUntilElementLoaded('byClassName','owm_list_item_view_title');
            self::assertTrue($this->checkIfXPathExists('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]'));
            $this->byXPath('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]')->click();

            //check if title is the same
            self::assertTrue($this->checkIfXPathExists('//span[contains(@class,"owm_list_item_view_title") and contains(text(),"'.$this->entry1_title.'")]',
                DEFAULT_TIMEOUT_MILLIS));
            //check if can comment
            self::assertTrue($this->checkIfXPathExists('//textarea[@name="commentText"]'));
        } catch (Exception $ex) {
            $this->handleException($ex,$test_caption,true);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete news
        EntryService::getInstance()->deleteEntry($this->entry1->getId());

        //delete users
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        parent::tearDown();
    }
}