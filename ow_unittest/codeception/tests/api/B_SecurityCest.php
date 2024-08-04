<?php

class B_SecurityCest
{
    public function testToken(\ApiTester $I)
    {
        $user = $I->getANormalUser();

        $log = $I->login($user->username, $user->password);
        $token = $log['cookies']['ow_login'];
        $res = $I->shareNewfeedPost($token, 'salam. sample 1.');
        $I->assertNotFalse($res);
        $I->logout($token);

        $res = $I->shareNewfeedPost($token, 'salam. sample 1.');
        $I->assertFalse($res);
    }

    public function testEmptyToken(\ApiTester $I)
    {
        $res = $I->shareNewfeedPost('', 'salam. sample 1.');
        $I->assertFalse($res);
    }

    /***
     * Try to share a news post using a normal user
     *
     * @param ApiTester $I
     * @throws Exception
     */
    public function testUnauthorizedAction(\ApiTester $I)
    {
        $user = $I->getANormalUser();
        $log = $I->login($user->username, $user->password);
        $token = $log['cookies']['ow_login'];
        $res = $I->shareNewsPost($token, 'Today weather', 'A good day!');
        $I->assertFalse($res);
    }

}
