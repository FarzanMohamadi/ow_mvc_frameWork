<?php
/**
 * Data Access Object for `base_config` table.
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ConfigDao extends OW_BaseDao
{
    const KEY = 'key';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const VALUE = 'value';

    /**
     * Singleton instance.
     *
     * @var BOL_ConfigDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ConfigDao
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

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Config';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        $db_prefix = defined('OW_DB_PREFIX')?OW_DB_PREFIX:'';
        return $db_prefix . 'base_config';
    }

    /**
     * Finds config item by key and name.
     *
     * @param string $key
     * @param string $name
     * @return BOL_Config
     */
    public function findConfig( $key, $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);
        $example->andFieldEqual(self::NAME, $name);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds confids list by plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function findConfigsList( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findListByExample($example);
    }

    /**
     * Removes config by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function removeConfig( $key, $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, trim($key));
        $example->andFieldEqual(self::NAME, trim($name));

        $this->deleteByExample($example);
    }

    /**
     * Removes configs by provided plugin key.
     * 
     * @param string $key
     */
    public function removeConfigs( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, trim($key));

        $this->deleteByExample($example);
    }
}