<?php
/**
 * Data Access Object for `base_authorization_moderator` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationModeratorDao extends OW_BaseDao
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
     * @var BOL_AuthorizationModeratorDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationModeratorDao
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
        return 'BOL_AuthorizationModerator';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_authorization_moderator';
    }

    public function getIdByUserId( $userId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->findIdByExample($ex);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION));
    }

    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        $example = new OW_Example();
        $example->setOrder('id');

        return $this->findListByExample($example, 3600 * 24, array(
            BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION,
            OW_CacheManager::TAG_OPTION_INSTANT_LOAD
        ));
    }
}