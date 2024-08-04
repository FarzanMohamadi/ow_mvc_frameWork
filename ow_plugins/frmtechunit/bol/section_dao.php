<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/1/18
 * Time: 11:41 AM
 */

class FRMTECHUNIT_BOL_SectionDao extends OW_BaseDao
{

    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_BOL_SectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_BOL_SectionDao
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
        return OW_DB_PREFIX . 'frmtechunit_section';
    }

    public function getDtoClassName()
    {
        return 'FRMTECHUNIT_BOL_Section';
    }

    public function findByName($name)
    {
        $example = new OW_Example();
        $example->andFieldEqual('name',$name);
        return $this->findObjectByExample($example);
    }

    public function all($offset,$count)
    {
        $example = new OW_Example();
        $example->setLimitClause($offset,$count);
        return $this->findListByExample($example);
    }
}