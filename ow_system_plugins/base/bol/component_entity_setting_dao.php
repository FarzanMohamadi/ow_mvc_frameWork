<?php
/**
 * Data Access Object for `base_component_entity_setting` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentEntitySettingDao extends OW_BaseDao
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
     * @var BOL_ComponentEntitySettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentEntitySettingDao
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
        return 'BOL_ComponentEntitySetting';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_entity_setting';
    }

    public function findAllEntitySettingList( $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);

        return $this->findListByExample($example);
    }

    public function findSettingList( $componentPlaceUniqName, $entityId, $settingNames = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $example->andFieldEqual('entityId', $entityId);
        if ( !empty($settingNames) )
        {
            $example->andFieldInArray('name', $settingNames);
        }

        return $this->findListByExample($example);
    }

    public function findListByComponentUniqNameList( array $componentPlaceUniqNameList, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldInArray('componentPlaceUniqName', $componentPlaceUniqNameList);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findListByExample($example);
    }

    /**
     *
     * @param string $componentPlaceUniqName
     * @param string $name
     * @param string $value
     */
    public function saveSetting( $componentPlaceUniqName, $entityId, $name, $value )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $example->andFieldEqual('entityId', $entityId);
        $componentSettingDto = $this->findObjectByExample($example);

        if ( !$componentSettingDto )
        {
            $componentSettingDto = new BOL_ComponentEntitySetting();
            $componentSettingDto->name = $name;
            $componentSettingDto->entityId = $entityId;
            $componentSettingDto->componentPlaceUniqName = $componentPlaceUniqName;
        }
        
        $componentSettingDto->setValue($value);

        return $this->save($componentSettingDto);
    }

    public function deleteList( $componentPlaceUniqName, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $example->andFieldEqual('entityId', $entityId);

        return $this->deleteByExample($example);
    }

    public function deleteAllByUniqName( $componentPlaceUniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);

        return $this->deleteByExample($example);
    }

    public function deleteByUniqNameList( $entityId, $uniqNameList = array() )
    {
        $entityId = (int) $entityId;
        if ( !$entityId )
        {
            throw new InvalidArgumentException('Invalid argument $entityId');
        }

        if ( empty($uniqNameList) )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldInArray('componentPlaceUniqName', $uniqNameList);

        return $this->deleteByExample($example);
    }
}