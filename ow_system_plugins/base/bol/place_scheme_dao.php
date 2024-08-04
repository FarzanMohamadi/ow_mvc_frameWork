<?php
/**
 * Data Access Object for `base_place_scheme` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PlaceSchemeDao extends OW_BaseDao
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
        return 'BOL_PlaceScheme';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_place_scheme';
    }

    /**
     *
     * @param int $placeId
     * @return BOL_PlaceScheme
     */
    public function findPlaceScheme( $placeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('placeId', $placeId);

        return $this->findObjectByExample($example);
    }
}