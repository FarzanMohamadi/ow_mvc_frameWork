<?php
class frmevaluationTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('evaluationusertest', 'evaluation@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
    }

    /**
     * Test of frmevaluation plugin
     */
    public function testEvaluation()
    {

        $service = FRMEVALUATION_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findByUsername('evaluationusertest');

        //user must be in activated users.
        $service->assignUser($user->getId(), $user->username);
        $activeUsers = $service->getActiveUsers();
        $userExistInActivateUsers = false;
        foreach($activeUsers as $activeUser){
            if($activeUser->username == 'evaluationusertest'){
                $userExistInActivateUsers = true;
            }
        }
        self::assertEquals(true, $userExistInActivateUsers);

        //locked user
        $service->assignUser($user->getId(), $user->username, true);
        $activeUsers = $service->getActiveUsers();
        $userExistInActivateUsers = false;
        foreach($activeUsers as $activeUser){
            if($activeUser->username == 'evaluationusertest'){
                $userExistInActivateUsers = true;
            }
        }
        self::assertEquals(false, $userExistInActivateUsers);

        //user must be in locked users.
        $lockedUsers = $service->getLockedUsers();
        $userExistInLockedUsers = false;
        foreach($lockedUsers as $lockedUser){
            if($lockedUser->username == 'evaluationusertest'){
                $userExistInLockedUsers = true;
            }
        }
        self::assertEquals(true, $userExistInLockedUsers);

        //remove user and user must not be in locked users.
        $service->unassignUser('evaluationusertest');
        $lockedUsers = $service->getLockedUsers();
        $userExistInLockedUsers = false;
        foreach($lockedUsers as $lockedUser){
            if($lockedUser->username == 'evaluationusertest'){
                $userExistInLockedUsers = true;
            }
        }
        self::assertEquals(false, $userExistInLockedUsers);
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('evaluationusertest');
    }

}