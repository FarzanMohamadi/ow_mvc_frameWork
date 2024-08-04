<?php
/**
 * Singleton. Language Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageDao extends OW_BaseDao
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
     * @var BOL_LanguageDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageDao
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Language';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_language';
    }

    /**
     * Enter description here...
     *
     * @param string $tag
     * @return BOL_Language
     */
    public function findByTag( $tag )
    {
        $example = new OW_Example();
        $example->andFieldEqual('tag', trim($tag));

        return $this->findObjectByExample($example);
    }

    public function findMaxOrder()
    {
        return $this->dbo->queryForColumn('SELECT MAX(`order`) FROM ' . $this->getTableName());
    }

    public function getCurrent()
    {
        $ex = new OW_Example();

        $ex->setOrder('`order` ASC')->setLimitClause(0, 1);

        return $this->findObjectByExample($ex);
    }

    public function countActiveLanguages()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('status', 'active');

        return $this->countByExample($ex);
    }

    public function findActiveList()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('status', 'active');

        return $this->findListByExample($ex);
    }
}