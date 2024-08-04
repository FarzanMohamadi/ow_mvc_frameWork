<?php
class FRMGRAPH_BOL_GraphDao extends OW_BaseDao
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
        return 'FRMGRAPH_BOL_Graph';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgraph_graph';
    }

    public function getLastGroupId(){
        $query = "SELECT MAX(`groupId`) FROM `{$this->getTableName()}`";
        $maxOrder = $this->dbo->queryForColumn($query);
        if ($maxOrder == null) {
            $maxOrder = 0;
        }
        return $maxOrder;
    }

    public function getGroupIdForInsert(){
        $lastGroupId = $this->getLastGroupId();
        return $lastGroupId+1;
    }

    /***
     * @param $groupId
     * @return FRMGRAPH_BOL_Graph
     */
    public function getLastCalculatedMetricsByGroupId($groupId){
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findObjectByExample($example);
    }
}
