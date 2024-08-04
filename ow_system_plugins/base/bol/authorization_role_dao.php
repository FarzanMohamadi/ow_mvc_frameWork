<?php
/**
 * Data Access Object for `base_authorization_role` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationRoleDao extends OW_BaseDao
{
    const GUEST = 'guest';

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
     * @var BOL_AuthorizationRoleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationRoleDao
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
        return 'BOL_AuthorizationRole';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_authorization_role';
    }

    public function getGuestRoleId()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('name', self::GUEST);

        return $this->findIdByExample($ex);
    }

    public function findNonGuestRoleList()
    {
        $ex = new OW_Example();
        $ex->andFieldNotEqual('id', $this->getGuestRoleId())
            ->setOrder('sortOrder ASC');

        return $this->findListByExample($ex);
    }

    public function findDefault()
    {
        $query = "SELECT * FROM `{$this->getTableName()}` WHERE `sortOrder` != 0 ORDER BY `sortOrder` ASC limit 1";

        return $this->dbo->queryForObject($query, $this->getDtoClassName());
    }

    public function findMaxOrder()
    {
        $query = "SELECT MAX(`sortOrder`) FROM `{$this->getTableName()}`";

        return $this->dbo->queryForColumn($query);
    }

    public function findUserRoleList( $userId )
    {
        $query = "SELECT `r`.* FROM `{$this->getTableName()}` as `r`
		INNER JOIN `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` as `ur`
			ON(`r`.`id` = `ur`.`roleId`)
		WHERE `ur`.`userId` = ? ORDER BY `sortOrder` ASC
		";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($userId));
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION));
    }

    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
    	$example = new OW_Example();
    	$example->setOrder('`sortOrder` ASC');
    	
        return $this->findListByExample($example, 3600 * 24, array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    public function findRoleByName( $roleName )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('name', $roleName);

        return $this->findListByExample($example);
    }
}