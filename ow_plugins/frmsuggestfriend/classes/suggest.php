<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsuggestfriend.bol
 * @since 1.0
 */
class FRMSUGGESTFRIEND_CLASS_Suggest
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
    
    public function getSuggestedFriends($currentUserId, $sizeOfSuggestFriend = 9){
        $secondLevelFriendsOfFriendsId = array();
        if(!FRMSecurityProvider::checkPluginActive('friends', true)) {
            return $secondLevelFriendsOfFriendsId;
        }
        $secondLevelFriendsOfFriendsId = FRIENDS_BOL_Service::getInstance()->findFriendsIdOfUsersList($currentUserId, 0, $sizeOfSuggestFriend);
        return $secondLevelFriendsOfFriendsId;
    }
}