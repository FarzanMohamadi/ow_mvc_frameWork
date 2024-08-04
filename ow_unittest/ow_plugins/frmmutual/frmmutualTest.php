<?php
class frmmutualTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('frmmutual_user1', 'frmmutual_user1@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmmutual_user2', 'frmmutual_user2@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmmutual_user3', 'frmmutual_user3@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
    }

    /**
     * Test of frmmutual plugin
     */
    public function testMutual()
    {

        $user1 = BOL_UserService::getInstance()->findByUsername('frmmutual_user1');
        $user2 = BOL_UserService::getInstance()->findByUsername('frmmutual_user2');
        $user3 = BOL_UserService::getInstance()->findByUsername('frmmutual_user3');

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user2->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user2->getId(), $user1->getId());

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user3->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user3->getId(), $user1->getId());

        $mutuals = FRMMUTUAL_CLASS_Mutual::getInstance()->getMutualFriends($user2->getId(), $user3->getId())['mutualFriensdId'];
        self::assertEquals(true, in_array($user1->getId(), $mutuals));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('frmmutual_user1');
        FRMSecurityProvider::deleteUser('frmmutual_user2');
        FRMSecurityProvider::deleteUser('frmmutual_user3');
    }
}