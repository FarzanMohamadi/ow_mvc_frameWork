<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.bol
 * @since 1.6.1
 */
class PHOTO_BOL_SearchDataDao extends OW_BaseDao
{
    CONST ENTITY_TYPE_ID = 'entityTypeId';
    CONST ENTITY_ID = 'entityId';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_search_data';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchData';
    }
    
    public function getDataForIndexing( $limit )
    {
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            LIMIT :limit';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('limit' => $limit));
    }
    
    public function deleteDataItem( $entityTypeId, $entityId )
    {
        if ( empty($entityTypeId) || empty($entityId) )
        {
            return FALSE;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE_ID, $entityTypeId);
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        
        return $this->deleteByExample($example);
    }
}
