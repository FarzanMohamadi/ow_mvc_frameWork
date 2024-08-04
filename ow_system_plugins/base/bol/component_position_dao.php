<?php
/**
 * Data Access Object for `base_component_position` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */

class BOL_ComponentPositionDao extends OW_BaseDao
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
     * @var BOL_ComponentAdminSectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentPositionDao
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
        return 'BOL_ComponentPosition';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_position';
    }
    

    public function findAllPositionList( $placeId )
    {
    	$componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
    	   	
    	$query = '
    		SELECT `p`.*  FROM `' . $this->getTableName() . '` AS `p`
    			INNER JOIN `' . $componentPlaceDao->getTableName() . '` AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName`
    				WHERE `c`.`placeId`=? 
    	';
    	
    	return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array( $placeId ));
    }
    
    public function findAllPositionIds( $placeId )
    {
    	$componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
    	   	
    	$query = '
    		SELECT `p`.`id` FROM `' . $this->getTableName() . '` AS `p`
    			INNER JOIN `' . $componentPlaceDao->getTableName() . '` AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName`
    				WHERE `c`.`placeId`=? 
    	';
    	
    	return $this->dbo->queryForColumnList($query, array( $placeId ));
    }
    
    public function findSectionPositionList( $placeId, $section)
    {
    	$componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
    	   	
    	$query = '
    		SELECT `p`.* FROM `' . $this->getTableName() . '` AS `p`
    			INNER JOIN `' . $componentPlaceDao->getTableName() . '` AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName`
    				WHERE `c`.`placeId`=? AND `p`.`section`=? 
    	';
    	
    	return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array( $placeId, $section ));
    }
    
    public function findSectionPositionIdList( $placeId, $section)
    {
    	$componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
    	   	
    	$query = '
    		SELECT `p`.`id` FROM `' . $this->getTableName() . '` AS `p`
    			INNER JOIN `' . $componentPlaceDao->getTableName() . '` AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName`
    				WHERE `c`.`placeId`=? AND `p`.`section`=? 
    	';
    	
    	return $this->dbo->queryForColumnList($query, array( $placeId, $section ));
    }
    
    public function deleteByUniqNameList($uniqNameList)
    {
        if ( empty( $uniqNameList ) )
        {
            return 0;
        }
        
        $example = new OW_Example();
        $example->andFieldInArray('componentPlaceUniqName', $uniqNameList);
        
        return $this->deleteByExample($example);
    }
    
    public function deleteByUniqName( $uniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $uniqName);
        
        return $this->deleteByExample($example);
    }
    
    /**
     * 
     * @param $uniqName
     * @return BOL_ComponentPosition
     */
    public function findByUniqName( $uniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $uniqName);
        
        return $this->findObjectByExample($example);
    }
}