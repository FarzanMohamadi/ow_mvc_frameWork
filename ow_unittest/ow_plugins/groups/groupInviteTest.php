<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/11/2017
 * Time: 2:50 PM
 */
class groupInviteTest extends FRMUnitTestUtilites
{
    private static $USER_NAME_1 = 'user_group_1';
    private static $EMAIL_1 = 'user_group_1@gmail.com';
    private static $PASSWORD_1 = 'password123';
    private static $USER_NAME_2 = 'user_group_2';
    private static $EMAIL_2 = 'user_group_2@gmail.com';
    private static $PASSWORD_2 = 'password123';
    private static $USER_NAME_3 = 'user_group_3';
    private static $EMAIL_3 = 'user_group_3@gmail.com';
    private static $PASSWORD_3 = 'password123';

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
     * @var BOL_User
     */
    private $user3;
    /**
     * @var GROUPS_BOL_Group
     */
    private $creatorCanInviteGroup;
    /**
     * @var GROUPS_BOL_Group
     */
    private $participantsCanInviteGroup;

    protected function setUp()
    {
        parent::setUp();

        $this->groupService = GROUPS_BOL_Service::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser(self::$USER_NAME_1, self::$EMAIL_1, self::$PASSWORD_1, "1987/3/21", "1", $accountType);
        $this->user1 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_1);

        FRMSecurityProvider::createUser(self::$USER_NAME_2, self::$EMAIL_2, self::$PASSWORD_2, "1987/3/21", "1", $accountType);
        $this->user2 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_2);

        FRMSecurityProvider::createUser(self::$USER_NAME_3, self::$EMAIL_3, self::$PASSWORD_3, "1987/3/21", "1", $accountType);
        $this->user3 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_3);

        ensure_session_active();
        OW_User::getInstance()->login($this->user1->getId());
        $data1 = array(
            'title' => 'creatorCanInvite',
            'description' => 'test',
            'whoCanInvite' => 'creator',
            'whoCanView' => 'invite',
        );
        $this->creatorCanInviteGroup = GROUPS_BOL_Service::getInstance()->createGroup($this->user1->getId(), $data1);

        $data2 = array(
            'title' => 'participantsCanInvite',
            'description' => 'test',
            'whoCanInvite' => 'participant',
            'whoCanView' => 'invite',
        );
        $this->participantsCanInviteGroup = GROUPS_BOL_Service::getInstance()->createGroup($this->user1->getId(), $data2);

        $this->groupService->inviteUser($this->creatorCanInviteGroup->getId(),$this->user2->getId(),$this->user1->getId());
        $this->groupService->inviteUser($this->participantsCanInviteGroup->getId(),$this->user2->getId(),$this->user1->getId());
        $this->groupService->addUser($this->creatorCanInviteGroup->getId(),$this->user2->getId());
        $this->groupService->addUser($this->participantsCanInviteGroup->getId(),$this->user2->getId());
        OW_User::getInstance()->logout();
    }


    public function test()
    {
        OW_User::getInstance()->login($this->user1->getId());
        //user1 can invite because he/she is the creator
        self::assertTrue($this->groupService->isCurrentUserInvite($this->creatorCanInviteGroup->getId()));
        //user1 can be invited because he/she is the creator
        self::assertTrue($this->groupService->isCurrentUserInvite($this->participantsCanInviteGroup->getId()));
        OW_User::getInstance()->logout();

        OW_User::getInstance()->login($this->user2->getId());
        //user2 can not invite because he/she is not the creator
        self::assertFalse($this->groupService->isCurrentUserInvite($this->creatorCanInviteGroup->getId()));
        //user2 can invite because he/she is a group participant
        self::assertTrue($this->groupService->isCurrentUserInvite($this->participantsCanInviteGroup->getId()));
        OW_User::getInstance()->logout();

        OW_User::getInstance()->login($this->user3->getId());
        //user2 can not invite because he/she is not the creator
        self::assertFalse($this->groupService->isCurrentUserInvite($this->creatorCanInviteGroup->getId()));
        //user2 can not invite because he/she is not a group participant
        self::assertFalse($this->groupService->isCurrentUserInvite($this->participantsCanInviteGroup->getId()));
        OW_User::getInstance()->logout();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->groupService->deleteGroup($this->creatorCanInviteGroup->getId());
        $this->groupService->deleteGroup($this->participantsCanInviteGroup->getId());
        FRMSecurityProvider::deleteUser(self::$USER_NAME_1);
        FRMSecurityProvider::deleteUser(self::$USER_NAME_2);
        FRMSecurityProvider::deleteUser(self::$USER_NAME_3);

    }


}