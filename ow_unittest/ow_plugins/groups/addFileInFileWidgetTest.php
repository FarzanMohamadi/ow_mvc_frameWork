<?php
class addFileInFileWidgetTest extends FRMUnitTestUtilites
{
    private static $USER_NAME = 'user_private_group';
    private $groupService;
    private $user;
    private $privateGroup;
    protected function setUp()
    {
        $this->checkRequiredPlugins(array('frmgroupsplus'));
        parent::setUp();
        $this->groupService = GROUPS_BOL_Service::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser(self::$USER_NAME, 'user_private_group@gmail.com', 'password123', "1987/3/21", "1", $accountType);
    }
    public function testProfileFeed()
    {
        $this->user = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME);
        OW_User::getInstance()->login($this->user->getId(), false);

        $groupData = array(
            'title' => 'test',
            'description' => 'test',
            'whoCanInvite' => 'creator',
            'whoCanView' => 'invite',
        );
        $this->privateGroup = GROUPS_BOL_Service::getInstance()->createGroup($this->user->getId(), $groupData);

        $file = array('name' => 'test.png',
            'type'=>'image/png',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'php'),
            'error' => 0,
            'size'=>31344);
        FRMGROUPSPLUS_BOL_Service::getInstance()->manageAddFile($this->privateGroup->getId(), $file);

        $newsfeedService = NEWSFEED_BOL_Service::getInstance();
        $actions = $newsfeedService->findActionsByUserId($this->user->getId());
        $actionIds=array();
        foreach ( $actions as $action ) {
            if($action->entityType == "group" || $action->entityType=="groups-add-file")
                $actionIds[] = $action->id;
        }
        $activityIds = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds($actionIds);
        $activityIds_user = array();
        foreach ($activityIds as $activityId)
        {
            $activity=$newsfeedService->findActivity($activityId)[0];
            if($activity->activityType == "create")
                $activityIds_user[] = $activityId;
        }
        $newsfeedActivity = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds($activityIds_user);
        foreach ($newsfeedActivity as $act)
        {
           self::assertTrue( $act->feedType != "user");
        }
        OW_User::getInstance()->logout();
    }

    protected function tearDown()
    {
        parent::tearDown();
        //delete group
        $this->groupService->deleteGroup($this->privateGroup->getId());
        //delete user
        FRMSecurityProvider::deleteUser(self::$USER_NAME);

    }

}