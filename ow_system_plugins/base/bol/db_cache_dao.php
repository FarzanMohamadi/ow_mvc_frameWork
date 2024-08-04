<?php
/**
 * Data Access Object for `base_db_cache` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_DbCacheDao extends OW_BaseDao
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
     * @var BOL_DbCacheDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_DbCacheDao
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
        return 'BOL_DbCache';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_db_cache';
    }

    /**
     * 
     * @param string $name
     * @return BOL_DbCache
     */
    public function findByName( $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);

        return $this->findObjectByExample($example);
    }

    public function deleteExpiredList()
    {
        $example = new OW_Example();
        $example->andFieldLessThan('expireStamp', time());

        $this->deleteByExample($example);
    }
}