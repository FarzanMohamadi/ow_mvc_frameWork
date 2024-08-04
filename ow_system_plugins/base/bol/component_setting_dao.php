<?php
/**
 * Data Access Object for `base_component_setting` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentSettingDao extends OW_BaseDao
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
     * @var BOL_ComponentSettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentSettingDao
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
        return 'BOL_ComponentSetting';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_setting';
    }

    public function findSettingList( $componentPlaceUniqName, array $settingNames = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        if ( !empty($settingNames) )
        {
            $example->andFieldInArray('name', $settingNames);
        }

        return $this->findListByExample($example);
    }

    public function findListByComponentUniqNameList( array $componentPlaceUniqNameList )
    {
        if ( empty($componentPlaceUniqNameList) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('componentPlaceUniqName', $componentPlaceUniqNameList);

        return $this->findListByExample($example);
    }

    public function cloneSettingList( $sourceComponentPlaceUniqName, $destComponentPlaceUniqName )
    {
        $sourceSettings = $this->findSettingList($sourceComponentPlaceUniqName);

        foreach ( $sourceSettings as $setting )
        {
            $setting->id = null;
            $setting->componentPlaceUniqName = $destComponentPlaceUniqName;
            $this->save($setting);
        }
    }

    /**
     *
     * @param string $componentPlaceUniqName
     * @param string $name
     * @param string $value
     */
    public function saveSetting( $componentPlaceUniqName, $name, $value )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $componentSettingDto = $this->findObjectByExample($example);
        if ( !$componentSettingDto )
        {
            $componentSettingDto = new BOL_ComponentSetting();
            $componentSettingDto->name = $name;
        }

        $componentSettingDto->componentPlaceUniqName = $componentPlaceUniqName;
        $componentSettingDto->setValue($value);

        return $this->save($componentSettingDto);
    }

    public function deleteList( $componentPlaceUniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);

        return $this->deleteByExample($example);
    }

    public function findSettingByComponentPlaceUniqNameAndName( $componentPlaceUniqName, $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $example->andFieldEqual('name', $name);

        return $this->findObjectByExample($example);
    }
}