<?php
class frmimportcontactTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('frmsuggestfriend_user1', 'frmsuggestfriend_user1@gmail.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmsuggestfriend_user2', 'frmsuggestfriend_user2@gmail.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmsuggestfriend_user3', 'frmsuggestfriend_user3@gmail.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
        FRMSecurityProvider::createUser('frmsuggestfriend_user4', 'frmsuggestfriend_user4@gmail.com', '12345678', '1987/3/21', '1',$accountType,'c0de');
    }

    /**
     * Test of frmimport plugin
     */
    public function testFriendsList()
    {
        $user1 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user1');
        $user2 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user2');
        $user3 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user3');
        $user4 = BOL_UserService::getInstance()->findByUsername('frmsuggestfriend_user4');

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user2->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user2->getId(), $user1->getId());

        FRIENDS_BOL_Service::getInstance()->request($user1->getId(), $user3->getId());
        FRIENDS_BOL_Service::getInstance()->accept($user3->getId(), $user1->getId());
        $service = FRMIMPORT_BOL_Service::getInstance();
        $service->addUser($user1->getId(),  $user2->email, 'google');
        $service->addUser($user1->getId(),  $user3->email, 'google');
        $service->addUser($user1->getId(),  $user4->email, 'google');
        $emails = $service->getEmailsByUserId($user1->getId(), 'google');
        $emailsInformation = $service->getRegisteredExceptFriendEmails($emails,$user1->getId());
        $suggestedEmails = array();
        foreach ($emailsInformation as $emailInformation) {
            $suggestedEmails[] = $emailInformation['email'];
        }
        self::assertEquals(true, in_array($user4->email, $suggestedEmails));
        self::assertEquals(true, !in_array($user3->email, $suggestedEmails));
        self::assertEquals(true, !in_array($user2->email, $suggestedEmails));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user1');
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user2');
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user3');
        FRMSecurityProvider::deleteUser('frmsuggestfriend_user4');
    }
}