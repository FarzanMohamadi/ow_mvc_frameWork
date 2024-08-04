<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/1/18
 * Time: 11:41 AM
 */

class FRMTECHUNIT_BOL_UnitDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_BOL_UnitDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_BOL_UnitDao
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
        return OW_DB_PREFIX . 'frmtechunit_unit';
    }

    public function getDtoClassName()
    {
        return 'FRMTECHUNIT_BOL_Unit';
    }

    /**
     * @param $page
     * @param $count
     * @return array
     */
    public function findAllOrderByTime($page, $count){
        $example = new OW_Example();
        $example->setLimitClause($page,$count);
        $example->setOrder('timestamp desc');
        return $this->findListByExample($example);
    }

    /**
     * @param $query
     * @param $first
     * @param $count
     * @return array
     */
    public function search($query, $first, $count){
        $example = new OW_Example();
        $example->andFieldLike('name','%'.$query.'%');
        $example->setLimitClause($first,$count);
        $example->setOrder('timestamp desc');
        return $this->findListByExample($example);
    }

    /**
     * @param $query
     * @return array
     */
    public function searchCount($query){
        $example = new OW_Example();
        $example->andFieldLike('name','%'.$query.'%');
        return $this->countByExample($example);
    }
}