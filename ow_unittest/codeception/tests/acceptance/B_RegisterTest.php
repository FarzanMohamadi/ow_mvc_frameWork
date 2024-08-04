<?php

class B_RegisterTest extends \Codeception\Test\Unit
{
    /**
     * @var \AcceptanceTester
     */
    protected $tester;

    /***
     * Successful Register
     */
    public function testSuccessfulRegister()
    {
        $user = $this->tester->registerUser();
        self::assertTrue($this->tester->login($user->username, $user->password));
        self::assertTrue(str_contains($user->email, '@'));
        self::assertGreaterThan(1, $user->id);
        $this->tester->logout();
    }
}