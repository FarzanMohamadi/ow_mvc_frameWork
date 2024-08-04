<?php
/**
 * Singleton. 'Language Prefix' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguagePrefixDao extends OW_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var BOL_LanguagePrefixDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguagePrefixDao
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
        return 'BOL_LanguagePrefix';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_language_prefix';
    }

    public function findAllWithKeyCount()
    {
        return $this->dbo->queryForList(
            'SELECT `p`.*, COUNT(`k`.`id`) as keyCount FROM ' . $this->getTableName()
            . ' AS `p` LEFT JOIN ' . BOL_LanguageKeyDao::getInstance()->getTableName()
            . ' AS `k` ON `p`.`id` = `k`.`prefixId` GROUP BY `k`.`prefixId` '
        );
    }

    public function findPrefixId( $prefix )
    {
        $query = "SELECT `id` FROM `" . $this->getTableName() . "` WHERE `prefix`=?";

        return $this->dbo->queryForColumn($query, array($prefix));
    }

    public function findByPrefix( $prefix )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('prefix', $prefix);

        return $this->findObjectByExample($ex);
    }

    public function findByPrefixes($prefixes )
    {
        if (empty($prefixes)) {
            return array();
        }
        $ex = new OW_Example();

        $ex->andFieldInArray('prefix', $prefixes);

        return $this->findListByExample($ex);
    }
}
