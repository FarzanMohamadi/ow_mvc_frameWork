<?php
/**
 * Data Access Object for `base_place` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PlaceDao extends OW_BaseDao
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
     * @var BOL_PlaceDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PlaceDao
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
        return 'BOL_Place';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_place';
    }

    /**
     * @return BOL_Place
     */
    public function findByName( $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        return $this->findObjectByExample($example);
    }
}