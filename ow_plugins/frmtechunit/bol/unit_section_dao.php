<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/1/18
 * Time: 11:41 AM
 */

class FRMTECHUNIT_BOL_UnitSectionDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_BOL_UnitSectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_BOL_UnitSectionDao
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtechunit_unit_section';
    }

    public function getDtoClassName()
    {
        return 'FRMTECHUNIT_BOL_UnitSection';
    }

    public function getUnitSections($unitId,$sectionId = null){
        $example = new OW_Example();
        $example->andFieldEqual('unitId',$unitId);
        if(isset($sectionId))
            $example->andFieldEqual('sectionId',$sectionId);
        return $this->findListByExample($example);
    }

    public function deleteUnitSectionsByUnit($unitId){
        $example = new OW_Example();
        $example->andFieldEqual('unitId',$unitId);
        return $this->deleteByExample($example);
    }

    public function deleteUnitSectionsBySection($sectionId){
        $example = new OW_Example();
        $example->andFieldEqual('sectionId',$sectionId);
        return $this->deleteByExample($example);
    }
}