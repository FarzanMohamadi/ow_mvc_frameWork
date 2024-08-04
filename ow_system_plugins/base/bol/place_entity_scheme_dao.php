<?php
/**
 * Data Access Object for `base_place_entity_scheme` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PlaceEntitySchemeDao extends OW_BaseDao
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
     * @var BOL_ComponentSettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentSettingDao
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
        return 'BOL_PlaceEntityScheme';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_place_entity_scheme';
    }

    /**
     *
     * @param int $placeId
     * @param int $entityId
     * @return BOL_PlaceScheme
     */
    public function findPlaceScheme( $placeId, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('placeId', $placeId);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }
}