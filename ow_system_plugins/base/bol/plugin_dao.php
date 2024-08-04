<?php
/**
 * Data Access Object for `plugin` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PluginDao extends OW_BaseDao
{
    const ID = "id";
    const TITLE = "title";
    const DESCRIPTION = "description";
    const MODULE = "module";
    const KEY = "key";
    const IS_SYSTEM = "isSystem";
    const IS_ACTIVE = "isActive";
    const VERSION = "version";
    const UPDATE = "update";
    const LICENSE_KEY = "licenseKey";
    const LICENSE_CHECK_STAMP = "licenseCheckTimestamp";
    const UPDATE_VAL_UP_TO_DATE = 0;
    const UPDATE_VAL_UPDATE = 1;
    const UPDATE_VAL_MANUAL_UPDATE = 2;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_PluginDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PluginDao
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
        return "BOL_Plugin";
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . "base_plugin";
    }

    /**
     * Returns all active plugins.
     *
     * @return array<BOL_Plugin>
     */
    public function findActivePlugins()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_ACTIVE, true);
        return $this->findListByExample($example);
    }

    /**
     * Finds plugin by key.
     * 
     * @param string $key
     * @return BOL_Plugin
     */
    public function findPluginByKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    /**
     * Deletes plugin entry by key.
     * 
     * @param string $key
     */
    public function deletePluginKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        $this->deleteByExample($example);
    }

    /**
     * Returns all regular (not system plugins).
     * 
     * @return array<BOL_Plugin>
     */
    public function findRegularPlugins()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_SYSTEM, 0);

        return $this->findListByExample($example);
    }

    public function findPluginsForUpdateCount()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::UPDATE, self::UPDATE_VAL_UPDATE);

        return $this->countByExample($example);
    }

    /**
     * @return BOL_Plugin
     */
    public function findPluginForManualUpdate()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::UPDATE, self::UPDATE_VAL_MANUAL_UPDATE);
        $example->andFieldEqual(self::IS_ACTIVE, 1);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * @return array 
     */
    public function findPluginsWithInvalidLicense()
    {
        $example = new OW_Example();
        $example->andFieldGreaterThan(self::LICENSE_CHECK_STAMP, 0);

        return $this->findListByExample($example);
    }

    /**
     * Deletes admin settings route field for a plugin.
     *
     * @param string $key
     */
    public function deletePluginAdminSettingsRoute($key)
    {
        $plugin = $this->findPluginByKey($key);
        $plugin->setAdminSettingsRoute(null);
        $this->save($plugin);
        $this->clearCache();
    }
}
