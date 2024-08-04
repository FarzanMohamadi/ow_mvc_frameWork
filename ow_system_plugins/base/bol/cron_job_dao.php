<?php
/**
 * Data Access Object for `base_cron_job` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_CronJobDao extends OW_BaseDao
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
     * @var BOL_CronJobDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CronJobDao
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
        return 'BOL_CronJob';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_cron_job';
    }

    public function batchSave( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
    }

    /**
     * @param $methodName
     * @return BOL_CronJob
     */
    public function findByMethodName($methodName){
        $ex = new OW_Example();
        $ex->andFieldEqual('methodName', $methodName);
        return $this->findObjectByExample($ex);
    }

    public function deleteJobsByPluginKey($pluginKey){
        $q = strtoupper($pluginKey).'_Cron';
        $query="DELETE FROM `". $this->getTableName(). "` WHERE `methodName` LIKE '".$q."%'";
        $this->dbo->query($query);
    }
}