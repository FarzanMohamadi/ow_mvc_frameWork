<?php

class C_NewsfeedCest
{

    public function shareNewsfeedPost(\ApiTester $I)
    {
        $user = $I->getANormalUser();
        $log = $I->login($user->username, $user->password);
        $token = $log['cookies']['ow_login'];
        $res = $I->shareNewfeedPost($token, 'salam. sample 1.');
        $entityId = $res['entityId'];
        $I->assertNotFalse($res);
        $I->assertNotFalse($entityId);
        $I->logout($token);
    }

}
