<?php
class frmsuggestfriendTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('frmsuggestfriend_user1', 'frmsuggestfriend_user1@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmsuggestfriend_user2', 'frmsuggestfriend_user2@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmsuggestfriend_user3', 'frmsuggestfriend_user3@test.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
    }

    /**
     * Test of frmsuggestfriend plugin
     */
    public function testSuggestFriend()
    {

        $user1 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user1');
        $user2 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user2');
        $user3 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user3');

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user2->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user2->getId(), $user1->getId());

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user3->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user3->getId(), $user1->getId());

        $suggestedFriendsToUser2 = FRMSUGGESTFRIEND_CLASS_Suggest::getInstance()->getSuggestedFriends($user2->getId());
        self::assertEquals(true, in_array($user3->getId(), $suggestedFriendsToUser2));

        $suggestedFriendsToUser3 = FRMSUGGESTFRIEND_CLASS_Suggest::getInstance()->getSuggestedFriends($user3->getId());
        self::assertEquals(true, in_array($user2->getId(), $suggestedFriendsToUser3));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user1');
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user2');
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user3');
    }
}