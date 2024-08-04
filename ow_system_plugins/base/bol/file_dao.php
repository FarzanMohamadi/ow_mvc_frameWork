<?php
/**
 * Data Access Object for `base_file` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 */
class BOL_FileDao extends OW_BaseDao
{
    CONST CACHE_TAG_FILE_LIST = 'photo.list';
    CONST FILE_ENTITY_TYPE = 'file';

    /**
     * Singleton instance.
     *
     * @var BOL_FileDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class.
     *
     * @return BOL_FileDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
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
        return 'BOL_File';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_file';
    }

    /**
     * Counts files uploaded by a user
     *
     * @param int $userId
     * @return int
     */
    public function countUserFiles( $userId )
    {
        if ( !$userId )
            return false;

        $query = "SELECT COUNT(`t`.`id`) FROM `" . $this->getTableName() . "` as t WHERE `t`.`userId` = :user";

        return $this->dbo->queryForColumn($query, array('user' => $userId));
    }

}
