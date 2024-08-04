<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 5/22/2019
 * Time: 7:11 AM
 */

class groupOwnerChangeTest extends FRMUnitTestUtilites
{
    private static $TEST_USER1_NAME = "test_user1";
    private static $TEST_USER2_NAME = "test_user2";
    private static $TEST_USER1_EMAIL = "user_1@gmail.com";
    private static $TEST_USER2_EMAIL = "user_2@gmail.com";
    private static $TEST_PASSWORD = 'TestUser12345';

    /**
     * @var BOL_UserService
     */
    private $userService;

    /**
     * @var BOL_User
     */
    private $user1,$user2;

    /**
     * @var GROUPS_BOL_Group
     */
    private $testGroup;

    /**
     * @var GROUPS_BOL_Service
     */
    private $groupService;


    protected function setUp()
    {
        parent::setUp();
        $this->groupService = GROUPS_BOL_Service::getInstance();
        $this->userService =  BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        FRMSecurityProvider::createUser(self::$TEST_USER1_NAME, self::$TEST_USER1_EMAIL, self::$TEST_PASSWORD, "1987/3/21", "1", $accountType);
        $this->user1 = $this->userService->findByUsername(self::$TEST_USER1_NAME);

        FRMSecurityProvider::createUser(self::$TEST_USER2_NAME, self::$TEST_USER2_EMAIL, self::$TEST_PASSWORD, "1987/3/21", "1", $accountType);
        $this->user2 = $this->userService->findByUsername(self::$TEST_USER2_NAME);

        OW_User::getInstance()->login($this->user1->getId(),false);
        $groupData = array(
            'title' => 'test',
            'description' => 'test',
            'whoCanInvite' => 'participant',
            'whoCanView' => 'anyone',
        );

        $this->testGroup = $this->groupService->createGroup($this->user1->getId(), $groupData);
        $this->groupService->addUser($this->testGroup->getId(),$this->user2->getId());
        OW_User::getInstance()->logout();
    }

    public function testDeleteUserAfterGroupOwnerChange()
    {
        OW_User::getInstance()->login($this->user1->getId(),false);
        FRMGROUPSPLUS_BOL_Service::getInstance()->addUserAsManager($this->testGroup->getId(), $this->user2->getId());

        $groupManagersDao = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance();
        $groupManager = $groupManagersDao->getGroupManagerByUidAndGid($this->testGroup->getId(), $this->user2->getId());
        self::assertTrue(isset($groupManager));

        $this->groupService->setGroupOwner($this->testGroup->getId(),$this->user2->getId());
        OW_User::getInstance()->logout();

        $this->groupService->deleteUser($this->testGroup->getId(),array($this->user1->getId()));
        FRMSecurityProvider::deleteUser(self::$TEST_USER1_NAME);
        $group = $this->groupService->findGroupById($this->testGroup->getId());
        self::assertTrue(isset($group));

    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->groupService->deleteGroup($this->testGroup->getId());
        FRMSecurityProvider::deleteUser(self::$TEST_USER2_NAME);
    }


}