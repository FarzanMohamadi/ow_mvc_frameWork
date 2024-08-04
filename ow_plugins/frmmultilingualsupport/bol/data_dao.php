<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmultilingualsupport.bol
 * @since 1.0
 */
class FRMMULTILINGUALSUPPORT_BOL_DataDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'FRMMULTILINGUALSUPPORT_BOL_Data';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmmultilingualsupport_data';
    }


    /***
     * @param $entityId
     * @param $entityType
     * @return FRMMULTILINGUALSUPPORT_BOL_Data
     */
    public function findEnDataByEntityIdAndType($entityId,$entityType){
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $entityId);
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityLanguage', 'en');
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $entityId
     * @param $entityType
     * @return FRMMULTILINGUALSUPPORT_BOL_Data
     */
    public function findFaDataByEntityIdAndType($entityId,$entityType){
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $entityId);
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityLanguage', 'fa-IR');
        return $this->findObjectByExample($ex);
    }
    /***
     * @param $entityId
     * @param $entityType
     * @param $data
     * @return FRMMULTILINGUALSUPPORT_BOL_Data
     */
    public function saveData($entityId, $entityType,$entityLanguage, $entityData){
        $enData = null;
        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $entityId);
        $ex->andFieldEqual('entityType', $entityType);
        $ex->andFieldEqual('entityLanguage', $entityLanguage);
        $enData = $this->findObjectByExample($ex);

        if($enData == null){
            $enData = new FRMMULTILINGUALSUPPORT_BOL_Data();
        }
        $enData->entityId = $entityId;
        $enData->entityType = $entityType;
        $enData->entityLanguage = $entityLanguage;
        $enData->entityData = $entityData;
        $this->save($enData);
        return $enData;
    }
}
