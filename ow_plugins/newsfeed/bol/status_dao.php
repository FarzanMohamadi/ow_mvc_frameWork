<?php
/**
 * Data access Object for `newsfeed_status` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_StatusDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_StatusDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_StatusDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'NEWSFEED_BOL_Status';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'newsfeed_status';
    }
    
    public function saveStatus( $feedType, $feedId, $status )
    {
        $dto = $this->removeStatus($feedType, $feedId);
        
        $dto = new NEWSFEED_BOL_Status();
        $dto->feedType = $feedType;
        $dto->feedId = $feedId;
        $dto->status = $status;
        $dto->timeStamp = time();
        
        $this->save($dto);
        
        return $dto;
    }
    
    /**
     * 
     * @param $feedType
     * @param $feedId
     * @return NEWSFEED_BOL_Status
     */
    public function findStatus( $feedType, $feedId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        return $this->findObjectByExample($example);
    }
    
    public function removeStatus( $feedType, $feedId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        return $this->deleteByExample($example);
    }
}