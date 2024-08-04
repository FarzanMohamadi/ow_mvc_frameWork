<?php

class A_RegisterUserCest
{
    /***
     * Successful Register and Login
     */
    public function testSuccessfulLogin(\ApiTester $I)
    {
        $user = $I->getANormalUser(true);
        $I->assertNotFalse($user);
        $I->assertNotFalse($I->login($user->username, $user->password));
    }

}
