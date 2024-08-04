<?php
class frmPasswordChangeIntervalTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test of validating and invalidating of user's password
     */
    public function testInvalidateAndValidateUsers()
    {
        //Invalidate all user's password
        $service = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
        $service->setAllUsersPasswordInvalid(false);
        $invalidUsers = $service->getAllUsersInvalid(null, false);
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
        self::assertEquals(count($invalidUsers), count($users));

        //Check non of users should be invalid
        foreach ($invalidUsers as $invalidUser)
        {
            $service->setUserPasswordValid($invalidUser->id);
        }
        $invalidUsers = $service->getAllUsersInvalid(null, false);
        self::assertEquals(count($invalidUsers), 0);

        //Check all users should be valid
        $validUsers = $service->getAllUsersValid(null, false);
        self::assertEquals(count($validUsers), count($users));

        $service->deleteAllUsersFromPasswordValidation();
    }
}