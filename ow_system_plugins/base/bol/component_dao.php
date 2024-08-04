<?php
/**
 * Data Access Object for `base_component` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentDao extends OW_BaseDao
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
     * @var BOL_ComponentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentDao
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
        return 'BOL_Component';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component';
    }

    public function findComponentsByIds( array $componentIds ) //TODO findListByIdList
    {
        return $this->findByIdList($componentIds);
    }

    public function findClonable()
    {
        $example = new OW_Example();
        $example->andFieldEqual('clonable', 1);
        return $this->findListByExample($example);
    }

    /**
     * 
     * @param $className
     * @return BOL_Component
     */
    public function findByClassName( $className )
    {
        $example = new OW_Example();
        $example->andFieldEqual('className', $className);

        return $this->findObjectByExample($example);
    }

     /**
     *
     * @param $pluginKey
     * @return BOL_Component
     */
    public function findByPluginKey( $pluginKey )
    {
        if ( empty($pluginKey) )
        {
            return array();
        }
        
        $example = new OW_Example();
        $example->andFieldLike('className', mb_strtoupper( preg_replace('/[_]/', '\_', $pluginKey) ) . '\_%');

        return $this->findListByExample($example);
    }
}