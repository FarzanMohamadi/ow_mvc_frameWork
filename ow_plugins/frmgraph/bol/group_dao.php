<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

class FRMGRAPH_BOL_GroupDao extends OW_BaseDao
{

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMGRAPH_BOL_Group';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgraph_group';
    }

    /***
     * @param $groupId
     * @return mixed
     */
    public function getLastCalculatedMetricsByGroupId($groupId){
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findListByExample($example);
    }
}
