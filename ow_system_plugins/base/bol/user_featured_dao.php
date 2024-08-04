<?php
/**
 * Singleton. 'Featured User' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserFeaturedDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var BOL_UserFeaturedDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserFeaturedDao
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
        return 'BOL_UserFeatured';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_featured';
    }

    public function findByUserId( $id )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('userId', $id);

        return $this->findObjectByExample($ex);
    }

    public function deleteByUserId( $userId )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('userId', $userId);

        $this->deleteByExample($ex);
    }
}