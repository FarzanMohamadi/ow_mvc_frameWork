<?php


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
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;


    /***
     * @param $uri
     * @param array $params
     * @return string
     */
    public function sendAjax($uri, $params = []) {
        $this->sendAjaxPostRequest($uri, $params);
        return $this->grabPageSource();
    }

    /***
     * @param $username
     * @param $pass
     * @return bool
     */
    public function login($username, $pass)
    {
        $this->amOnPage('/sign-in');
        $this->click('ورود');
        $this->fillField('identity', $username);
        $this->fillField('password', $pass);

        try {
            $this->fillField('captchaField', 111);
        } catch (Exception $e) {
        }

        $this->click('submit');

        try {
            $this->see('داشبورد');
            return true;
        }catch (Exception $ex){
            return false;
        }
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
    public function logout(){
        $this->click(['class' => 'console_my_profile_no_avatar']);
        $this->click('خروج');
    }

    /**
     * Registers a new user
     * @param $username
     * @param $pass
     * @param $email
     * @return object
     */
    public function registerUser($username=null, $pass=null, $email=null){
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

        $this->loginAsAdmin();
        $resTxt = $this->sendAjax('/admin/users/add-user-responder/',
            ['username'=>$username, 'password'=>$pass, 'email'=>$email]);
        $this->logout();

        $res = json_decode($resTxt, true);
        if (!isset($res['message']) || !str_contains($res['message'], 'کاربر با موفقیت اضافه شد')) {
            print('Could not create a user: '. $resTxt);
            return false;
        }
        return (object) ['id'=>$res['user_id'], 'username'=>$username, 'password'=>$pass, 'email'=>$email];
    }

    /**
     * @param $cssOrXpath
     */
    public function callOnClickHrefLink($cssOrXpath)
    {
        $onClickAttr = $this->grabAttributeFrom($cssOrXpath,'onclick');
        preg_match_all('#\bhttp?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $onClickAttr, $match);
        $relativeUrl = "/".str_replace(OW_URL_HOME,"",$match[0][0]);
        $this->amOnPage($relativeUrl);
    }

    public function makeFriendship($user1,$user2)
    {
        /**
         * User1 sends friend request
         */
        $this->login($user1->username,$user1->password);
        $this->amOnPage("/user/". $user2->username);
        $this->see('افزودن به دوستان');
        $this->callOnClickHrefLink('//a[contains(@id,"friendship")]');
        $this->see('درخواست افزودن دوست فرستاده شد');
        $this->logout();

        /**
         * User2 accepts friend request of user1
         */
        $this->login($user2->username,$user2->password);
        $this->amOnPage('/user/'.$user1->username);
        $this->see('تایید درخواست افزودن دوست');
        $this->callOnClickHrefLink('//a[contains(@id,"friendship")]');
        $this->amOnPage('/user/'.$user1->username);
        $this->see("حذف از دوستان");
        $this->logout();
    }

}
