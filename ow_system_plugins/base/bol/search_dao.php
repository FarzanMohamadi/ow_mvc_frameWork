<?php
/**
 * Data Access Object for `base_search` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchDao extends OW_BaseDao
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
     * @var BOL_SearchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_Search
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
        return 'BOL_Search';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_search';
    }

    public function findExpireSearchId( $limit )
    {
        $expirationTime = 60 * 60 * 24; // 1 day
        $query = "SELECT id FROM " . $this->getTableName() . " WHERE (" . $this->dbo->escapeValue(time()) . " - timeStamp) > " . $this->dbo->escapeValue($expirationTime) . " LIMIT :count";

        return $this->dbo->queryForColumnList($query, array('count' => $limit));
    }
}