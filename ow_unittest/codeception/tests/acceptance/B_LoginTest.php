<?php

class B_LoginTest extends \Codeception\Test\Unit
{
    /**
     * @var \AcceptanceTester
     */
    protected $tester;

    /***
     * Successful Login
     */
    public function testSuccessfulLogin()
    {
        self::assertTrue($this->tester->loginAsAdmin());
        $this->tester->logout();
    }

    /***
     * Failed Login
     */
    public function testFailedLogin()
    {
        $username = $this->tester->getSiteInfo('site_admin_username');
        self::assertFalse($this->tester->login($username, 'alaki'));
    }
}