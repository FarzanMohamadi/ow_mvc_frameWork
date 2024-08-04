<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceSuggest
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }


    public function getUserSuggest($userId){
        if(!FRMSecurityProvider::checkPluginActive('frmsuggestfriend', true)){
            return array();
        }

        if($userId == null){
            return array();
        }

        if(!OW::getUser()->isAuthenticated() || OW::getUser()->getId() != $userId){
            return array();
        }
        $users = array();
        $usersId = FRMSUGGESTFRIEND_CLASS_Suggest::getInstance()->getSuggestedFriends($userId, 100);
        $usersId = array_slice($usersId,0,100);
        $usersObject = BOL_UserService::getInstance()->findUserListByIdList($usersId);
        $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($usersId);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($usersId);
        foreach ($usersObject as $userObject){
            $users[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatars[$userObject->id], $usernames[$userObject->id]);
        }
        return $users;
    }
}