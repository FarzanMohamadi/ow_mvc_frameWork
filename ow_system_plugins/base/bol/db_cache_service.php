<?php
/**
 * Database cache service
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_DbCacheService implements OW_CacheService
{
    /**
     * 
     * @var BOL_DbCacheDao
     */
    private $dbCacheDao;

    private function __construct()
    {
        $this->dbCacheDao = BOL_DbCacheDao::getInstance();
    }
    /**
     * Class instance
     *
     * @var BOL_DbCacheService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_DbCacheService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function get( $key )
    {
        $dto = $this->dbCacheDao->findByName($key);

        if ($dto !== null && $dto->expireStamp < time()) {
            $this->dbCacheDao->delete($dto);
            return false;
        }

        return $dto === null ? false : $dto->value;
    }

    public function set( $key, $var, $lifeTime = 0 )
    {
        $dto = $this->dbCacheDao->findByName($key);

        if ( $dto === null )
        {
            $dto = new BOL_DbCache();
            $dto->name = $key;
        }

        $dto->expireStamp = empty($lifeTime) ? PHP_INT_MAX : time() + $lifeTime;
        $dto->value = $var;

        $this->dbCacheDao->save($dto);
    }

    public function deleteExpiredList()
    {
        $this->dbCacheDao->deleteExpiredList();
    }
}