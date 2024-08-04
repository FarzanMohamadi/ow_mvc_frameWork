<?php
class chatsGroupsAndFriendsTest extends FRMTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $test_caption;
    private $userService;
    private $user1;
    private $users = array();
    private $groups = array();
    private $groupService;
    private $count = 16;

    protected function setUp()
    {
        parent::setUp();
        //fwrite(STDERR,'check plugin');
        $this->checkRequiredPlugins(array('frmmainpage', 'friends', 'groups'));
        $this->groupService = GROUPS_BOL_Service::getInstance();
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        ensure_session_active();
        FRMSecurityProvider::createUser($this->TEST_USER1_NAME, "user1@gmail.com", $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);

        //activate chatGroups and friends in main page
        FRMMAINPAGE_BOL_Service::getInstance()->removeFromDisableList("chatGroups");
        FRMMAINPAGE_BOL_Service::getInstance()->removeFromDisableList("friends");

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        /*create x user and group
        join groups and create friendship*/
        for ($usersAndGroupsCount = 0; $usersAndGroupsCount < $this->count; $usersAndGroupsCount++)
        {
            //create user
            $username = 'test_user' . $usersAndGroupsCount;
            $email = 'test_user' . $usersAndGroupsCount . '@gmail.com';
            FRMSecurityProvider::createUser($username, $email, $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
            $this->users[$usersAndGroupsCount] = BOL_UserService::getInstance()->findByUsername($username);

            // create group
            $groupData = array(
                'title' => 'test_group' . $usersAndGroupsCount,
                'description' => 'test',
                'whoCanInvite' => 'participant',
                'whoCanView' => 'anyone',
            );
            $this->groups[$usersAndGroupsCount] = $this->groupService->createGroup($this->users[$usersAndGroupsCount]->getId(), $groupData);

            if ($usersAndGroupsCount < $this->count - 2)
            {
                //create friendship
                $initiatorId = $this->user1->getId();
                $interlocutorId = $this->users[$usersAndGroupsCount]->getId();
                $friendsQuestionService->request($initiatorId, $interlocutorId);
                $friendsQuestionService->accept($interlocutorId, $initiatorId);

                //join user to groups and chat with friends
                if ($usersAndGroupsCount < 5) {
                    $this->groupService->addUser($this->groups[$usersAndGroupsCount]->getId(), $initiatorId);

                    $conversation = new MAILBOX_BOL_Conversation();
                    $conversation->initiatorId = $initiatorId;
                    $conversation->interlocutorId = $interlocutorId;
                    $conversation->subject =  'mailbox_chat_conversation';;
                    $conversation->createStamp = time();
                    $conversation->viewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;
                    MAILBOX_BOL_ConversationService::getInstance()->saveConversation($conversation);
                    MAILBOX_BOL_ConversationService::getInstance()->addMessage($conversation, $initiatorId, 'salam');
                }
            }
        }
    }

    public function testChatGroupsAndFriends()
    {
        $this->test_caption = "ChatGroupsTest";
        $this->webDriver->prepare();
        $this->setScreenSize(500, 800);

        $this->url(OW_URL_HOME . "mobile-version");
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        try {
            $this->mobile_sign_in($this->user1->getUsername(), $this->TEST_PASSWORD, true, $sessionId);

            //friend count
            $this->url(OW_URL_HOME . "frmmainpage/friends");
            $this->scrollDown();
            $friendCount = $this->webDriver->findElementsByCssSelector('*[class="owm_user_list_item"]');
            self::assertEquals($this->count - 2, count($friendCount));

            //chat groups count
            $this->url(OW_URL_HOME . "frmmainpage/chats-groups");
            $this->scrollDown();
            $chatsGroupsCount = $this->webDriver->findElementsByCssSelector('*[class="owm_list_item"]');
            self::assertEquals(10, count($chatsGroupsCount));
        }catch (Exception $ex) {
            $this->handleException($ex,$this->test_caption,true);
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        //delete users and groups
        for ($usersAndGroupsCount=0; $usersAndGroupsCount<$this->count; $usersAndGroupsCount++) {
            $this->groupService->deleteGroup($this->groups[$usersAndGroupsCount]->getId());
            FRMSecurityProvider::deleteUser($this->users[$usersAndGroupsCount]->getUsername());
        }
        //delete user1
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        parent::tearDown();
    }
}