<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmoghat.bol
 * @since 1.0
 */
class FRMNEWSFEEDPIN_BOL_PinDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMNEWSFEEDPIN_BOL_Pin';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmnewsfeedpin_pin';
    }

    public function findMostRecentPins($maxNum){
        $example = new OW_Example();
        $example->setLimitClause(0,$maxNum);
        $example->setOrder('createDate desc');
        return $this->findListByExample($example);
    }

    public function findByEntityIdAndEntityType($entityId,$entityType){
        $example = new OW_Example();
        $example->andFieldEqual('entityId',(int) $entityId);
        $example->andFieldEqual('entityType',$entityType);
        return $this->findObjectByExample($example);
    }

    public function findByEntityIdsAndEntityTypes($entityIds,$entityTypes){
        if (!is_array($entityIds) || empty($entityIds) || !is_array($entityTypes) || empty($entityTypes)) {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('entityId', $entityIds);
        $example->andFieldInArray('entityType', $entityTypes);
        return $this->findListByExample($example);
    }

    public function deleteByEntityIdAndEntityType($entityId,$entityType){
        $example = new OW_Example();
        $example->andFieldEqual('entityId',(int) $entityId);
        $example->andFieldEqual('entityType',$entityType);
        $this->deleteByExample($example);
    }
}