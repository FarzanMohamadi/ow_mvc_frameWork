<?php
/**
 * Data Access Object for `login_cookie` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LoginCookieDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const COOKIE = 'cookie';

    /**
     * Singleton instance.
     *
     * @var BOL_LoginCookieDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_LoginCookieDao
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
        return 'BOL_LoginCookie';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_login_cookie';
    }

    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    public function findAllCookies()
    {
        $sql = 'SELECT `cookie` FROM ' . $this->getTableName();
        return $this->dbo->queryForColumnList($sql, array());
    }
    /**
     * @param string $cookie
     * @return BOL_LoginCookie
     */
    public function findByCookie( $cookie )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::COOKIE, $cookie);

        return $this->findObjectByExample($example);
    }

    /**
     * @param $cookies
     * @return mixed|null
     */
    public function deleteByCookies( $cookies )
    {
        if(sizeof($cookies) == 0){
            return null;
        }
        $example = new OW_Example();
        $example->andFieldInArray(self::COOKIE, $cookies);

        return $this->deleteByExample($example);
    }

    /**
     * @param $cookie
     * @return mixed|null
     */
    public function deleteByCookie( $cookie )
    {
        if(!isset($cookie)){
            return null;
        }
        $example = new OW_Example();
        $example->andFieldEqual(self::COOKIE, $cookie);

        return $this->deleteByExample($example);
    }
}