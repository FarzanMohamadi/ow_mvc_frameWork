<?php

use Codeception\Util\HttpCode;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /***
     * @return object
     */
    public function getANormalUser($forceRegister=false){
        $path = OW_DIR_ROOT.'ow_unittest'.DS.'codeception'.DS.'tests'.DS.'_output'.DS.'normal_user.json';
        if(!file_exists($path) || $forceRegister) {
            $user = $this->registerUser();
            file_put_contents($path, json_encode($user));
        }
        else{
            $user = json_decode(file_get_contents($path));
        }
        return $user;
    }

    /***
     * @return object
     */
    public function getAdminUser(){
        $user = [];
        $user['username'] = $this->getSiteInfo('site_admin_username');
        $user['password'] = $this->getSiteInfo('site_admin_password');
        return (object) $user;
    }

    /***
     * @param $username
     * @param $pass
     * @return bool
     */
    public function login($username, $pass)
    {
        $this->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->sendPost('/mobile/services/action/login', [
            'username' =>  $username,
            'password' => $pass
        ]);

        $this->seeResponseCodeIs(200); // 200
        $this->seeResponseIsJson();
        $resp = $this->grabDataFromResponseByJsonPath('login')[0];
        if(!$resp['valid']){
            return false;
        }
        $this->seeResponseMatchesJsonType(
            ['login'=>
                [
                    "valid"=>'boolean',
                    'cookies' =>['ow_login' => 'string']
                ]
            ]
        );
        return $resp;
    }

    /**
     */
    public function loginAsAdmin(){
        $username = $this->getSiteInfo('site_admin_username');
        $pass = $this->getSiteInfo('site_admin_password');
        return $this->login($username, $pass);
    }

    /**
     */
    public function logout($token){
        $this->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->sendPost('/mobile/services/action/logout',[
            'access_token' => $token
        ]);

        $this->seeResponseCodeIs(200); // 200
        $this->seeResponseIsJson();
        $resp = $this->grabDataFromResponseByJsonPath('logout')[0];
        return $resp['valid'];
    }

    /**
     * Registers a new user
     * @param $username
     * @param $pass
     * @param $email
     * @return object
     */
    private function registerUser($username=null, $pass=null, $email=null){
        $r = rand(10000, 90000);
        if(empty($username)){
            $username = 'user_'.$r;
        }
        if(empty($pass)){
            $pass = 'pass_'.$r;
        }
        if(empty($email)){
            $email = 'email_'.$r.'@gmail.com';
        }

        $this->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->sendPost('/mobile/services/action/join', [
            'email' => $email,
            'username' =>  $username,
            'realname' => $username,
            'password' => $pass,
            'captchaField' => 'RRR',
            'birthdate' => '1356/12/11',
            'sex' => 'زن'
        ]);

        $this->seeResponseCodeIs(200); // 200
        $this->seeResponseIsJson();
        $resp = $this->grabDataFromResponseByJsonPath('join')[0];
        if(!$resp['valid']){
            return false;
        }
        $user = $resp['user'];
        return (object) ['id'=>$user['id'], 'username'=>$username, 'password'=>$pass, 'email'=>$email];
    }

    /***
     * @param $token
     * @param $text
     * @param string $feedType
     * @return mixed
     * @throws Exception
     */
    public function shareNewfeedPost($token, $text, $feedType='user'){
        $this->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->sendPost('/mobile/services/action/send_post',[
            'access_token' => $token,
            'text'=>$text,
            'feedType' => $feedType,
        ]);

        $this->seeResponseIsJson();
        $resp = $this->grabDataFromResponseByJsonPath('send_post')[0];
        if(!$resp['valid']){
            return false;
        }
        $this->seeResponseCodeIs(200); // 200
        return $resp['item'][0];
    }

    /***
     * @param $token
     * @param $text
     * @param string $feedType
     * @return mixed
     * @throws Exception
     */
    public function shareNewsPost($token, $title, $text){
        $this->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->sendPost('/mobile/services/action/add_news',[
            'access_token' => $token,
            'title' => $title,
            'entry' => $text,
        ]);

        $this->seeResponseIsJson();
        $resp = $this->grabDataFromResponseByJsonPath('add_news')[0];
        if(!$resp['valid']){
            return false;
        }
        $this->seeResponseCodeIs(200); // 200
        return $resp['news'];
    }
    private function friendshipRequest(ApiTester $I,$access_token_user,$targetUserFriendId ){
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/friend_request/?=&userId='.$targetUserFriendId , [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.friend_request')[0];
        return $resp['valid'];
    }
    private function friendshipAccept(ApiTester $I,$access_token_user,$userid){
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/accept_friend/?=&requesterId='.$userid, [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.accept_friend')[0];
        return $resp['valid'];
    }
    public function makeFriendship(ApiTester $I,$access_token_user1,$access_token_user2,$id_user1,$id_user2){
        $I->assertNotFalse($this->friendshipRequest($I,$access_token_user1,$id_user2));
        $I->assertNotFalse($this->friendshipAccept($I,$access_token_user2,$id_user1));
    }

}
