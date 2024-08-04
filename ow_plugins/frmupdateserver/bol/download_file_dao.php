<?php
/**
 * FRM Update Server
 */

/**
 * Data Access Object for `DownloadFile` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
class FRMUPDATESERVER_BOL_DownloadFileDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMUPDATESERVER_BOL_DownloadFileDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_DownloadFileDao
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
        return 'FRMUPDATESERVER_BOL_DownloadFile';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmupdateserver_download_file';
    }

}