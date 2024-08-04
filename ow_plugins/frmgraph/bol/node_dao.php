<?php
class FRMGRAPH_BOL_NodeDao extends OW_BaseDao
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
        return 'FRMGRAPH_BOL_Node';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgraph_node';
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
