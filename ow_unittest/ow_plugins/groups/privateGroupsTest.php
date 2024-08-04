<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class privateGroupsTest extends FRMUnitTestUtilites
{
    private static $USER_NAME_1 = 'user_private_group_1';
    private static $EMAIL_1 = 'user_private_group_1@gmail.com';
    private static $PASSWORD_1 = 'password123';
    private static $USER_NAME_2 = 'user_private_group_2';
    private static $EMAIL_2 = 'user_private_group_2@gmail.com';
    private static $PASSWORD_2 = 'password123';

    /**
     * @var GROUPS_BOL_Service
     */
    private $groupService;
    /**
     * @var BOL_User
     */
    private $user1;
    /**
     * @var BOL_User
     */
    private $user2;
    /**
     * @var GROUPS_BOL_Group
     */
    private $privateGroup;

    protected function setUp()
    {
        parent::setUp();

        $this->groupService = GROUPS_BOL_Service::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser(self::$USER_NAME_1, self::$EMAIL_1, self::$PASSWORD_1, "1987/3/21", "1", $accountType);
        $this->user1 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_1);

        FRMSecurityProvider::createUser(self::$USER_NAME_2, self::$EMAIL_2, self::$PASSWORD_2, "1987/3/21", "1", $accountType);
        $this->user2 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_2);

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        ensure_session_active();
        OW_User::getInstance()->login($this->user1->getId());
        $data2 = array(
            'title' => 'test',
            'description' => 'test',
            'whoCanInvite' => 'creator',
            'whoCanView' => 'invite',
        );
        $this->privateGroup = GROUPS_BOL_Service::getInstance()->createGroup($this->user1->getId(), $data2);
        OW_User::getInstance()->logout();
    }


    public function testViewPrivateGroup()
    {
        OW_User::getInstance()->login($this->user2->getId());
        //user1 should not view the private group because user is not the creator or a member
        self::assertFalse($this->groupService->isCurrentUserCanView($this->privateGroup));
        OW_User::getInstance()->logout();

        OW_User::getInstance()->login($this->user1->getId());
        //user1 can view the private group because user is the group creator
        self::assertTrue($this->groupService->isCurrentUserCanView($this->privateGroup));
        OW_User::getInstance()->logout();
    }

    public function testViewPrivateGroupPost()
    {
        OW_User::getInstance()->login($this->user1->getId());
        NEWSFEED_BOL_Service::getInstance()->addStatus($this->user1->getId(), 'groups', $this->privateGroup->getId(), 14, 'A text in a private group', ['content' => [], 'attachmentId' => null]);

        $actionFeeds = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByFeed('groups', $this->privateGroup->getId());
        // there should be two feeds: group create feed + my status
        self::assertEquals(count($actionFeeds), 2);

        $activityIdForMyStatus = $actionFeeds[1]->activityId;
        $actionFeedsForMyStatus = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds([$activityIdForMyStatus]);
        // there should be only one feed: no user feed
        self::assertEquals(count($actionFeedsForMyStatus), 1);

        OW_User::getInstance()->logout();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->groupService->deleteGroup($this->privateGroup->getId());
        FRMSecurityProvider::deleteUser(self::$USER_NAME_1);
        FRMSecurityProvider::deleteUser(self::$USER_NAME_2);
    }

}