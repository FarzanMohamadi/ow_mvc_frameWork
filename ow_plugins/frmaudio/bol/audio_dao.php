<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio.bol
 * @since 1.0
 */

class FRMAUDIO_BOL_AudioDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMAUDIO_BOL_AudioDao
     */
    private static $classInstance;

    /***
     * @return FRMAUDIO_BOL_AudioDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /***
     * @return string
     */
    public function getDtoClassName()
    {
        return 'FRMAUDIO_BOL_Audio';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frm_audio';
    }

    /***
     * @param $userId
     * @return array
     */
    public function findAudiosByUserId($userId)
    {
        $ex=new OW_Example();
        $ex->andFieldEqual('userId',$userId);
        $ex->andFieldEqual('valid',true);
        return  $this->findListByExample($ex);
    }

    /***
     * @param $id
     * @return FRMAUDIO_BOL_Audio
     */
    public function findAudioById($id)
    {
        $ex=new OW_Example();
        $ex->andFieldEqual('id',$id);
        return  $this->findObjectByExample($ex);
    }

    /***
     * @param $objectId
     * @param $objectType
     */
    public function deleteByObjectIdAndType($objectId, $objectType){
        $ex=new OW_Example();
        $ex->andFieldEqual('object_id',$objectId);
        $ex->andFieldEqual('object_type',$objectType);
        $this->deleteByExample($ex);
    }

    /***
     * @param $object_id
     * @param $object_type
     * @return mixed
     */
    public function findAudiosByObject($object_id, $object_type){
        $ex=new OW_Example();
        $ex->andFieldEqual('object_id',$object_id);
        $ex->andFieldEqual('object_type',$object_type);
        return  $this->findObjectByExample($ex);
    }

    /***
     * @param $userId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findListOrderedByDate($userId, $first = 0, $count = 10)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('valid', true);
        $example->setLimitClause($first, $count);
        $example->setOrder("`addDateTime` DESC");
        return $this->findListByExample($example);
    }

    /***
     * @param $expiredTime
     */
    public function removeTempAudios($expiredTime){
        $example = new OW_Example();
        $example->andFieldLessOrEqual('addDateTime',time() - $expiredTime);
        $example->andFieldEqual('valid', false);
        $this->deleteByExample($example);
    }
}