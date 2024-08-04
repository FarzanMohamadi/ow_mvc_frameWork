<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfriendsplus.bol
 * @since 1.0
 */
class FRMFRIENDSPLUS_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function isRoleInSelected($roleId, $selectedRolesArray){
        if($selectedRolesArray == null){
            return false;
        }
        foreach ($selectedRolesArray as $selectedRole){
            if($selectedRole == $roleId){
                return true;
            }
        }

        return false;
    }

    public function onUserRegistered(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['forEditProfile']) && $params['forEditProfile']==true){
            return;
        }
        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return;
        }
        if (isset($params["userId"])) {
            $this->manageByUserIdList(array($params["userId"]));
        }
    }

    public function manageByUserIdList($targetUserIds){
        $config =  OW::getConfig();
        $selectedRoles = $config->getValue('frmfriendsplus', 'selected_roles');
        if($selectedRoles != null){
            $selectedRoles = json_decode($selectedRoles);
        }

        $markUserIds = array();
        if($selectedRoles != null && sizeof($selectedRoles) > 0){
            $usersId = BOL_AuthorizationUserRoleDao::getInstance()->findUsersByRoleIds($selectedRoles);
            foreach ($usersId as $userId){
                if(!in_array($userId->userId, $markUserIds)) {
                    $markUserIds[] = $userId->userId;
                    foreach ($targetUserIds as $targetUserId){
                        if($targetUserId!=$userId->userId) {
                            $this->addFriendship($userId->userId, $targetUserId);
                            $dto = new NEWSFEED_BOL_Follow();
                            $dto->feedType = 'user';
                            $dto->feedId = $userId->userId;
                            $dto->userId = $targetUserId;
                            $dto->followTime = time();
                            $dto->permission = NEWSFEED_BOL_Service::PRIVACY_EVERYBODY;
                            NEWSFEED_BOL_FollowDao::getInstance()->save($dto);
                        }
                    }
                }
            }
        }

        if(FRMSecurityProvider::checkPluginActive('mailbox', true)){
            MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();
        }
    }

    public function manageAllUsers(){
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $allUsers = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
        $userIds = array();
        foreach ($allUsers as $user){
            if(!in_array($user->id, $userIds)){
                $userIds[] = $user->id;
            }
        }
        $this->manageByUserIdList($userIds);
    }

    public function addFriendship($requesterId, $userId){
        if(!FRMSecurityProvider::checkPluginActive('friends', true)){
            return;
        }
        FRIENDS_BOL_Service::getInstance()->addFriendship($requesterId, $userId);
    }

    public function getAllUsersForm(){
        $formAllUsers = new Form('manage_all_users');
        $formAllUsers->setAction(OW::getRouter()->urlForRoute('frmfriendsplus_admin_config_all_users'));
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmfriendsplus', 'all_users_label'));
        $formAllUsers->addElement($submit);
        return $formAllUsers;
    }
}