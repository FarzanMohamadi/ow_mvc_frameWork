<?php
ob_start();
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class AuthorizationServiceTest extends FRMUnitTestUtilites
{
    private static $USER_NAME_1 = 'user_moderator_1';
    private static $EMAIL_1 = 'user_moderator_1@gmail.com';
    private static $PASSWORD_1 = 'password123';

    private $auth_service;
    /**
     * @var BOL_User
     */
    private $user1;

    protected function setUp()
    {
        parent::setUp();

        $this->auth_service = BOL_AuthorizationService::getInstance();

        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser(self::$USER_NAME_1, self::$EMAIL_1, self::$PASSWORD_1, "1987/3/21", "1", $accountType);
        $this->user1 = BOL_UserService::getInstance()->findByUsername(self::$USER_NAME_1);
    }

    function testFindAction()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $this->auth_service->addAction($action, array("en"=>"action_label"));
        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);

        self::assertTrue($this->auth_service->findAction($new_group_name, $new_action_name) != null);

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $this->auth_service->deleteAction($action_id);
    }

    function testFindGroupByName()
    {
        $event_installed = true;
        if (!FRMSecurityProvider::checkPluginActive('event', true)){
            $event_installed = false;
            BOL_PluginService::getInstance()->install('event');
        }
        $event_group = $this->auth_service->findGroupByName('event');
        self::assertTrue($event_group != null and $event_group->getName() == 'event');

        if (!$event_installed){
            BOL_PluginService::getInstance()->deactivate('event');
        }
    }

    function testGetLastDisplayLabelRoleOfIdList(){
        $role_label = 'test_role_' . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($role_label);
        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role->setDisplayLabel(true);
        BOL_AuthorizationRoleDao::getInstance()->save($new_role);
        $role_id = $new_role->id;

        BOL_AuthorizationUserRoleDao::getInstance()->clearCachedItems($this->user1->id);
        $this->auth_service->saveUserRole($this->user1->id, $role_id);

        $user1_new_roles = $this->auth_service->getLastDisplayLabelRoleOfIdList(array($this->user1->id));

        self::assertTrue($user1_new_roles[$this->user1->id]['name'] == $role_label);

        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role_id = $new_role->id;
        $this->auth_service->deleteRoleById($new_role_id);
    }

    function testGetGroupList()
    {
        $event_installed = true;
        if (!FRMSecurityProvider::checkPluginActive('event', true)) {
            $event_installed = false;
            BOL_PluginService::getInstance()->install('event');
        }

        $all_groups = array();
        foreach ($this->auth_service->getGroupList() as $group){
            $all_groups[] = $group->name;
        }
        self::assertTrue(in_array('event', $all_groups));
        if (!$event_installed){
            BOL_PluginService::getInstance()->deactivate('event');
        }
    }

    function testIsActionAuthorized()
    {
        $this->signOut();
        self::assertFalse($this->auth_service->isActionAuthorized('event', 'add_event'));

        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $action->setAvailableForGuest(true);
        $this->auth_service->addAction($action, array("en"=>"action_label"));

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();

        $this->signIn($this->user1->id);
        self::assertTrue($this->auth_service->isActionAuthorized($new_group_name, $new_action_name));
        $this->signout();

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);
        $this->auth_service->deleteAction($action_id);
    }

    function testIsActionAuthorizedBy()
    {
        $this->signOut();

        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $action->setAvailableForGuest(true);
        $this->auth_service->addAction($action, array("en"=>"action_label"));

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();


        $this->signIn($this->user1->id);
        self::assertTrue($this->auth_service->isActionAuthorizedBy($new_group_name, $new_action_name)['status']);
        $this->signout();

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);
        $this->auth_service->deleteAction($action_id);

        self::assertTrue(true);
    }

    function testGetActionStatus()
    {
        $this->signOut();
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $action->setAvailableForGuest(true);
        $this->auth_service->addAction($action, array("en"=>"action_label"));

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();

        self::assertTrue($this->auth_service->getActionStatus($new_group_name, $new_action_name)['status'] == 'available');
    }

    function testIsActionAuthorizedForUser()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $action->setAvailableForGuest(false);
        $this->auth_service->addAction($action, array("en"=>"action_label"));

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();

        $this->signIn($this->user1->id);
        self::assertTrue($this->auth_service->isActionAuthorizedForUser($this->user1->id, $new_group_name, $new_action_name));
        $this->signout();

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);
        $this->auth_service->deleteAction($action_id);
    }

    function testIsActionAuthorizedForGuest()
    {
        $this->signOut();
        self::assertFalse($this->auth_service->isActionAuthorizedForGuest("event", "add_event"));

        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $action->setAvailableForGuest(true);
        $this->auth_service->addAction($action, array("en"=>"action_label"));

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();
        $this->signOut();
        self::assertTrue($this->auth_service->isActionAuthorizedForGuest($new_group_name, $new_action_name));

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);
        $this->auth_service->deleteAction($action_id);
    }

    function testAddModerator()
    {
        $this->auth_service->addModerator($this->user1->getId());
        self::assertTrue($this->auth_service->isModerator($this->user1->getId()));
    }

    function testAddAdministrator()
    {
        $this->auth_service->addAdministrator($this->user1->id);
        $this->auth_service->clearAndResetModeratorCache();
        $this->signIn($this->user1->id);
        self::assertTrue(OW::getUser()->isAdmin());
    }

    function testDeleteModerator()
    {
        $this->auth_service->addModerator($this->user1->id);
        $moderator_id = $this->auth_service->getModeratorIdByUserId( $this->user1->id);
        $this->auth_service->deleteModerator($moderator_id);
        self::assertFalse($this->auth_service->isModerator($this->user1->id));
    }

    function testGiveAllPermissions()
    {
        $this->auth_service->addModerator($this->user1->id);
        $this->auth_service->giveAllPermissions($this->user1->id);

        $all_groups_permissions = $this->auth_service->getGroupList(true);
        $all_groups_ids = array();
        foreach ($all_groups_permissions as $group){
            if ($this->auth_service->getAdminGroupId() == $group->id)
                continue;
            $all_groups_ids[] = $group->id;
        }

        $moderator_permissions = $this->auth_service->getModeratorPermissionList();
        $moderator_permissions_ids = array();
        foreach ($moderator_permissions as $mod_per){
            if ($mod_per->moderatorId == $this->user1->id)
                $moderator_permissions_ids[] = $mod_per->groupId;
        }

        self::assertTrue($moderator_permissions_ids == $all_groups_ids);
    }

    function testAddRole()
    {
        $test_role_name = "test_role_" . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($test_role_name);
        $all_roles = $this->auth_service->getRoleList();
        foreach ($all_roles as $role){
            if ($role->name == $test_role_name)
                $this->auth_service->deleteRoleById($role->id);
            self::assertTrue(true);
            return;
        }
        self::assertTrue(false);
    }

    function testFindAdminIdList(){
        $admin_ids_list = $this->auth_service->findAdminIdList();
        self::assertFalse(in_array($this->user1->id, $admin_ids_list));

        $this->auth_service->addModerator($this->user1->id);
        $permission = new BOL_AuthorizationModeratorPermission();
        $permission->setGroupId($this->auth_service->getAdminGroupId())->setModeratorId($this->user1->id);
        $this->auth_service->saveModeratorPermission($permission);

        $admin_ids_list = $this->auth_service->findAdminIdList();
        self::assertTrue(in_array($this->user1->id, $admin_ids_list));
    }

    function testDeleteRoleById(){
        $role_label = 'test_role_' . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($role_label);
        $new_role_id = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0]->id;
        $this->auth_service->deleteRoleById($new_role_id);
        $all_roles = $this->auth_service->getRoleList();
        foreach ($all_roles as $role){
            if ($role->id == $new_role_id){
                self::assertTrue(false);
                return false;
            }
        }
        self::assertTrue(true);
    }

    function testDeleteUserRolesByUserId(){
        $role_label = 'test_role_' . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($role_label);
        $role_id = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0]->id;
        $this->auth_service->saveUserRole($this->user1->id, $role_id);
        $this->auth_service->deleteUserRolesByUserId($this->user1->id);
        $user1_roles = $this->auth_service->findUserRoleList($this->user1->id);
        self::assertTrue(sizeof($user1_roles) == 0);

        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role_id = $new_role->id;
        $this->auth_service->deleteRoleById($new_role_id);
    }

    function testDeleteUserRole()
    {
        $role_label = 'test_role_' . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($role_label);
        $role_id = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0]->id;
        $this->auth_service->saveUserRole($this->user1->id, $role_id);
        $this->auth_service->deleteUserRole($this->user1->id, $role_id);
        $user1_roles = $this->auth_service->findUserRoleList($this->user1->id);
        foreach ($user1_roles as $role){
            if ($role->id == $role_id){
                self::assertTrue(false);
                return;
            }
        }
        self::assertTrue(true);

        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role_id = $new_role->id;
        $this->auth_service->deleteRoleById($new_role_id);
    }

    function testGrantActionListToRole()
    {
        $role_label = 'test_role_' . FRMSecurityProvider::generateUniqid();
        $this->auth_service->addRole($role_label);
        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role_id = $new_role->id;
        $this->auth_service->saveUserRole($this->user1->id, $new_role_id);

        $event_group_id = $this->auth_service->findGroupByName("event")->id;
        $event_group_actions = $this->auth_service->findActionListByGroupId($event_group_id);
        $this->auth_service->grantActionListToRole($new_role, $event_group_actions);

        $this->auth_service->clearAndResetGroupAndActionCache();
        $this->auth_service->clearAndResetPermissionsCache();

        foreach ($event_group_actions as $action){
            if (!$this->auth_service->isActionAuthorizedForUser($this->user1->id, "event", $action->name)){
                self::assertTrue(false);
                return false;
            }
        }
        self::assertTrue(true);

        $new_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName($role_label)[0];
        $new_role_id = $new_role->id;
        $this->auth_service->deleteRoleById($new_role_id);
    }

    function testAddGroup()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);

        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        self::assertTrue($this->auth_service->findGroupByName($new_group_name) != null);
        $this->auth_service->deleteGroup($new_group_name);

        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);
    }

    function testDeleteGroup()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);

        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $this->auth_service->deleteGroup($new_group_name);
        self::assertTrue($this->auth_service->findGroupByName($new_group_name) == null);

        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);
    }

    function testAddAction()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $this->auth_service->addAction($action, array("en"=>"action_label"));
        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);

        self::assertTrue(sizeof($this->auth_service->findActionListByGroupId($new_group_id)) == 1 &&
            $this->auth_service->findActionListByGroupId($new_group_id)[0]->id == $action_id);

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);

        $this->auth_service->deleteAction($action_id);
    }

    function testDeleteAction()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);
        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $new_group_id = $this->auth_service->findGroupByName($new_group_name)->id;

        $action = new BOL_AuthorizationAction();
        $new_action_name = "test_action_" . FRMSecurityProvider::generateUniqueId();
        $action->setName($new_action_name);
        $action->setGroupId($new_group_id);
        $this->auth_service->addAction($action, array("en"=>"action_label"));
        $action_id = BOL_AuthorizationActionDao::getInstance()->getIdByName($new_action_name);

        $this->auth_service->deleteAction($action_id);
        self::assertTrue(sizeof($this->auth_service->findActionListByGroupId($new_group_id)) == 0);

        $this->auth_service->deleteGroup($new_group_name);
        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);
    }

    function testDeleteGroupByName()
    {
        $new_group = new BOL_AuthorizationGroup();
        $new_group_name = 'new_group_name_' . FRMSecurityProvider::generateUniqueId();
        $new_group->setName($new_group_name);

        $this->auth_service->addGroup($new_group, array("en"=>"first_label"));
        $this->auth_service->deleteGroupByName($new_group_name);
        self::assertTrue($this->auth_service->findGroupByName($new_group_name) == null);

        $base_prefix_id = BOL_LanguagePrefixDao::getInstance()->findPrefixId('base');
        $new_group_key = BOL_LanguageKeyDao::getInstance()->findKeyId($base_prefix_id, "authorization_group_" . $new_group_name);
        BOL_LanguageService::getInstance()->deleteKey($new_group_key);
    }

    protected function tearDown()
    {
        parent::tearDown();
        try{
            $this->signout();
            FRMSecurityProvider::deleteUser(self::$USER_NAME_1);
        } catch (Exception $e){
        }
    }
}