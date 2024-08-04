<?php

class B_LoginUserCest
{
    /***
     * Successful Login
     */
    public function testSuccessfulLogin(\ApiTester $I)
    {
        $user = $I->getANormalUser();
        $I->assertNotFalse($user);
        $I->assertNotFalse($I->login($user->username, $user->password));
    }

    /***
     * Failed Login
     */
    public function testFailedLogin(\ApiTester $I)
    {
        $user = $I->getANormalUser();
        $I->assertNotFalse($user);
        $I->assertFalse($I->login($user->username, 'alaki'));
    }

    /***
     * Successful Admin Login
     */
    public function testLoginAdminViaAPI(\ApiTester $I)
    {
        $I->assertNotFalse($I->loginAsAdmin());
    }
}
