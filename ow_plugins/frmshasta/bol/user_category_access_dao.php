<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.bol
 * @since 1.0
 */
class FRMSHASTA_BOL_UserCategoryAccessDao extends OW_BaseDao
{
    private static $classInstance;

    const ACCESS_GRANTED = 'access_granted';
    const ACCESS_DENIED = 'access_denied';
    /***
     * @return FRMSHASTA_BOL_UserCategoryAccessDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMSHASTA_BOL_UserCategoryAccess';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_user_category_access';
    }

    public function findUserIdsGrantedAccessToCategory($categoryId)
    {
        $query='SELECT `userId` from '.$this->getTableName().' WHERE `categoryId`=:categoryId AND `access`="'.self::ACCESS_GRANTED.'"';
        return $this->dbo->queryForColumnList($query,array('categoryId'=>$categoryId));
    }

    public function findUserIdsDeniedAccessToCategory($categoryId)
    {
        $query='SELECT `userId` from '.$this->getTableName().' WHERE `categoryId`=:categoryId AND `access`="'.self::ACCESS_DENIED.'"';
        return $this->dbo->queryForColumnList($query,array('categoryId'=>$categoryId));
    }

    public function findAccessInfoByUserIdAndCategoryId($userId,$categoryId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId',$userId);
        $example->andFieldEqual('categoryId',$categoryId);
        return $this->findObjectByExample($example);
    }
    public function UpdateUserAccessInfo($userId,$categoryId,$access)
    {
        $accessDto=$this->findAccessInfoByUserIdAndCategoryId($userId,$categoryId);
        if(!isset($accessDto)) {
            $accessDto = new FRMSHASTA_BOL_UserCategoryAccess();
            $accessDto->userId=$userId;
            $accessDto->categoryId=$categoryId;
        }
        $accessDto->access=$access;
        $this->save($accessDto);
    }

    public function deleteUsersAccessInfo()
    {
        $this->clearCache();
        $this->dbo->delete('TRUNCATE TABLE ' . $this->getTableName());
    }

    public function findByUser($userId = null) {
        if ($userId == null) {
            return null;
        }
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findListByExample($example);
    }
}
