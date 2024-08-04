<?php
/**
 * Data Access Object for `base_sitemap` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.8.4
 */
class BOL_SitemapDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var BOL_SitemapDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SitemapDao
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
        return 'BOL_Sitemap';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_sitemap';
    }

    /**
     * Truncate table
     *
     * @return void
     */
    public function truncate()
    {
        $this->dbo->query('TRUNCATE TABLE  `' . $this->getTableName() . '`');
    }

    /**
     * Find url list
     *
     * @param integer $count
     * @return array
     */
    public function findUrlList( $count )
    {
        $query = "
			SELECT
			    `id`,
			    `url`,
			    `entityType`
			FROM
			    `{$this->getTableName()}`
			ORDER BY
			    `id`
			LIMIT
			    ?";

        return $this->dbo->queryForList($query, array(
            $count
        ));
    }
}
