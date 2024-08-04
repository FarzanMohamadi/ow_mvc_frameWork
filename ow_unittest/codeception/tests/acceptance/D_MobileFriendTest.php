<?php
use \Codeception\Util\Locator;
class D_MobileFriendTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    private $user1,$user2;

    protected function _before()
    {
        $this->user1 = $this->tester->registerUser();
        $this->user2 = $this->tester->registerUser();
    }

    /**
     SCENARIO 1
     User1 goes to User2 page
     User1 sends request
     User2 accepts request
     */
    public function testAcceptFriendship()
    {
        $this->tester->amOnPage('/desktop-version');
        /**
         * User1 sends friend request
         */
        $this->tester->login($this->user1->username,$this->user1->password);
        $this->tester->amOnPage('/mobile-version');
        $this->tester->amOnPage("/user/". $this->user2->username);
        $this->tester->see('افزودن به دوستان');
        $this->tester->callOnClickHrefLink('//a[contains(@id,"friendship")]');
        $this->tester->see('حذف درخواست افزودن دوست');
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();

        /**
         * User2 accepts friend request of user1
         */
        $this->tester->login($this->user2->username,$this->user2->password);
        $this->tester->amOnPage('/mobile-version');
        $this->tester->amOnPage('/main/notifications');
        $this->tester->see('برای شما درخواست دوستی ارسال کرده است');
        $dataCodeAttr = $this->tester->grabAttributeFrom("//*[contains(@class, 'owm_lbutton owm_friend_request_accept')]",'data-code');
        $dataRidAttr = $this->tester->grabAttributeFrom("//*[contains(@class, 'owm_lbutton owm_friend_request_accept')]",'data-rid');
        $this->tester->sendAjax('/m-friends/action/accept-ajax/',
            ['id'=>$dataRidAttr, 'code'=>$dataCodeAttr]);
        $this->tester->amOnPage("/user/". $this->user1->username);
        $this->tester->see("حذف از دوستان");
    }

    /**
    SCENARIO 1
    User1 goes to User2 page
    User1 sends request
    User2 ignores request
     */
    public function testIgnoreFriendship()
    {
        $this->tester->amOnPage('/desktop-version');
        /**
         * User1 sends friend request
         */
        $this->tester->login($this->user1->username,$this->user1->password);
        $this->tester->amOnPage('/mobile-version');
        $this->tester->amOnPage("/user/". $this->user2->username);
        $this->tester->see('افزودن به دوستان');
        $this->tester->callOnClickHrefLink('//a[contains(@id,"friendship")]');
        $this->tester->see('حذف درخواست افزودن دوست');
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();

        /**
         * User2 ignores friend request of user1
         */
        $this->tester->login($this->user2->username,$this->user2->password);
        $this->tester->amOnPage('/mobile-version');
        $this->tester->amOnPage('/main/notifications');
        $this->tester->see('برای شما درخواست دوستی ارسال کرده است');
        $dataCodeAttr = $this->tester->grabAttributeFrom("//*[contains(@class, 'owm_lbutton owm_friend_request_ignore')]",'data-code');
        $dataRidAttr = $this->tester->grabAttributeFrom("//*[contains(@class, 'owm_lbutton owm_friend_request_ignore')]",'data-rid');
        $this->tester->sendAjax('/m-friends/action/ignore-ajax/',
            ['id'=>$dataRidAttr, 'code'=>$dataCodeAttr]);
        $this->tester->amOnPage("/user/". $this->user1->username);
        $this->tester->see("افزودن به دوستان");
    }
}