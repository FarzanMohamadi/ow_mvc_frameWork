<?php
class frmuserloginTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('frmuserlogintest', 'frmuserlogintest@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
    }

    /**
     * Test of frmevaluation plugin
     */
    public function testLogin()
    {
        $service = FRMUSERLOGIN_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findByUsername('frmuserlogintest');
        $service->addLoginDetails($user->getId(), false);
        $userLoginDetails = $service->getUserLoginDetails($user->getId(), false);
        self::assertEquals(1, sizeof($userLoginDetails));

        $service->addLoginDetails($user->getId(), false);
        $userLoginDetails = $service->getUserLoginDetails($user->getId(), false);
        self::assertEquals(2, sizeof($userLoginDetails));

        $service->deleteUserLoginDetails($user->getId(), false);
        $userLoginDetails = $service->getUserLoginDetails($user->getId(), false);
        self::assertEquals(0, sizeof($userLoginDetails));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('frmuserlogintest');
    }
}