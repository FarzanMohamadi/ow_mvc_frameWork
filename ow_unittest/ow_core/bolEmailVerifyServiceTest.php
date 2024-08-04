<?php
class BolEmailVerifyServiceTest extends FRMUnitTestUtilites
{
    private $TEST_USER_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $TEST_EMAIL = "user1@gmail.com";
    private $userService;
    private $emailVerifyService;
    private $user;
    protected function setUp()
    {
        parent::setUp();
        $this->userService = BOL_UserService::getInstance();
        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER_NAME, $this->TEST_EMAIL, $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user =  $this->userService->findByUsername($this->TEST_USER_NAME);
    }

    public function testBolEmailVerifyService()
    {
        //send mail to user
        $this->emailVerifyService->sendUserVerificationMail($this->user);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        self::assertNotEquals(null,$emailVerifyData);

        //delete by Id
        $this->emailVerifyService->deleteById($emailVerifyData->id);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        self::assertNull($emailVerifyData);

        //delete by userId
        $this->emailVerifyService->sendUserVerificationMail($this->user);
        $this->emailVerifyService->deleteByUserId($this->user->getId());
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        self::assertNull($emailVerifyData);

        //delete by createdStamp
        $this->emailVerifyService->sendUserVerificationMail($this->user);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        $this->emailVerifyService->deleteByCreatedStamp($emailVerifyData->createStamp);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        self::assertNull($emailVerifyData);

        //verify email
        $this->emailVerifyService->sendUserVerificationMail($this->user);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        $this->emailVerifyService->verifyEmailCode($emailVerifyData->hash);
        $emailVerifyData = $this->emailVerifyService->findByEmailAndUserId($this->TEST_EMAIL, $this->user->getId(), 'user');
        self::assertNull($emailVerifyData);
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser($this->user->getUsername());
    }

}