<?php
use \Codeception\Util\Locator;
class D_MobileEventTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    private $user1,$user2,$user3;

    private $event1_title;

    private $eventId = null;

    protected function _before()
    {
        $this->user1 = $this->tester->registerUser();
        $this->user2 = $this->tester->registerUser();
        $this->user3 = $this->tester->registerUser();
        $this->event1_title="Mobile Test Event".time();
    }

    /**
     SCENARIO 1 - Secret Society
     User1 creates an event.
     User1 invites User2
     User2 can't view
     User3 can't view
     */
    public function testAccessPrivateEvent()
    {
        /**
         * go to mobile version
         */
        $this->tester->makeFriendship($this->user1,$this->user2);
        $this->tester->login($this->user1->username,$this->user1->password);
        $this->tester->amOnPage('/mobile-version');

        /**
         * go to event creation page
         */
        $this->tester->amOnPage('/event/add');
        $this->tester->see('ساخت رویداد جدید');

        /**
         * fill fields and create a new event
         */
        $this->tester->fillField(['name'=>'title'],$this->event1_title);
        $this->tester->checkOption(['name'=>'endDateFlag']);
        $this->tester->fillField(['name'=>'desc'],'event desc');
        $this->tester->fillField(['name'=>'location'],'event location');
        $this->tester->selectOption('//input[@name="who_can_view"]', '2');
        $this->tester->selectOption('//input[@name="who_can_invite"]', '1');
        $this->tester->click('اضافه کردن');
        $this->tester->see($this->event1_title);
        $this->tester->see('پایان');
        $this->eventId = (int)$this->tester->grabFromCurrentUrl('~/event/(\d+)$~');;

        /**
         * invite user2 to the event
         */
        $userIdList=json_encode(array((int)$this->user2->id));
        $this->tester->sendAjax('/m-event/base/invite-responder/',['eventId'=>$this->eventId,'userIdList'=>$userIdList]);


        /**
         * User1 goes to desktop version and sign-out
         */
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();

        /**
         * User2 sign-in and go to mobile version
         */
        $this->tester->login($this->user2->username,$this->user2->password);
        $this->tester->amOnPage('/mobile-version');

        /**
         * go to event page and must redirected to invited events page
         */
        $this->tester->amOnPage('/event/'.$this->eventId);
        $this->tester->seeInCurrentUrl('/events/invited');


        /**
         * user2 accept the invitation
         */
        $this->tester->amOnPage('/events/invited');
        $this->tester->click(['id'=>'accept_'.$this->eventId]);

        /**
         * user2 access to the event
         */
        $this->tester->amOnPage('/event/'.$this->eventId);
        $this->tester->see($this->event1_title);

        /**
         * User2 goes to desktop version and sign-out
         */
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();


        /**
         * Guest can't view private event
         */

        $this->tester->amOnPage('/mobile-version');
        $this->tester->amOnPage('/event/'.$this->eventId);
        $this->tester->dontSee($this->event1_title);
        $this->tester->amOnPage('/desktop-version');


        /**
         * User3 sign-in and go to mobile version
         */
        $this->tester->login($this->user3->username,$this->user3->password);
        $this->tester->amOnPage('/mobile-version');

        /**
         * go to events list and see nothing
         */
        $this->tester->amOnPage('/event/'.$this->eventId);
        $this->tester->see('صفحه مورد نظر وجود ندارد');
        /**
         * User3 goes to desktop version and sign-out
         */
        $this->tester->amOnPage('/desktop-version');
        $this->tester->logout();
    }

}