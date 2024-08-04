<?php
/**
 * Data Access Object for `base_component_place` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentPlaceDao extends OW_BaseDao
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
     * @var BOL_ComponentPlaceDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentPlaceDao
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
        return 'BOL_ComponentPlace';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_place';
    }

    public function cloneComponent( $uniqName )
    {
        $component = $this->findByUniqName($uniqName);
        $component->id = 0;
        $component->clone = 1;
        $component->uniqName = FRMSecurityProvider::generateUniqueId('admin-');
        $this->save($component);

        return $component;
    }

    public function findByUniqName( $uniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('uniqName', $uniqName);

        return $this->findObjectByExample($example);
    }

    public function deleteByUniqName( $uniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('uniqName', $uniqName);

        return $this->deleteByExample($example);
    }

    public function deleteByComponentId( $componentId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentId', $componentId);

        return $this->deleteByExample($example);
    }

    public function findListByComponentId( $componentId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentId', $componentId);

        return $this->findListByExample($example);
    }

    public function findComponentList( $placeId )
    {
        $componentDao = BOL_ComponentDao::getInstance();

        $query =
            'SELECT `c`.*, `cp`.`id`, `cp`.`componentId`, `cp`.`clone`, `cp`.`uniqName` FROM `' . $this->getTableName() . '` AS `cp`
    			INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
    				WHERE `cp`.`placeId`=?';

        return $this->dbo->queryForList($query, array($placeId));
    }

    public function findListBySection( $placeId, $section )
    {
        $componentDao = BOL_ComponentDao::getInstance();
        $componentSectionDao = BOL_ComponentPositionDao::getInstance();

        $query = '
            SELECT `c`.*, `cp`.`id`, `cp`.`componentId`, `cp`.`clone`, `cp`.`uniqName`, `p`.`order`  FROM `' . $this->getTableName() . '` AS `cp`
                INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
                INNER JOIN `' . $componentSectionDao->getTableName() . '` AS `p` 
                    ON `p`.`componentPlaceUniqName` = `cp`.`uniqName`
                    WHERE `cp`.`placeId`=? AND `p`.`section`=?
        ';

        return $this->dbo->queryForList($query, array($placeId, $section));
    }
}