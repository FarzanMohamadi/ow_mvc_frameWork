<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 1/21/2018
 * Time: 1:10 PM
 */
class newsfeedPinTest extends FRMUnitTestUtilites
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
    private $group;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmnewsfeedpin'));

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

        $data = array(
            'title' => 'creatorCanInvite',
            'description' => 'test',
            'whoCanInvite' => 'creator',
            'whoCanView' => 'invite',
        );
        $this->group = GROUPS_BOL_Service::getInstance()->createGroup($this->user1->getId(), $data);

        $this->groupService->inviteUser($this->group->getId(),$this->user2->getId(),$this->user1->getId());
        $this->groupService->addUser($this->group->getId(),$this->user2->getId());
        OW_User::getInstance()->logout();
    }


    public function testCanEditGroup()
    {
        OW_User::getInstance()->login($this->user1->getId());
        //user1 can edit
        self::assertTrue(FRMNEWSFEEDPIN_BOL_Service::getInstance()->canEditGroup($this->group));
        OW_User::getInstance()->logout();

        OW_User::getInstance()->login($this->user2->getId());
        //user2 can edit
        self::assertFalse(FRMNEWSFEEDPIN_BOL_Service::getInstance()->canEditGroup($this->group));
        OW_User::getInstance()->logout();

        OW_User::getInstance()->login($this->user3->getId());
        //user3 can not edit
        self::assertFalse(FRMNEWSFEEDPIN_BOL_Service::getInstance()->canEditGroup($this->group));
        OW_User::getInstance()->logout();
    }

    public function testLoadNewItem(){
        $event = new OW_Event('test',array('pin'=>true,'entityId'=>1,'entityType'=>1));
        FRMNEWSFEEDPIN_BOL_Service::getInstance()->loadNewItem($event);
        $pin = FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->findByEntityIdAndEntityType(1,1);
        self::assertTrue(isset($pin));
    }

    protected function tearDown()
    {
        if($this->isSkipped)
            return;

        parent::tearDown();
        $this->groupService->deleteGroup($this->group->getId());
        FRMSecurityProvider::deleteUser(self::$USER_NAME_1);
        FRMSecurityProvider::deleteUser(self::$USER_NAME_2);
        FRMSecurityProvider::deleteUser(self::$USER_NAME_3);

    }


}