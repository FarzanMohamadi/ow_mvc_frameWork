<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/06/01
 */

class frmnewsTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2,$user3;

    private $entry1;
    private $entry1_title = "title1";

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
        $this->checkRequiredPlugins(array('frmnews','friends'));
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        FRMSecurityProvider::createUser($this->TEST_USER3_NAME,"user3@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        // set some info to users

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        ensure_session_active();
        OW::getUser()->login($this->user1->getId());
        $this->entry1 = $this->createNews($this->user1->getId(), $this->entry1_title,'<p>Some Text!</p>');
        OW::getUser()->logout();
    }

    public function testNews1()
    {
        //self::markTestSkipped('must be rewritten');
        //----SCENARIO 1 - Seminar
        //admin publishes news.
        //guest can read, can't comment
        //User2 can read. comments on the news

        $test_caption = "frmnewsTest-testNews1";
        //$this->echoText($test_caption);
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        //--------GUEST
        $this->url(OW_URL_HOME . 'news/'.$this->entry1->id);
        $this->hide_element('demo-nav');
        //check if title is the same
        $res = $this->checkIfXPathExists('//*[contains(text(),"'.$this->entry1_title.'")]');
        self::assertTrue($res);
        //check if can comment
        $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
        self::assertTrue(!$res);


        //----------USER2
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);
        $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
        try {
            $this->url(OW_URL_HOME . 'news/'.$this->entry1->id);
            $this->hide_element('demo-nav');

            //check if title is the same
            $res = $this->checkIfXPathExists('//*[contains(text(),"'.$this->entry1_title.'")]');
            self::assertTrue($res);

            //check if can comment
            $res = $this->checkIfXPathExists('//*[contains(@class,"ow_comments_input")]');
            self::assertTrue($res);
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
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
        parent::tearDown();
    }
}