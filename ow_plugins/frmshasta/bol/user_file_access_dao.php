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
class FRMSHASTA_BOL_UserFileAccessDao extends OW_BaseDao
{
    private static $classInstance;

    const ACCESS_GRANTED = 'access_granted';
    const ACCESS_DENIED = 'access_denied';

    /***
     * @return FRMSHASTA_BOL_UserFileAccessDao
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
        return 'FRMSHASTA_BOL_UserFileAccess';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_user_file_access';
    }

    public function findUserIdsGrantedAccessToFile($fileId)
    {
       $query='SELECT `userId` from '.$this->getTableName().' WHERE `fileId`=:fileId AND `access`="'.self::ACCESS_GRANTED.'"';
       return $this->dbo->queryForColumnList($query,array('fileId'=>$fileId));
    }

    public function findUserIdsDeniedAccessToFile($fileId)
    {
        $query='SELECT `userId` from '.$this->getTableName().' WHERE `fileId`=:fileId AND `access`="'.self::ACCESS_DENIED.'"';
        return $this->dbo->queryForColumnList($query,array('fileId'=>$fileId));
    }

    public function findAccessInfoByUserIdAndFileId($userId,$fileId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId',$userId);
        $example->andFieldEqual('fileId',$fileId);
        return $this->findObjectByExample($example);
    }
    public function UpdateUserAccessInfo($userId,$fileId,$access)
    {
        $accessDto=$this->findAccessInfoByUserIdAndFileId($userId,$fileId);
        if(!isset($accessDto)) {
            $accessDto = new FRMSHASTA_BOL_UserFileAccess();
            $accessDto->userId=$userId;
            $accessDto->fileId=$fileId;
        }
        $accessDto->access=$access;
        $this->save($accessDto);
    }

    public function deleteUserAccessInfo($userId,$fileId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId',$userId);
        $example->andFieldEqual('fileId',$fileId);
        return $this->deleteByExample($example);
    }
}
