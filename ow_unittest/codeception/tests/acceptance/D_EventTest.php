<?php

class D_EventTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;
    private $user1, $user2, $user3;
    private $event1, $event2, $event3;

    protected function _before() {
        $this->user1 = $this->tester->registerUser();
        $this->user2 = $this->tester->registerUser();
        $this->user3 = $this->tester->registerUser();

        $this->tester->makeFriendship($this->user1, $this->user2);

        $this->tester->login($this->user1->username, $this->user1->password);

        $event_name = uniqid('event_test_');
        $this->event1 = $this->createEvent($event_name, 'Seminar', 'loc1', 'anyone', 'creator');

        $event_name = uniqid('event_test_');
        $this->event2 = $this->createEvent($event_name, 'Zoo', 'loc2', 'anyone', 'participant');

        $event_name = uniqid('event_test_');
        $this->event3 = $this->createEvent($event_name, 'Secret Society', 'loc3', 'invite', 'participant');

        $this->tester->sendAjax('/event/base/invite-responder/', [
            'eventId' => $this->event3->id,
            'userIdList' => json_encode([$this->user3->id])
        ]);
        $this->tester->logout();
    }

    private function createEvent($title, $desc, $location, $whoCanView, $whoCanInvite) {
        $this->tester->amOnPage('/event/add');
        $this->tester->fillField('title', $title);
        $this->tester->fillField('desc', $desc);
        $this->tester->fillField('location', $location);

        if ($whoCanView == 'anyone') {
            $this->tester->selectOption('input[name="who_can_view"]', '1');
        } else {
            $this->tester->selectOption('input[name="who_can_view"]', '2');
        }

        if ($whoCanInvite == 'creator') {
            $this->tester->selectOption('input[name="who_can_invite"]', '1');
        } else {
            $this->tester->selectOption('input[name="who_can_invite"]', '2');
        }

        $this->tester->click('submit');
        $this->tester->seeCurrentUrlMatches('~/event/(\d+)~');
        $eventId = $this->tester->grabFromCurrentUrl('~/event/(\d+)~');
        return (object) ['id' => $eventId, 'title' => $title, 'desc' => $desc, 'location' => $location, '$whoCanView' => $whoCanView, '$whoCanInvite' => $whoCanInvite];
    }

    /***
     * Successful Login
     */
    public function testEvent1() {
        //----SCENARIO 1 - Seminar
        //User1 create Event1 : everyone can join, only user1 can invite
        //User2 Maybe attends, can't invite, can post
        //User3 Won't attend, can't invite, can post

        //----SCENARIO 2 - Zoo
        //User1 create Event2 : everyone can join and invite
        //User2 Maybe attends, can invite, can post

        //----SCENARIO 3 - Secret Society
        //User1 create Event3 : join with invite link, invites user3
        //User2 can't attend or read
        //User3 can't attend or read

        //----------USER2
        $this->tester->login($this->user2->username, $this->user2->password);
        $this->tester->amOnPage('/event/' . $this->event1->id);

        $this->tester->sendAjax('/event/base/attend-form-responder',[
                'form_name' => 'event_attend',
                'eventId' => $this->event1->id,
                'attend_status' => 2 // maybe
         ]);

        $this->tester->amOnPage('/event/' . $this->event1->id); // refresh page for invite link
        $this->tester->see('احتمالا شرکت می‌کنم');
        $this->tester->seeElement('//*[contains(@class,"ow_comments_input")]');
        $this->tester->dontSeeElement('//*[@id="inviteLink"]');

        $this->tester->amOnPage('/event/' . $this->event2->id);
        $this->tester->sendAjax('/event/base/attend-form-responder',[
            'form_name' => 'event_attend',
            'eventId' => $this->event2->id,
            'attend_status' => 2 // maybe
        ]);
        $this->tester->amOnPage('/event/' . $this->event2->id);
        $this->tester->see('احتمالا شرکت می‌کنم');
        $this->tester->seeElement('//*[contains(@class,"ow_comments_input")]');
        $this->tester->seeElement('//*[@id="inviteLink"]');

        $this->tester->amOnPage('/event/' . $this->event3->id);
        $this->tester->dontSeeElement('//*[contains(@class,"ow_comments_input")]');
        $this->tester->logout();

        //----------USER3
        $this->tester->login($this->user3->username, $this->user3->password);
        $this->tester->amOnPage('/event/' . $this->event1->id);
        $this->tester->sendAjax('/event/base/attend-form-responder',[
            'form_name' => 'event_attend',
            'eventId' => $this->event1->id,
            'attend_status' => 3 // no
        ]);
        $this->tester->amOnPage('/event/' . $this->event1->id); // refresh page for invite link
        $this->tester->see('شرکت نمی‌کنم');
        $this->tester->seeElement('//*[contains(@class,"ow_comments_input")]');
        $this->tester->dontSeeElement('//*[@id="inviteLink"]');

        $this->tester->amOnPage('/event/' . $this->event3->id);
        $this->tester->dontSeeElement('//*[contains(@class,"ow_comments_input")]');

        $this->tester->logout();
    }

}