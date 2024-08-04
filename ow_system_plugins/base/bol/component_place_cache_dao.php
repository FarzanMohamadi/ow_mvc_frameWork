<?php
/**
 * Data Access Object for `base_component_place_cache` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentPlaceCacheDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_ComponentPlaceCacheDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentPlaceCacheDao
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
        return 'BOL_ComponentPlaceCache';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_place_cache';
    }

    /**
     * 
     * @param $placeId
     * @param $entityId
     * @return OW_Example
     */
    private function getFilterExample( $placeId, $entityId = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('placeId', $placeId);

        if ( empty($entityId) )
        {
            $example->andFieldEqual('entityId', 0);
        }
        else
        {
            $example->andFieldEqual('entityId', $entityId);
        }

        return $example;
    }

    public function findCache( $placeId, $entityId = null )
    {
        $example = $this->getFilterExample($placeId, $entityId);

        return $this->findObjectByExample($example);
    }

    public function deleteCache( $placeId, $entityId = null )
    {
        $example = $this->getFilterExample($placeId, $entityId);

        return $this->deleteByExample($example);
    }

    public function deleteAllCache( $placeId = null )
    {
        if ( empty($placeId) )
        {
            $this->dbo->query('TRUNCATE TABLE `' . $this->getTableName() . '`');

            return;
        }

        $example = new OW_Example();
        $example->andFieldEqual('placeId', $placeId);

        return $this->deleteByExample($example);
    }
}