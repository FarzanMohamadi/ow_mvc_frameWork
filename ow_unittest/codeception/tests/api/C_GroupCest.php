<?php
use \Codeception\Util\HttpCode;
class C_GroupCest
{

    public function _before(ApiTester $I) {

    }


    private function createGroupViaAPI(ApiTester $I, $access_token, $title = null, $description = null,
                                       $whoCanView = null, $whoCanInvite = null, $whoCanCreateContent = null) {
        // create group
        $groupTitle = !empty($title) ? $title : uniqid('group_title_test_');
        $groupDescription = !empty($description) ? $description : uniqid('group_description_test_');
        $whoCanView = !empty($whoCanView) ? $whoCanView : 'anyone';  // invite or anyone
        $whoCanInvite = !empty($whoCanInvite) ? $whoCanInvite : 'participant'; // participant or creator
        $whoCanCreateContent = !empty($whoCanCreateContent) ? $whoCanCreateContent : 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $access_token,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => true,
            "message" => "group_created"
        ]);
        $I->seeResponseContainsJson([
            'create_group' => [
                'group' => [
                    'title' =>  $groupTitle,
                    'description' => $groupDescription,
                    'whoCanView' => $whoCanView,
                    'whoCanInvite' => $whoCanInvite,
                    'whoCanCreateContent' => $whoCanCreateContent,
                ]
            ]
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
                'group' => [
                    'id' => 'integer',
                    'title' => 'string',
                    'description' => 'string',
                    'whoCanView' => 'string',
                    'whoCanInvite' => 'string',
                    'whoCanCreateContent' => 'string'
                ]
            ]
        ]);
        $groupId = $I->grabDataFromResponseByJsonPath('$.create_group.group.id')[0];
        return (object) ['id' => $groupId, 'title' => $groupTitle, 'description' => $groupDescription, 'whoCanView' => $whoCanView, 'whoCanInvite' => $whoCanInvite, 'whoCanCreateContent' => $whoCanCreateContent];
    }

    /**
     * @param ApiTester $I
     * @param $user
     * @return string $access_token
     */
    private function loginUser(\ApiTester $I, $username, $password) {
        $user_login = $I->login($username, $password);
        $access_token = $user_login['cookies']['ow_login'];
        return $access_token;
    }


    /*
     * Scenario: user can create group
     *
     * inputs: group information
     *
     * output: successful group creation
     */
    public function userCanCreateGroupViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        $this->createGroupViaAPI($I, $user->access_token);
        $I->logout($user->access_token);
    }

    /*
    * Scenario: user can't create group without title
    *
    * inputs: group information
    *
    * output: unsuccessful group creation
    */
    public function userCantCreateGroupWithoutTitleViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $groupDescription = uniqid('group_description_test_');
        $whoCanView = 'invite';  // invite or anyone
        $whoCanInvite = 'participant'; // participant or creator
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $user->access_token,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "invalid_data"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
        $I->logout($user->access_token);
    }

    /*
    * Scenario: user can't create group without description
    *
    * inputs: group information
    *
    * output: unsuccessful group creation
    */
    public function userCantCreateGroupWithoutDescriptionViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $groupTitle = uniqid('group_title_test_');
        $whoCanView = 'invite';  // invite or anyone
        $whoCanInvite = 'participant'; // participant or creator
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $user->access_token,
            'title' =>  $groupTitle,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "invalid_data"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
        $I->logout($user->access_token);
    }

    /*
    * Scenario: user can't create group without whoCanView
    *
    * inputs: group information
    *
    * output: unsuccessful group creation
    */
    public function userCantCreateGroupWithoutWhoCanViewViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $groupTitle = uniqid('group_title_test_');
        $groupDescription = uniqid('group_description_test_');
        $whoCanInvite = 'participant'; // participant or creator
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $user->access_token,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "invalid_data"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
        $I->logout($user->access_token);
    }

    /*
    * Scenario: user can't create group without whoCanInvite
    *
    * inputs: group information
    *
    * output: unsuccessful group creation
    */
    public function userCantCreateGroupWithoutWhoCanInviteViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $groupTitle = uniqid('group_title_test_');
        $groupDescription = uniqid('group_description_test_');
        $whoCanView = 'invite';  // invite or anyone
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $user->access_token,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "invalid_data"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
        $I->logout($user->access_token);
    }

    /*
     * Scenario: user can't create group with duplicated title
     *
     * inputs: group information
     *
     * output: unsuccessful group creation
     */
    public function cantCreateGroupWithDuplicatedTitleViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $group = $this->createGroupViaAPI($I, $user->access_token);

        // create group with duplicated title
        $groupTitle = $group->title;
        $groupDescription = uniqid('group_description_test_');
        $whoCanView = 'invite';  // invite or anyone
        $whoCanInvite = 'participant'; // participant or creator
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'access_token' => $user->access_token,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "گروهی با این نام در حال حاضر موجود است"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
        $I->logout($user->access_token);
    }

    /*
     * Scenario: guest user can't create group
     *
     * inputs: group information
     *
     * output: unsuccessful group creation
     */
    public function guestUserCantCreateGroupViaAPI(\ApiTester $I) {
        // guest user
        // create group
        $groupTitle = uniqid('group_title_test_');
        $groupDescription = uniqid('group_description_test_');
        $whoCanView = 'invite';
        $whoCanInvite = 'participant';

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/create_group', [
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => 'participant',
            'whoCanUploadFile' => 'participant',
            'whoCanCreateTopic' => 'manager',
        ]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN); // 403
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => false,
            "message" => "authorization_error"
        ]);
        $I->seeResponseMatchesJsonType([
            'create_group' => [
                "valid" => 'boolean',
                "message" => 'string',
            ]
        ]);
    }

    /*
     * Scenario: user can edit group
     *
     * inputs: group information
     *
     * output: successful group edition
     */
    public function userCanEditGroupViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $group = $this->createGroupViaAPI($I, $user->access_token);

        // edit group
        $groupTitle = uniqid('group_title_test_');
        $groupDescription = uniqid('group_description_test_');
        $whoCanView = 'invite';  // invite or anyone
        $whoCanInvite = 'participant'; // participant or creator
        $whoCanCreateContent = 'group'; // group or channel

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/edit_group?groupId=' . $group->id, [
            'access_token' => $user->access_token,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanView' => $whoCanView,
            'whoCanInvite' => $whoCanInvite,
            'whoCanCreateContent' => $whoCanCreateContent,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => true,
            "message" => "group_edited"
        ]);
        $I->seeResponseContainsJson([
            'id' => $group->id,
            'userId' => $user->id,
            'title' =>  $groupTitle,
            'description' => $groupDescription,
            'whoCanInvite' => $whoCanInvite,
            'whoCanView' => $whoCanView,
        ]);
        $I->seeResponseMatchesJsonType([
            'edit_group' => [
                "valid" => 'boolean',
                "message" => 'string',
                'group' => [
                    'id' => 'integer',
                    'time' => 'string',
                    'userId' => 'integer',
                    'title' => 'string',
                    'description' => 'string',
                    'whoCanInvite' => 'string',
                    'whoCanView' => 'string'
                ]
            ]
        ]);
        $I->logout($user->access_token);
    }

    /**
     * Scenario: user can delete group
     *
     * inputs: group information
     *
     * output: successful group deletion
     */
    public function userCanDeleteGroupViaAPI(\ApiTester $I) {
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // create group
        $group = $this->createGroupViaAPI($I, $user->access_token);

        // delete group
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/delete_group?groupId=' . $group->id, [
            'access_token' => $user->access_token,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "valid" => true,
            "leavable" => true,
            "groupId" => $group->id
        ]);
        $I->seeResponseMatchesJsonType([
            'delete_group' => [
                "valid" => 'boolean',
                "leavable" => 'boolean',
                "groupId" => 'string'
            ]
        ]);
        $I->logout($user->access_token);
    }

    /**
     * Scenario: user can invite users to group
     *
     * inputs: group information and userId
     *
     * output: successful invite to group
     */
    public function userCanInviteUsersToGroupViaAPI(\ApiTester $I) {
        // register and login user 1
        $user = $I->getANormalUser();
        $user->access_token = $this->loginUser($I, $user->username, $user->password);

        // user1 create group
        $group = $this->createGroupViaAPI($I, $user->access_token);

        //register user2
        $user2 = $I->getANormalUser();

        // user 1 invites user2 to group
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPost('/mobile/services/action/groups_invite_user?groupId=' . $group->id . '&userId=' . $user2->id, [
            'access_token' => $user->access_token,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'groups_invite_user' => [
                "valid" => true,
                "result_key" => "add_automatically"
            ]
        ]);
        $I->logout($user->access_token);
    }

}
