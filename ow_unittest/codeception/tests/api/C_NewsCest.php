<?php

class C_NewsCest
{

    public function shareNewsPost(\ApiTester $I)
    {
        $user = $I->getAdminUser();
        $log = $I->login($user->username, $user->password);
        $token = $log['cookies']['ow_login'];
        $res = $I->shareNewsPost($token, 'Today weather', 'A good day!');
        $I->assertNotFalse($res);
    }

}
