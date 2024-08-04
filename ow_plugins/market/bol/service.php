<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class MARKET_BOL_Service
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

    public function updateUserRoleToSeller($userId, $storeName) {

        $accountType = BOL_AuthorizationRoleDao::getInstance()->findRoleByName("seller");

        if ( !empty($accountType) && !empty($accountType[0]->id) )
        {
            BOL_AuthorizationService::getInstance()->deleteUserRole($userId, $accountType[0]->id);
            BOL_AuthorizationService::getInstance()->saveUserRole($userId, $accountType[0]->id);
            $store = MARKET_BOL_StoreDao::getInstance()->getUserStoreNameById($userId);
            if( empty($store) ){
                $newStore = new MARKET_BOL_Store();
                $newStore->createdAt = time();
                $newStore->userId = $userId;
                $newStore->storeName = $storeName;
                return MARKET_BOL_StoreDao::getInstance()->save($newStore);
            }else{
                $store->storeName =$storeName;
                return MARKET_BOL_StoreDao::getInstance()->save($store);
            }

        }else{
            return false;
        }
    }

    public function removeSeller($userId) {

        $accountType = BOL_AuthorizationRoleDao::getInstance()->findRoleByName("seller");

        if ( !empty($accountType) && !empty($accountType[0]->id) )
        {
            BOL_AuthorizationService::getInstance()->deleteUserRole($userId, $accountType[0]->id);
            MARKET_BOL_StoreDao::getInstance()->removeUserStoreNameById($userId);
            return true;
        }else{
            return false;
        }
    }

    public function getUserRoleStatus($userId){
        $roles = BOL_AuthorizationUserRoleDao::getInstance()->getRoleIdList((int)$userId);
        $accountType = BOL_AuthorizationRoleDao::getInstance()->findRoleByName("seller");
        return in_array($accountType[0]->id , $roles);
    }

    public function getUserStoreNameById($userId){
        $store = MARKET_BOL_StoreDao::getInstance()->getUserStoreNameById($userId);
        return $store;
    }

    public function productHashtagSearchByArray( $hashtags ){

        return NEWSFEED_BOL_ActionDao::getInstance()->productHashtagSearchByArray($hashtags);

    }



}
