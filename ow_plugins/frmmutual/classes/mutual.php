<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmutual.bol
 * @since 1.0
 */
class FRMMUTUAL_CLASS_Mutual
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

    /***
     * @param $profileOwnerId
     * @param $currentUserId
     * @return array
     */
    public function getMutualFriends($profileOwnerId, $currentUserId){
        if(isset($count)){
            $profileOwnerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $profileOwnerId,'count' => $count));
            $currentUserFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $currentUserId,'count' => $count));
        }else {
            $profileOwnerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $profileOwnerId));
            $currentUserFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $currentUserId));
        }
        $countNumberOfMutualFriends = OW::getConfig()->getValue('frmmutual', 'numberOfMutualFriends');
        $mutualFriensdId = array();
        foreach ($profileOwnerFriendsId as $profileOwnerFriendId) {
            if (in_array($profileOwnerFriendId, $currentUserFriendsId)) {
                $mutualFriensdId[] = $profileOwnerFriendId;
            }
        }
        $FilteredMutualFriensdId = array();
        foreach ($profileOwnerFriendsId as $profileOwnerFriendId) {
            if (in_array($profileOwnerFriendId, $currentUserFriendsId) && sizeof($FilteredMutualFriensdId)<$countNumberOfMutualFriends) {
                $FilteredMutualFriensdId[] = $profileOwnerFriendId;
            }
        }

        //all
        //

        return array('mutualFriensdId' => $mutualFriensdId, 'FilteredMutualFriensdId' => $FilteredMutualFriensdId);
    }
}