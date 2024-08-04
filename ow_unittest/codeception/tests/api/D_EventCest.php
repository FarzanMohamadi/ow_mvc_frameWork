<?php
use \Codeception\Util\HttpCode;
class D_EventCest
{
    private $user1, $user2, $user3;
    private $event1, $event2;
    private function friendshipRequest(ApiTester $I,$access_token_user,$userid){
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/friend_request/?=&userId='.$userid, [
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
    private function makeFriendship(ApiTester $I,$user1,$user2){
        $I->assertNotFalse($this->friendshipRequest($I,$user1->access_token,$user2->id));
        $I->assertNotFalse($this->friendshipAccept($I,$user2->access_token,$user1->id));
    }
    private function createEvent(ApiTester $I, $access_token, $fields ) {
        // create Event
        $eventTitle = !empty($fields['title'])? $fields['title']: null ;
        $eventDescription = !empty($fields['description'])? $fields['description']: null ;
        $whoCanView = !empty($fields['whoCanView'])? $fields['whoCanView']: null ;
        $whoCanInvite = !empty($fields['whoCanInvite'])? $fields['whoCanInvite']: null ;
        $location = !empty($fields['location'])? $fields['location']: null ;
        $startDate=!empty($fields['startDate'])? $fields['startDate']: null ;
        $endDate=!empty($fields['endDate'])? $fields['endDate']: null ;
        $startTime=!empty($fields['startTime'])? $fields['startTime']: null ;
        $endTime=!empty($fields['endTime'])? $fields['endTime']: null ;


        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_event', [
            'access_token' => $access_token,
            'title' =>  $eventTitle,
            'desc' => $eventDescription,
            'who_can_view' => $whoCanView,
            'who_can_invite' => $whoCanInvite,
            'location'=>$location,
            'start_date'=> $startDate,
            'end_date'=> $endDate,
            'start_time'=> $startTime,
            'end_time'=> $endTime
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.create_event')[0];
        if(!$resp['valid'] && $resp['message'] == "invalid_data"){
            return false;
        }

        $I->seeResponseContainsJson([
            "valid" => true,
            "message" => "event_created"
        ]);
        $I->seeResponseContainsJson([
            'create_event' => [
                'event' => [
                    'title' =>  $eventTitle,
                    'description' => $eventDescription,
                    'whoCanView' => $whoCanView,
                    'whoCanInvite' => $whoCanInvite,
                    'location'=>$location
                ]
            ]
        ]);
        $I->seeResponseMatchesJsonType([
            'create_event' => [
                "valid" => 'boolean',
                "message" => 'string',
                'event' => [
                    'id' => 'integer',
                    'title' => 'string',
                    'description' => 'string',
                    'whoCanView' => 'string',
                    'whoCanInvite' => 'string',
                ]
            ]
        ]);
        $eventId = $I->grabDataFromResponseByJsonPath('$.create_event.event.id')[0];
        return (object) ['id' => $eventId, 'title' => $eventTitle, 'description' => $eventDescription, 'whoCanView' => $whoCanView, 'whoCanInvite' => $whoCanInvite, 'location'=>$location];
    }
    private function attendEvent(ApiTester $I,$access_token_user,$eventid){
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/join_event/?=&eventId='.$eventid, [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.join_event')[0];
        return $resp['valid'];
    }
    private function inviteUserToEvent(ApiTester $I,$access_token_user,$eventid,$userid)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/event_invite_user/?=&userId='.$userid.'&eventId='.$eventid, [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.event_invite_user')[0];
        return $resp['valid'];
    }
    private function canSeeEvent(ApiTester $I,$access_token_user,$eventid)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/information/get_event/?=&eventId='.$eventid, [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $resp=$I->grabDataFromResponseByJsonPath('$.get_event')[0];
        if(isset($resp['valid'])) {
            return $resp['valid'];
        }
        else if(isset($resp['title']) ){
            return true;
        }
    }
    private function canAcceptInvite(ApiTester $I,$access_token_user,$eventid){
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/event_accept_invite/?=&eventId='.$eventid, [
            'access_token'=>$access_token_user,
        ]);
        $I->seeResponseCodeIs(200); // 200
        $I->seeResponseIsJson();
        $resp = $I->grabDataFromResponseByJsonPath('$.event_accept_invite.')[0];
        return $resp['valid'];
    }
    /**
     * @param ApiTester $I
     * @param $user
     * @return string $access_token
     */
    private function loginUser(\ApiTester $I,$user) {
        $user_login = $I->login($user->username, $user->password);
        $access_token = $user_login['cookies']['ow_login'];
        $user->access_token=$access_token;
    }
    /*
     * Scenario: user  create event
     *
     * inputs: event information
     *
     * output: successful event creation
     */
    public function testSuccessfullCreation(ApiTester $I) {
        $user = $I->getANormalUser();
        $this->loginUser($I, $user);
        $I->assertNotFalse($user);
        $today = date('Y/m/d', time());
        $I->assertNotFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when all fields exist,return true
        $I->logout($user->access_token);
    }
    /*
    * Scenario: user can't create event without any essential fields
    *
    * inputs: event information
    *
    * output: unsuccessful event creation
    */
    public function testUnsuccessfullCreation(ApiTester $I) {
        $user = $I->getANormalUser();
        $I->assertNotFalse($user);
        $this->loginUser($I,$user);
        $today = date('Y/m/d', time());
        $I->assertFalse($this->createEvent($I, $user->access_token,array()));
        $I->assertFalse($this->createEvent($I, $user->access_token,array('description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when title does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when description does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when location does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when whocanview does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','startDate'=>$today,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when whoCanInvite does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'endDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when startDate does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'startTime'=>'allday','endTime'=>'allday')));//when endDate does not exist,return false
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'endTime'=>'allday')));//when  startTime  does not exist,return flase
        $I->assertFalse($this->createEvent($I, $user->access_token,array('title'=>'t1','description'=>'d1','location'=>'loc1','whoCanView'=>'1','whoCanInvite'=>1,'startDate'=>$today,'endDate'=>$today,'startTime'=>'allday',)));//when endTime does not exist,return false

        $I->logout($user->access_token);
    }


    public function publicEvent(ApiTester $I){
        $this->user1 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user1);
        $this->user2 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user2);
        $this->user3 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user3);

        $this->makefriendship($I,$this->user1,$this->user2);
        $this->makefriendship($I,$this->user2,$this->user3);
        //event1: user1 create event1 as a public event that only himself can invite others to it
        $today = date('Y/m/d', time());
        $this->event1 = $this->createEvent($I, $this->user1->access_token, array('title'=>'seminar', 'description' => 'Seminar', 'location' => 'loc1',
            'whoCanView' => '1', 'whoCanInvite' => '1', 'startDate' => $today, 'endDate' => $today, 'startTime' => 'allday', 'endTime' => 'allday'));
        $I->assertNotFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event1->id));
        $I->assertNotFalse($this->attendEvent($I,$this->user2->access_token,$this->event1->id));
        $I->assertFalse($this->inviteUserToEvent($I,$this->user2->access_token,(string)$this->event1->id,$this->user3->id));
//event2: user1 cretae event2 as a public event that all articipant can invite others to it
        $this->event2 = $this->createEvent($I, $this->user1->access_token, array('title'=>'Zoo', 'description' => 'zoo', 'location' => 'loc2',
            'whoCanView' => '1', 'whoCanInvite' => '2', 'startDate' => $today, 'endDate' => $today, 'startTime' => 'allday', 'endTime' => 'allday'));
        $I->assertNotFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event2->id));
        $I->assertNotFalse($this->attendEvent($I,$this->user2->access_token,$this->event2->id));
        $I->assertNotFalse($this->inviteUserToEvent($I,$this->user2->access_token,(string)$this->event2->id,$this->user3->id));

        $I->logout($this->user2->access_token);
        $I->logout($this->user1->access_token);
        $I->logout($this->user3->access_token);

    }
    public function privateEvent(ApiTester $I){
        //register and login user1 and user2 and user3
        $this->user1 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user1);
        $this->user2 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user2);
        $this->user3 = $I->getANormalUser(true);
        $this->loginUser($I,$this->user3);
        $I->makeFriendship($I,$this->user1->access_token,$this->user2->access_token,$this->user1->id,$this->user2->id);
        $I->makeFriendship($I,$this->user2->access_token,$this->user3->access_token,$this->user2->id,$this->user3->id);

//event 1: User1 create a private event that only himself can invite others(if others are his frieds)
        $today = date('Y/m/d', time());
        $this->event1 = $this->createEvent($I, $this->user1->access_token, array('title'=>'Secre Society creator', 'description' => 'secret society creator', 'location' => 'loc3',
            'whoCanView' => '2', 'whoCanInvite' => '1', 'startDate' => $today, 'endDate' => $today, 'startTime' => 'allday', 'endTime' => 'allday'));
        //user2 can not see and attend to event1 because evet1 is private and he is not invited yet
        $I->assertFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event1->id));
        $I->assertFalse($this->attendEvent($I,$this->user2->access_token,$this->event1->id));
        //user2 is invited to event1 and can accept invite and  can see event1 but he can not invite other because in event1,only creator can invite others
        $I->assertNotFalse($this->inviteUserToEvent($I,$this->user1->access_token,(string)$this->event1->id,$this->user2->id));
        $I->assertNotFalse($this->canAcceptInvite($I,$this->user2->access_token,$this->event1->id));
        $I->assertNotFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event1->id));
        $I->assertFalse($this->inviteUserToEvent($I,$this->user2->access_token,(string)$this->event1->id,$this->user3->id));
//event 2 :User1 create a private event that all participant can invite others(if others are their frieds)

        $this->event2 = $this->createEvent($I, $this->user1->access_token, array('title'=>'secret sociaty participant', 'description' => 'secert society participant', 'location' => 'loc4',
            'whoCanView' => '2', 'whoCanInvite' => '2', 'startDate' => $today, 'endDate' => $today, 'startTime' => 'allday', 'endTime' => 'allday'));
        //user2 can not see and attend to event2 because evet2 is private and he is not invited yet
        $I->assertFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event2->id));
        $I->assertFalse($this->attendEvent($I,$this->user2->access_token,$this->event2->id));
        //user2 is invited to event2 and can accept invite and  can see event1 and he can  invite other because he is one of its participant
        $I->assertNotFalse($this->inviteUserToEvent($I,$this->user1->access_token,(string)$this->event2->id,$this->user2->id));
        $I->assertNotFalse($this->canAcceptInvite($I,$this->user2->access_token,$this->event2->id));
        $I->assertNotFalse($this->canSeeEvent($I,$this->user2->access_token,$this->event2->id));
        $I->assertNotFalse($this->inviteUserToEvent($I,$this->user2->access_token,(string)$this->event2->id,$this->user3->id));

        $I->logout($this->user2->access_token);
        $I->logout($this->user1->access_token);
        $I->logout($this->user3->access_token);

    }

}
